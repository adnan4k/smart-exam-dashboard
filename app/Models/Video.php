<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'duration'  => 'integer',
        'grade'     => 'integer',
    ];

    /**
     * Appended so the mobile app always gets one ready-to-play URL,
     * regardless of whether the video was uploaded or linked.
     */
    protected $appends = ['stream_url', 'thumbnail_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * The URL the client should play.
     */
    public function getStreamUrlAttribute()
    {
        if ($this->source === 'upload' && $this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return $this->video_url;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path
            ? Storage::disk('public')->url($this->thumbnail_path)
            : null;
    }

    /**
     * Delete the stored files when the row goes away.
     */
    protected static function booted()
    {
        static::deleting(function (Video $video) {
            if ($video->file_path) {
                Storage::disk('public')->delete($video->file_path);
            }
            if ($video->thumbnail_path) {
                Storage::disk('public')->delete($video->thumbnail_path);
            }
        });
    }

    /* ---------------------------------------------------------------- */
    /* Scopes                                                            */
    /* ---------------------------------------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForChapter($query, $chapterId)
    {
        return $query->where('chapter_id', $chapterId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }
}
