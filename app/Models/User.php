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
        // 'email_verified_at' => 'datetime',
    ];

    /**
     * A user can have many subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
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
}
