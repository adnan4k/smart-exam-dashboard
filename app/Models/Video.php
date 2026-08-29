<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    use HasFactory;

    /** Videos live on the private disk — never public/storage. */
    public const DISK = 'local';

    /** Thumbnails are not the valuable asset, so they stay publicly servable. */
    public const THUMB_DISK = 'public';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'duration'  => 'integer',
        'grade'     => 'integer',
    ];

    protected $appends = ['thumbnail_url'];

    /**
     * file_path points at private storage and must never reach a client.
     */
    protected $hidden = ['file_path'];

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

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path
            ? Storage::disk(self::THUMB_DISK)->url($this->thumbnail_path)
            : null;
    }

    /**
     * Absolute URL of the authorizing download endpoint. Serving the file
     * itself is gated there — this URL alone grants nothing without a
     * user_id that holds a paid subscription.
     */
    public function downloadUrl($userId = null)
    {
        return url('/api/videos/' . $this->id . '/download'
            . ($userId ? '?user_id=' . $userId : ''));
    }

    public function fileExists(): bool
    {
        return $this->file_path && Storage::disk(self::DISK)->exists($this->file_path);
    }

    public function absolutePath(): ?string
    {
        return $this->fileExists()
            ? Storage::disk(self::DISK)->path($this->file_path)
            : null;
    }

    protected static function booted()
    {
        static::deleting(function (Video $video) {
            if ($video->file_path) {
                Storage::disk(self::DISK)->delete($video->file_path);
            }
            if ($video->thumbnail_path) {
                Storage::disk(self::THUMB_DISK)->delete($video->thumbnail_path);
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
