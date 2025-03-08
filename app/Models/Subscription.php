<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'year_group_id', 
        'start_date', 
        'end_date',
        'image',
        'payment_status',
    ];

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
} 