<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'role',
        'status',
        'institution_type',
        'institution_name',
        'type_id',
        'referred_by',
        'device_id',
        'last_login_at',
        'phone',
        'uv',
        'fcm_token',
        'total_stars',
        'total_coins',
    ];

    protected $guarded = [];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            do {
                $user->referral_code = Str::upper(Str::random(8)); // Generates a unique 8-character referral code
            } while (User::where('referral_code', $user->referral_code)->exists());
        });
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    /**
     * Contest entries. At most one per contest, enforced by a unique index.
     */
    public function contestAttempts()
    {
        return $this->hasMany(\App\Models\ContestAttempt::class);
    }

    /**
     * Every star and coin this user has earned or spent.
     */
    public function rewardTransactions()
    {
        return $this->hasMany(\App\Models\RewardTransaction::class);
    }

    /**
     * A user can have many subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Has this user paid for the package covering their own exam type?
     */
    public function hasPaidPackage(): bool
    {
        if (! $this->type_id) {
            return false;
        }

        // Active package subscription or legacy type subscription
        return $this->subscriptions()
            ->active()
            ->where(function ($q) {
                $q->where('type_id', $this->type_id)
                  ->orWhereNotNull('package_id');
            })
            ->exists();
    }

    /**
     * Get all active paid package slugs for this user.
     * E.g. ['semester_1', 'coc'] or ['all_access'].
     *
     * @return array<string>
     */
    public function paidPackageSlugs(): array
    {
        return $this->subscriptions()
            ->active()
            ->whereNotNull('package_id')
            ->with('package')
            ->get()
            ->pluck('package.slug')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Check if user has an active paid All-Access package,
     * or a legacy paid subscription where package_id is null.
     */
    public function hasPaidAllAccess(): bool
    {
        // 1. Paid All-Access package
        $hasAllAccessPkg = $this->subscriptions()
            ->active()
            ->whereHas('package', function ($q) {
                $q->where('slug', Package::SLUG_ALL_ACCESS);
            })
            ->exists();

        if ($hasAllAccessPkg) {
            return true;
        }

        // 2. Backward-compatibility: legacy subscription without package_id
        return $this->subscriptions()
            ->active()
            ->whereNull('package_id')
            ->exists();
    }

    /**
     * Check if user has paid access to a given subject's package or selected subjects.
     */
    public function canAccessSubject($subject): bool
    {
        if (is_numeric($subject)) {
            $subject = Subject::find($subject);
        }

        if (! $subject) {
            return false;
        }

        // Sample subjects are open to everyone
        if ($subject->is_sample) {
            return true;
        }

        // Students with All-Access unlock all subjects
        if ($this->hasPaidAllAccess()) {
            return true;
        }

        // Check if student has an active paid subscription that covers this subject:
        // 1. Subject was selected by user in their subscription (or shares name with selected variant)
        // 2. Subject is a default package subject for their subscribed package
        // 3. Legacy: subject.package_id matches subscription.package_id
        // 4. Legacy: subject.package_type matches subscription package slug
        $hasAccessViaSubscription = $this->subscriptions()
            ->active()
            ->where(function ($query) use ($subject) {
                $query->whereHas('subjects', function ($subQuery) use ($subject) {
                    $subQuery->where('subjects.id', $subject->id)
                             ->orWhere('subjects.name', $subject->name);
                })
                ->orWhereHas('package.defaultSubjects', function ($subQuery) use ($subject) {
                    $subQuery->where('subjects.id', $subject->id)
                             ->orWhere('subjects.name', $subject->name);
                });

                if ($subject->package_id) {
                    $query->orWhere('package_id', $subject->package_id);
                }

                if ($subject->package_type) {
                    $query->orWhereHas('package', function ($q) use ($subject) {
                        $q->where('slug', $subject->package_type);
                    });
                }
            })
            ->exists();

        if ($hasAccessViaSubscription) {
            return true;
        }

        // Subjects not tied to any package require a paid subscription
        $isTiedToPackage = (bool) $subject->package_id
            || (bool) $subject->package_type
            || \Illuminate\Support\Facades\DB::table('package_subject')->where('subject_id', $subject->id)->exists();

        if (! $isTiedToPackage) {
            return $this->hasPaidPackage();
        }

        return false;
    }

    /**
     * canAccessSubject() for many subjects at once: the ids of the ones this
     * user may open, in a fixed handful of queries instead of several per
     * subject. It must stay equivalent to canAccessSubject(), which
     * UserAccessibleSubjectIdsTest checks; change the two together.
     *
     * Names and slugs are compared in SQL, so the column collation decides
     * what "same name" means, exactly as it does in canAccessSubject().
     *
     * @param  iterable<int, Subject>  $subjects
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function accessibleSubjectIds($subjects)
    {
        $subjects = collect($subjects);

        if ($subjects->isEmpty()) {
            return collect();
        }

        if ($this->hasPaidAllAccess()) {
            return $subjects->pluck('id')->values();
        }

        $active = $this->subscriptions()->active()->with('package')->get();
        $packageIds = $active->pluck('package_id')->filter()->unique()->values();
        $slugs = $active->pluck('package.slug')->filter()->unique()->values();

        // Subjects the student selected, plus their packages' default subjects.
        $chosenIds = \Illuminate\Support\Facades\DB::table('subscription_subject')
            ->whereIn('subscription_id', $active->pluck('id'))
            ->pluck('subject_id')
            ->merge(\Illuminate\Support\Facades\DB::table('package_subject')
                ->whereIn('package_id', $packageIds)
                ->where('is_default', true)
                ->pluck('subject_id'))
            ->unique()
            ->values();

        $covered = $active->isEmpty() ? collect() : Subject::whereIn('id', $subjects->pluck('id'))
            ->where(function ($query) use ($chosenIds, $packageIds, $slugs) {
                $query->whereIn('id', $chosenIds)
                    ->orWhereIn('name', Subject::select('name')->whereIn('id', $chosenIds))
                    ->orWhereIn('package_id', $packageIds)
                    ->orWhereIn('package_type', $slugs);
            })
            ->pluck('id');

        $inAnyPackage = \Illuminate\Support\Facades\DB::table('package_subject')
            ->whereIn('subject_id', $subjects->pluck('id'))
            ->pluck('subject_id');

        $hasPaidPackage = null;

        return $subjects->filter(function (Subject $subject) use ($covered, $inAnyPackage, &$hasPaidPackage) {
            if ($subject->is_sample || $covered->contains($subject->id)) {
                return true;
            }

            $isTiedToPackage = (bool) $subject->package_id
                || (bool) $subject->package_type
                || $inAnyPackage->contains($subject->id);

            return ! $isTiedToPackage && ($hasPaidPackage ??= $this->hasPaidPackage());
        })->pluck('id')->values();
    }

    /**
     * Check if the user is subscribed to a given year group.
     */
    public function isSubscribed($yearGroupId)
    {
        return $this->subscriptions()
            ->where('year_group_id', $yearGroupId)
            ->where('end_date', '>=', now())
            ->exists();
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function notificationComments()
    {
        return $this->hasMany(NotificationComment::class);
    }
}
