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
     *
     * `types.price` is what a student buys, and a paid `subscriptions` row for
     * that type is the receipt. This mirrors VideoController::isEntitled and
     * NoteController, which is deliberate: contests must not apply a stricter
     * rule than the notes and videos sold under the same package.
     *
     * Note what is NOT checked: `start_date`/`end_date` are written on purchase
     * but no feature in this app enforces them, so access does not expire
     * anywhere. Contests are not the place to introduce expiry on their own -
     * that has to land across every paid surface at once or a lapsed user keeps
     * their notes and videos while silently losing contests.
     */
    public function hasPaidPackage(): bool
    {
        if (! $this->type_id) {
            return false;
        }

        return $this->subscriptions()
            ->where('type_id', $this->type_id)
            ->where('payment_status', 'paid')
            ->exists();
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
