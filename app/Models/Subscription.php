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
}