<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'image_url',
        'like_count',
        'dislike_count',
        'comment_count',
    ];

    public function comments()
    {
        return $this->hasMany(NotificationComment::class);
    }
}

