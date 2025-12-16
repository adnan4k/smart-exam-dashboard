<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_notification_id',
        'user_id',
        'comment',
    ];

    public function notification()
    {
        return $this->belongsTo(AppNotification::class, 'app_notification_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


