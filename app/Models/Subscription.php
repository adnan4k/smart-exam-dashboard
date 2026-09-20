<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

   protected $guarded = [];

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