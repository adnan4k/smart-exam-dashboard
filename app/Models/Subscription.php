<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

   protected $guarded = [];

    /**
     * Paid and not yet expired. This is the only definition of a subscription
     * that grants access; `payment_status = paid` alone stays true forever.
     */
    public function scopeActive($query)
    {
        return $query->where('payment_status', 'paid')
            ->whereDate('end_date', '>=', now()->toDateString());
    }

    public function isActive(): bool
    {
        return $this->payment_status === 'paid'
            && $this->end_date
            && \Illuminate\Support\Carbon::parse($this->end_date)->endOfDay()->gte(now());
    }

    /**
     * A subscription belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A subscription belongs to a year group.
     */
    public function yearGroup()
    {
        return $this->belongsTo(YearGroup::class);
    }

    /**
     * A subscription belongs to a package.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * A subscription optionally belongs to an exam type.
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * User-selected and default subjects for this subscription.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subscription_subject')
            ->withTimestamps();
    }

    /**
     * Automatically sync the package's default subjects to this subscription.
     */
    public function syncDefaultSubjects(): void
    {
        if (! $this->package_id) {
            return;
        }

        $package = $this->package ?? Package::find($this->package_id);
        if (! $package) {
            return;
        }

        // Get default subject IDs from package_subject
        $defaultSubjectIds = \Illuminate\Support\Facades\DB::table('package_subject')
            ->where('package_id', $this->package_id)
            ->where('is_default', true)
            ->pluck('subject_id')
            ->toArray();

        // Also include subjects directly assigned via subjects.package_id
        $directSubjectIds = \Illuminate\Support\Facades\DB::table('subjects')
            ->where('package_id', $this->package_id)
            ->pluck('id')
            ->toArray();

        $allIds = array_values(array_unique(array_merge($defaultSubjectIds, $directSubjectIds)));

        if (! empty($allIds)) {
            $max = $package->max_subjects ?: 7;
            $capped = array_slice($allIds, 0, $max);
            $this->subjects()->syncWithoutDetaching($capped);
        }
    }

    /**
     * Number of subject slots remaining for this subscription.
     */
    public function remainingSubjectSlots(): int
    {
        $max = optional($this->package)->max_subjects ?? 7;
        $current = $this->subjects()->count();
        return max(0, $max - $current);
    }
}