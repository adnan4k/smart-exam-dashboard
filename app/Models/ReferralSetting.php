<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralSetting extends Model
{
    protected $fillable = [
        'required_referrals',
        'reward_amount',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'reward_amount' => 'decimal:2'
    ];

    public function referrals()
    {
        return $this->hasMany(Referral::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
