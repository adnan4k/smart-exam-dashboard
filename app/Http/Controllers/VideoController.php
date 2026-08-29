<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Subject;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /** Max upload size in kilobytes (500MB). */
    private const MAX_VIDEO_KB = 512000;

    private const VIDEO_MIMETYPES = 'video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm,video/mpeg';

    /**
     * JSON response with an explicit Content-Length, matching NoteController
     * so the mobile client sees consistent headers across endpoints.
     */
    private function jsonResponse($data, $status = 200)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $uncompressedLength = strlen($json);

        $acceptEncoding = request()->header('Accept-Encoding', '');
        $useGzip = stripos($acceptEncoding, 'gzip') !== false;

        if ($useGzip) {
            $compressed = gzencode($json, 6);

            $response = response($compressed, $status)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Encoding', 'gzip')
                ->header('Content-Length', (string) strlen($compressed))
                ->header('X-Uncompressed-Size', (string) $uncompressedLength);
        } else {
            $response = response($json, $status)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Length', (string) $uncompressedLength);
        }

        $response->headers->remove('Transfer-Encoding');

        return $response;
    }

    /* ------------------------------------------------------------------ */
    /* Listing                                                             */
    /* ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'type_id'    => 'nullable|exists:types,id',
            'grade'      => 'nullable|integer|min:0|max:12',
            'language'   => 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
            'source'     => 'nullable|in:url,upload',
            'search'     => 'nullable|string|max:255',
            'include_hidden' => 'nullable|boolean',
            'per_page'   => 'nullable|integer|min:1|max:100',
            'page'       => 'nullable|integer|min:1',
        ]);

        $query = Video::with(['subject', 'chapter', 'type', 'user']);

        if (!$request->boolean('include_hidden')) {
            $query->active();
        }

        foreach (['subject_id', 'chapter_id', 'type_id', 'grade', 'language', 'source'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('search')) {
            $term = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                  ->orWhere('description', 'like', $term);
            });
        }

        $videos = $query->ordered()->paginate($request->input('per_page', 15));

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $videos->items(),
            'pagination' => [
                'current_page' => $videos->currentPage(),
                'last_page'    => $videos->lastPage(),
                'per_page'     => $videos->perPage(),
                'total'        => $videos->total(),
                'from'         => $videos->firstItem(),
                'to'           => $videos->lastItem(),
            ],
        ]);
    }

    /**
     * All videos filed under one subject, grouped by chapter.
     * Videos attached to the subject but no chapter come back under
     * "subject_videos" so the app can show them above the chapter list.
     */
    public function bySubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $subject = Subject::findOrFail($request->input('subject_id'));

        $videos = Video::with(['chapter', 'type'])
            ->active()
            ->forSubject($subject->id)
            ->ordered()
            ->get();

        $subjectLevel = $videos->whereNull('chapter_id')->values();

        $chapters = $videos->whereNotNull('chapter_id')
            ->groupBy('chapter_id')
            ->map(function ($group, $chapterId) {
                return [
                    'chapter_id'   => (string) $chapterId,
                    'chapter_name' => optional($group->first()->chapter)->name,
                    'videos'       => $group->values(),
                ];
            })->values();

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => [
                'subject_id'     => (string) $subject->id,
                'subject_name'   => $subject->name,
                'subject_videos' => $subjectLevel,
                'chapters'       => $chapters,
            ],
        ]);
    }

    /**
     * All videos for one chapter.
     */
    public function byChapter(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $chapter = Chapter::findOrFail($request->input('chapter_id'));

        $videos = Video::with(['subject', 'type'])
            ->active()
            ->forChapter($chapter->id)
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', $request->input('subject_id')))
            ->ordered()
            ->get();

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => [
                'chapter_id'   => (string) $chapter->id,
                'chapter_name' => $chapter->name,
                'videos'       => $videos,
            ],
        ]);
    }

    /**
     * Everything visible to a user, grouped subject -> chapter,
     * scoped to the user's exam type (plus type-agnostic videos).
     */
    public function forUserGrouped(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if (!$user->type_id) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'No exam type associated with this user.',
            ], 400);
        }

        $videos = Video::with(['subject', 'chapter', 'type'])
            ->active()
            ->where(function ($q) use ($user) {
                $q->whereNull('type_id')->orWhere('type_id', $user->type_id);
            })
            ->ordered()
            ->get();

        $general = $videos->whereNull('subject_id')->values();

        $subjects = $videos->whereNotNull('subject_id')
            ->groupBy('subject_id')
            ->map(function ($subjectVideos, $subjectId) {
                $chapters = $subjectVideos->whereNotNull('chapter_id')
                    ->groupBy('chapter_id')
                    ->map(fn ($group, $chapterId) => [
                        'chapter_id'   => (string) $chapterId,
                        'chapter_name' => optional($group->first()->chapter)->name,
                        'videos'       => $group->values(),
                    ])->values();

                return [
                    'subject_id'     => (string) $subjectId,
                    'subject_name'   => optional($subjectVideos->first()->subject)->name,
                    'subject_videos' => $subjectVideos->whereNull('chapter_id')->values(),
                    'chapters'       => $chapters,
                ];
            })->values();

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => [
                'general_videos' => $general,
                'subjects'       => $subjects,
            ],
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Write                                                               */
    /* ------------------------------------------------------------------ */

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $video = new Video();
        $this->fillFromRequest($video, $request, $data);
        $video->save();

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $video->load(['subject', 'chapter', 'type', 'user']),
        ], 201);
    }

    public function show(Video $video)
    {
        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $video->load(['subject', 'chapter', 'type', 'user']),
        ]);
    }

    public function update(Request $request, Video $video)
    {
        $data = $this->validatePayload($request, true);

        $this->fillFromRequest($video, $request, $data);
        $video->save();

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $video->load(['subject', 'chapter', 'type', 'user']),
        ]);
    }

    public function destroy(Video $video)
    {
        // The model's deleting hook removes the stored file and thumbnail.
        $video->delete();

        return $this->jsonResponse([
            'status'  => 'success',
            'message' => 'Video deleted successfully',
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    private function validatePayload(Request $request, bool $partial = false)
    {
        $required = $partial ? 'sometimes' : 'required';
        $optional = 'sometimes|nullable';

        $rules = [
            'type_id'     => $optional . '|exists:types,id',
            'subject_id'  => $optional . '|exists:subjects,id',
            'chapter_id'  => $optional . '|exists:chapters,id',
            'user_id'     => $optional . '|exists:users,id',
            'title'       => $required . '|string|max:255',
            'description' => $optional . '|string',
            'source'      => $required . '|in:url,upload',
            'video_url'   => $optional . '|url|max:2048',
            'video'       => $optional . '|file|mimetypes:' . self::VIDEO_MIMETYPES . '|max:' . self::MAX_VIDEO_KB,
            'thumbnail'   => $optional . '|image|max:2048',
            'duration'    => $optional . '|integer|min:0',
            'grade'       => $optional . '|integer|min:0|max:12',
            'language'    => $partial ? 'sometimes|in:amharic,afan_oromo,english,tigrinya,somali,afar,other'
                                      : 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
            'sort_order'  => $optional . '|integer|min:0',
            'is_active'   => $optional . '|boolean',
        ];

        $validated = $request->validate($rules);

        // A video is useless without something to play.
        $source = $request->input('source');

        if ($source === 'url' && !$request->filled('video_url')) {
            abort($this->jsonResponse([
                'status'  => 'error',
                'message' => 'video_url is required when source is "url".',
            ], 422));
        }

        if ($source === 'upload' && !$request->hasFile('video') && !$partial) {
            abort($this->jsonResponse([
                'status'  => 'error',
                'message' => 'A video file is required when source is "upload".',
            ], 422));
        }

        return $validated;
    }

    private function fillFromRequest(Video $video, Request $request, array $data): void
    {
        foreach (['type_id', 'subject_id', 'chapter_id', 'user_id', 'title', 'description',
                  'duration', 'grade', 'language', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $video->{$field} = $data[$field];
            }
        }

        if ($request->filled('source')) {
            $video->source = $request->input('source');
        }

        if ($video->source === 'upload') {
            if ($request->hasFile('video')) {
                if ($video->file_path) {
                    Storage::disk('public')->delete($video->file_path);
                }
                $file = $request->file('video');
                $video->file_path = $file->store('videos', 'public');
                $video->mime_type = $file->getMimeType();
                $video->file_size = $file->getSize();
            }
            $video->video_url = null;
        } else {
            if ($video->file_path) {
                Storage::disk('public')->delete($video->file_path);
            }
            $video->file_path = null;
            $video->mime_type = null;
            $video->file_size = null;
            $video->video_url = $request->input('video_url', $video->video_url);
        }

        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail_path) {
                Storage::disk('public')->delete($video->thumbnail_path);
            }
            $video->thumbnail_path = $request->file('thumbnail')->store('videos/thumbnails', 'public');
        }

        if (!$video->language) {
            $video->language = 'english';
        }
    }
}
