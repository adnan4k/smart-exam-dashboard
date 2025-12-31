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
        'type_id',
        'like_count',
        'dislike_count',
        'comment_count',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function comments()
    {
        return $this->hasMany(NotificationComment::class);
    }

    /**
     * Get the full URL for the image
     */
    public function getImageUrlAttribute($value)
    {
        if (!$value) {
            return null;
        }
        
        // If it's already a full URL, return as is
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        // Otherwise, return the storage URL
        return asset('storage/' . $value);
    }

    /**
     * Get the raw image path (for storage operations)
     */
    public function getImagePathAttribute()
    {
        $value = $this->attributes['image_url'] ?? null;
        
        if (!$value) {
            return null;
        }
        
        // If it's a full URL, extract the path
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        // Return the storage path
        return $value;
    }
}

