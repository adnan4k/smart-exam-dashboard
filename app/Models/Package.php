<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    public const SLUG_SEMESTER_1 = 'semester_1';
    public const SLUG_SEMESTER_2 = 'semester_2';
    public const SLUG_COC = 'coc';
    public const SLUG_ALL_ACCESS = 'all_access';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'type_id',
        'duration_days',
        'max_subjects',
        'is_active',
        'order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'duration_days' => 'integer',
        'max_subjects' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Scope for only active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered packages.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('id', 'asc');
    }

    /**
     * Optional exam type association.
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * Default subjects assigned to this package.
     */
    public function defaultSubjects()
    {
        return $this->belongsToMany(Subject::class, 'package_subject')
            ->wherePivot('is_default', true)
            ->withTimestamps();
    }

    /**
     * All subjects associated with this package.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'package_subject')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    /**
     * Legacy direct subjects where subject.package_id = package.id.
     */
    public function directSubjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Subscriptions purchased for this package.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isAllAccess(): bool
    {
        return $this->slug === self::SLUG_ALL_ACCESS;
    }

    public function isSemester1(): bool
    {
        return $this->slug === self::SLUG_SEMESTER_1;
    }

    public function isSemester2(): bool
    {
        return $this->slug === self::SLUG_SEMESTER_2;
    }

    public function isCoc(): bool
    {
        return $this->slug === self::SLUG_COC;
    }
}
