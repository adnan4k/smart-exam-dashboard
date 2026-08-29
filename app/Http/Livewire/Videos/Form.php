<?php

namespace App\Http\Livewire\Videos;

use App\Models\Chapter;
use App\Models\Subject;
use App\Models\Type;
use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
    use WithFileUploads;

    /** Max upload size in kilobytes (500MB). Mirror of config/livewire.php rule. */
    public const MAX_VIDEO_KB = 512000;

    public $id;
    public $is_edit = false;
    public $openModal = false;
    public $isSubmitting = false;

    public $typeId;
    public $subjectId;
    public $chapterId;

    public $title;
    public $description;
    public $videoFile;             // TemporaryUploadedFile
    public $thumbnail;             // TemporaryUploadedFile
    public $existingFilePath;
    public $existingThumbnailPath;

    public $duration;              // seconds
    public $grade;
    public $language = 'english';
    public $sortOrder = 0;
    public $isActive = true;

    public $chaptersForSubject = [];

    protected $listeners = ['videoModal' => 'videoModal'];

    public function videoModal()
    {
        $this->openModal = true;
    }

    protected function rules()
    {
        return [
            'typeId'      => 'nullable|exists:types,id',
            'subjectId'   => 'nullable|exists:subjects,id',
            'chapterId'   => 'nullable|exists:chapters,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'videoFile'   => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm,video/mpeg|max:' . self::MAX_VIDEO_KB,
            'thumbnail'   => 'nullable|image|max:2048',
            'duration'    => 'nullable|integer|min:0',
            'grade'       => 'nullable|integer|min:0|max:12',
            'language'    => 'required|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
            'sortOrder'   => 'nullable|integer|min:0',
        ];
    }

    protected $messages = [
        'videoFile.mimetypes' => 'The video must be an mp4, mov, avi, mkv, webm or mpeg file.',
        'videoFile.max'       => 'The video may not be larger than 500MB.',
    ];

    /* ------------------------------------------------------------------ */
    /* Cascading selects                                                   */
    /* ------------------------------------------------------------------ */

    public function updatedTypeId($value)
    {
        $this->subjectId = null;
        $this->chapterId = null;
        $this->chaptersForSubject = [];
    }

    public function updatedSubjectId($value)
    {
        $this->chapterId = null;
        $this->loadChaptersForSubject($value);
    }

    /**
     * Chapters are not directly linked to a subject in this schema, so we
     * derive them from the questions/notes/videos already filed under it and
     * fall back to the full list when nothing has been filed yet.
     */
    protected function loadChaptersForSubject($subjectId)
    {
        if (!$subjectId) {
            $this->chaptersForSubject = Chapter::orderBy('name')->get();
            return;
        }

        $chapterIds = collect();

        foreach (['questions', 'notes', 'videos'] as $table) {
            $chapterIds = $chapterIds->merge(
                \DB::table($table)
                    ->where('subject_id', $subjectId)
                    ->whereNotNull('chapter_id')
                    ->distinct()
                    ->pluck('chapter_id')
            );
        }

        $chapterIds = $chapterIds->unique()->values();

        $this->chaptersForSubject = $chapterIds->isEmpty()
            ? Chapter::orderBy('name')->get()
            : Chapter::whereIn('id', $chapterIds)->orderBy('name')->get();
    }

    /* ------------------------------------------------------------------ */
    /* Edit                                                                */
    /* ------------------------------------------------------------------ */

    #[On('edit-video')]
    public function edit($videoId = null)
    {
        if (is_array($videoId) && isset($videoId['videoId'])) {
            $videoId = $videoId['videoId'];
        } elseif (is_object($videoId) && isset($videoId->videoId)) {
            $videoId = $videoId->videoId;
        }

        if (!$videoId) {
            Toaster::error('Video ID is required.');
            return;
        }

        $video = Video::findOrFail($videoId);

        $this->id                    = $video->id;
        $this->typeId                = $video->type_id;
        $this->subjectId             = $video->subject_id;
        $this->chapterId             = $video->chapter_id;
        $this->title                 = $video->title;
        $this->description           = $video->description;
        $this->existingFilePath      = $video->file_path;
        $this->existingThumbnailPath = $video->thumbnail_path;
        $this->duration              = $video->duration;
        $this->grade                 = $video->grade;
        $this->language              = $video->language ?? 'english';
        $this->sortOrder             = $video->sort_order;
        $this->isActive              = (bool) $video->is_active;
        $this->is_edit               = true;

        $this->loadChaptersForSubject($this->subjectId);

        $this->openModal = true;
    }

    /* ------------------------------------------------------------------ */
    /* Save                                                                */
    /* ------------------------------------------------------------------ */

    public function saveVideo()
    {
        $this->isSubmitting = true;

        try {
            $this->validate();

            // Students download these for offline use, so every video is a real file.
            if (!$this->videoFile && !$this->existingFilePath) {
                $this->addError('videoFile', 'Please choose a video file to upload.');
                Toaster::error('Please choose a video file to upload.');
                $this->isSubmitting = false;
                return;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            $first = collect($e->errors())->flatten()->first();
            Toaster::error($first ?: 'Please fill in all required fields correctly.');
            $this->isSubmitting = false;
            return;
        }

        $data = [
            'type_id'     => $this->typeId ?: null,
            'subject_id'  => $this->subjectId ?: null,
            'chapter_id'  => $this->chapterId ?: null,
            'title'       => $this->title,
            'description' => $this->description,
            'duration'    => $this->duration ?: null,
            'grade'       => $this->grade !== '' ? $this->grade : null,
            'language'    => $this->language,
            'sort_order'  => $this->sortOrder ?: 0,
            'is_active'   => (bool) $this->isActive,
        ];

        try {
            $video = $this->is_edit ? Video::findOrFail($this->id) : null;

            if ($this->videoFile) {
                // Replace the old file rather than orphaning it on disk.
                if ($video && $video->file_path) {
                    Storage::disk(Video::DISK)->delete($video->file_path);
                }
                $data['file_path'] = $this->videoFile->store('videos', Video::DISK);
                $data['mime_type'] = $this->videoFile->getMimeType();
                $data['file_size'] = $this->videoFile->getSize();
                $data['checksum']  = md5_file(Storage::disk(Video::DISK)->path($data['file_path']));
            }

            if ($this->thumbnail) {
                if ($video && $video->thumbnail_path) {
                    Storage::disk(Video::THUMB_DISK)->delete($video->thumbnail_path);
                }
                $data['thumbnail_path'] = $this->thumbnail->store('videos/thumbnails', Video::THUMB_DISK);
            }

            if ($video) {
                $video->update($data);
                $message = 'Video Updated Successfully!';
            } else {
                $data['user_id'] = auth()->id();
                Video::create($data);
                $message = 'Video Created Successfully!';
            }

            Toaster::success($message);

            $this->openModal = false;
            $this->resetForm();
            $this->dispatch('refreshVideos');
        } catch (\Exception $e) {
            $errorMessage = 'Something went wrong while saving the video.';
            if (config('app.debug')) {
                $errorMessage .= ' ' . $e->getMessage();
            }
            Toaster::error($errorMessage);
            Log::error('Video save error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $data,
            ]);
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function resetAfterClose()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'id', 'is_edit', 'typeId', 'subjectId', 'chapterId', 'title', 'description',
            'videoFile', 'thumbnail', 'existingFilePath',
            'existingThumbnailPath', 'duration', 'grade', 'language', 'sortOrder',
            'isActive', 'isSubmitting',
        ]);

        $this->language = 'english';
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->chaptersForSubject = [];
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.videos.form', [
            'types'       => Type::orderBy('name')->get(),
            'subjects'    => Subject::when($this->typeId, fn ($q) => $q->where('type_id', $this->typeId))
                                ->orderBy('name')->get(),
            'allChapters' => Chapter::orderBy('name')->get(),
        ]);
    }
}
