<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class YearGroup extends Model
{
    use HasFactory;

    protected $fillable = ['year'];

    /**
     * A year group can have many questions.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * A year group can have many subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
} 