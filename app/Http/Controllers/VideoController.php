<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Subject;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

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
    /* Entitlement                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * An active paid subscription for the user's own exam type, or package-specific access
     * when a subject is provided.
     */
    private function isEntitled(?User $user, ?Subject $subject = null): bool
    {
        if (!$user) {
            return false;
        }

        if ($subject) {
            return $user->canAccessSubject($subject);
        }

        return $user->hasPaidPackage();
    }

    private function resolveUser(Request $request): ?User
    {
        if ($request->filled('user_id')) {
            return User::find($request->input('user_id'));
        }

        return $request->user() ?: auth('sanctum')->user();
    }

    /**
     * Shape a video for the client: metadata always, the download link only
     * when the caller is entitled to it.
     */
    private function present(Video $video, ?bool $entitledOverride, ?User $user): array
    {
        $data = $video->toArray();

        $entitled = $entitledOverride ?? ($video->subject ? ($user ? $user->canAccessSubject($video->subject) : false) : $this->isEntitled($user));

        $typeAllowed   = !$video->type_id
                         || !$user?->type_id
                         || (int) $video->type_id === (int) $user->type_id;
        $fileAvailable = $video->fileExists();

        $data['locked']         = !$entitled || !$typeAllowed;
        $data['file_available'] = $fileAvailable;
        $data['download_url']   = ($entitled && $typeAllowed && $fileAvailable)
            ? $video->downloadUrl($user?->id)
            : null;

        return $data;
    }

    private function presentMany($videos, ?bool $entitled, ?User $user): array
    {
        return collect($videos)->map(fn ($v) => $this->present($v, $entitled, $user))->values()->all();
    }

    /**
     * Subject -> chapter tree for a flat collection of videos. A video with a
     * subject but no chapter sits in that subject's own "subject_videos".
     */
    private function groupBySubject($videos, bool $entitled, ?User $user): array
    {
        return $videos->whereNotNull('subject_id')
            ->groupBy('subject_id')
            ->map(function ($subjectVideos, $subjectId) use ($entitled, $user) {
                $subject = $subjectVideos->first()->subject;
                $subjectEntitled = $subject && $user ? $user->canAccessSubject($subject) : $entitled;

                $chapters = $subjectVideos->whereNotNull('chapter_id')
                    ->groupBy('chapter_id')
                    ->map(fn ($group, $chapterId) => [
                        'chapter_id'   => (int) $chapterId,
                        'chapter_name' => optional($group->first()->chapter)->name,
                        'videos'       => $this->presentMany($group, $subjectEntitled, $user),
                    ])->values();

                return [
                    'subject_id'     => (int) $subjectId,
                    'subject_name'   => optional($subject)->name,
                    'subject_videos' => $this->presentMany($subjectVideos->whereNull('chapter_id'), $subjectEntitled, $user),
                    'chapters'       => $chapters,
                ];
            })->values()->all();
    }

    /* ------------------------------------------------------------------ */
    /* Download — the gate                                                 */
    /* ------------------------------------------------------------------ */

    /**
     * Serve the file itself. Supports HTTP range requests, so an interrupted
     * download resumes instead of restarting — which matters a lot on mobile
     * data. Symfony's BinaryFileResponse handles Range/206 for us.
     */
    public function download(Request $request, Video $video)
    {
        $userId = $request->input('user_id') ?: auth('sanctum')->id();

        if (!$userId) {
            return response()->json([
                'status'  => 'error',
                'reason'  => 'missing_user',
                'message' => 'User ID is required to download this video.',
            ], 400);
        }

        $user = User::findOrFail($userId);

        $subject = $video->subject_id ? ($video->subject ?: Subject::find($video->subject_id)) : null;

        if (!$this->isEntitled($user, $subject)) {
            return $this->refuseDownload($video, $user, 'not_entitled',
                'An active subscription is required to download this video.', 403);
        }

        $hasAllAccess = $user->hasPaidAllAccess();
        $isSubjectEntitled = $subject && $user->canAccessSubject($subject);

        // A video scoped to another exam type is not this user's to download,
        // unless they hold All Access or are entitled to this subject.
        if (!$hasAllAccess && !$isSubjectEntitled && $video->type_id && $user->type_id && (int) $video->type_id !== (int) $user->type_id) {
            return $this->refuseDownload($video, $user, 'exam_type_mismatch',
                'This video is not available for your exam type.', 403);
        }

        if (!$video->is_active) {
            return $this->refuseDownload($video, $user, 'inactive',
                'This video is not currently available.', 404);
        }

        if (!$video->fileExists()) {
            return $this->refuseDownload($video, $user, 'file_missing',
                'Video file is missing on the server.', 404);
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        $response = new BinaryFileResponse($video->absolutePath());
        $response->headers->set('Content-Type', $video->mime_type ?: 'video/mp4');
        $response->headers->set('Accept-Ranges', 'bytes');

        if ($video->checksum) {
            // Lets the client verify a completed download and skip re-downloads.
            $response->headers->set('X-Checksum-MD5', $video->checksum);
            $response->setEtag($video->checksum);
        }

        $response->setContentDisposition(
            $request->boolean('inline')
                ? ResponseHeaderBag::DISPOSITION_INLINE
                : ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $this->downloadFilename($video)
        );

        return $response;
    }

    /**
     * A refused download shows up in the app only as "Download failed", so
     * every refusal is logged with the reason that produced it.
     */
    private function refuseDownload(Video $video, User $user, string $reason, string $message, int $status)
    {
        Log::warning('Video download refused', [
            'reason'        => $reason,
            'video_id'      => $video->id,
            'user_id'       => $user->id,
            'video_type_id' => $video->type_id,
            'user_type_id'  => $user->type_id,
        ]);

        return $this->jsonResponse([
            'status'  => 'error',
            'reason'  => $reason,
            'message' => $message,
        ], $status);
    }

    private function downloadFilename(Video $video): string
    {
        $extension = pathinfo($video->file_path, PATHINFO_EXTENSION) ?: 'mp4';
        $safeTitle = preg_replace('/[^A-Za-z0-9 _-]/', '', $video->title) ?: 'video';

        return trim($safeTitle) . '.' . $extension;
    }

    /* ------------------------------------------------------------------ */
    /* Listing                                                             */
    /* ------------------------------------------------------------------ */

    public function index(Request $request)
    {
        $request->validate([
            'user_id'    => 'nullable|exists:users,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'type_id'    => 'nullable|exists:types,id',
            'grade'      => 'nullable|integer|min:0|max:12',
            'language'   => 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
            'search'     => 'nullable|string|max:255',
            'include_hidden' => 'nullable|boolean',
            'per_page'   => 'nullable|integer|min:1|max:100',
            'page'       => 'nullable|integer|min:1',
        ]);

        $user = $this->resolveUser($request);
        $entitled = $this->isEntitled($user);

        $query = Video::with(['subject', 'chapter', 'type', 'user']);

        if (!$request->boolean('include_hidden')) {
            $query->active();
        }

        foreach (['subject_id', 'chapter_id', 'type_id', 'grade', 'language'] as $field) {
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
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => $this->presentMany($videos->items(), $entitled, $user),
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
            'user_id'    => 'nullable|exists:users,id',
            'language'   => 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
        ]);

        $subject = Subject::findOrFail($request->input('subject_id'));
        $user = $this->resolveUser($request);
        $entitled = $this->isEntitled($user, $subject);

        $videos = Video::with(['chapter', 'type'])
            ->active()
            ->forSubject($subject->id)
            ->when($request->filled('language'), fn ($q) => $q->where('language', $request->input('language')))
            ->ordered()
            ->get();

        $chapters = $videos->whereNotNull('chapter_id')
            ->groupBy('chapter_id')
            ->map(fn ($group, $chapterId) => [
                'chapter_id'   => (int) $chapterId,
                'chapter_name' => optional($group->first()->chapter)->name,
                'videos'       => $this->presentMany($group, $entitled, $user),
            ])->values();

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => [
                'subject_id'     => (int) $subject->id,
                'subject_name'   => $subject->name,
                'subject_videos' => $this->presentMany($videos->whereNull('chapter_id'), $entitled, $user),
                'chapters'       => $chapters,
            ],
        ]);
    }

    public function byChapter(Request $request)
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'user_id'    => 'nullable|exists:users,id',
            'language'   => 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
        ]);

        $user = $this->resolveUser($request);
        $chapter = Chapter::findOrFail($request->input('chapter_id'));
        $subject = $request->filled('subject_id')
            ? Subject::find($request->input('subject_id'))
            : Video::forChapter($chapter->id)->whereNotNull('subject_id')->first()?->subject;
        $entitled = $this->isEntitled($user, $subject);

        $videos = Video::with(['subject', 'type'])
            ->active()
            ->forChapter($chapter->id)
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', $request->input('subject_id')))
            ->when($request->filled('language'), fn ($q) => $q->where('language', $request->input('language')))
            ->ordered()
            ->get();

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => [
                'chapter_id'   => (int) $chapter->id,
                'chapter_name' => $chapter->name,
                'videos'       => $this->presentMany($videos, $entitled, $user),
            ],
        ]);
    }

    /**
     * Every video in one language, grouped subject -> chapter, so a language
     * picker in the app leads straight into a browsable list. Videos filed
     * under no subject come back under "general_videos".
     */
    public function byLanguage(Request $request)
    {
        $request->validate([
            'language'   => 'required|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
            'user_id'    => 'nullable|exists:users,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'type_id'    => 'nullable|exists:types,id',
            'grade'      => 'nullable|integer|min:0|max:12',
        ]);

        $user = $this->resolveUser($request);
        $entitled = $this->isEntitled($user);
        $language = $request->input('language');

        $videos = Video::with(['subject', 'chapter', 'type'])
            ->active()
            ->forLanguage($language)
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', $request->input('subject_id')))
            ->when($request->filled('type_id'), fn ($q) => $q->where('type_id', $request->input('type_id')))
            ->when($request->filled('grade'), fn ($q) => $q->where('grade', $request->input('grade')))
            ->ordered()
            ->get();

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => [
                'language'       => $language,
                'total'          => $videos->count(),
                'general_videos' => $this->presentMany($videos->whereNull('subject_id'), $entitled, $user),
                'subjects'       => $this->groupBySubject($videos, $entitled, $user),
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
            'user_id'    => 'required|exists:users,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'language'   => 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if (!$user->type_id) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'No exam type associated with this user.',
            ], 400);
        }

        $entitled = $this->isEntitled($user);

        $videos = Video::with(['subject', 'chapter', 'type'])
            ->active()
            ->where(function ($q) use ($user) {
                $q->whereNull('type_id')->orWhere('type_id', $user->type_id);
            })
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', $request->input('subject_id')))
            ->when($request->filled('language'), fn ($q) => $q->where('language', $request->input('language')))
            ->ordered()
            ->get();

        $subjects = $this->groupBySubject($videos, $entitled, $user);

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => [
                'general_videos' => $this->presentMany($videos->whereNull('subject_id'), $entitled, $user),
                'subjects'       => $subjects,
            ],
        ]);
    }

    public function show(Request $request, Video $video)
    {
        $user = $this->resolveUser($request);
        $video->load(['subject', 'chapter', 'type', 'user']);
        $entitled = $this->isEntitled($user, $video->subject);

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => $this->present($video, $entitled, $user),
        ]);
    }

    /* ------------------------------------------------------------------ */
    /* Write                                                               */
    /* ------------------------------------------------------------------ */

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(false));

        $video = new Video();
        $this->fillFromRequest($video, $request, $data);

        if (!$video->file_path) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'A video file is required.',
            ], 422);
        }

        $video->save();

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $this->present($video->load(['subject', 'chapter', 'type', 'user']), true, null),
        ], 201);
    }

    public function update(Request $request, Video $video)
    {
        $data = $request->validate($this->rules(true));

        $this->fillFromRequest($video, $request, $data);
        $video->save();

        return $this->jsonResponse([
            'status' => 'success',
            'data'   => $this->present($video->load(['subject', 'chapter', 'type', 'user']), true, null),
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

    private function rules(bool $partial): array
    {
        $required = $partial ? 'sometimes' : 'required';
        $optional = 'sometimes|nullable';

        return [
            'type_id'     => $optional . '|exists:types,id',
            'subject_id'  => $optional . '|exists:subjects,id',
            'chapter_id'  => $optional . '|exists:chapters,id',
            'user_id'     => $optional . '|exists:users,id',
            'title'       => $required . '|string|max:255',
            'description' => $optional . '|string',
            'video'       => ($partial ? 'sometimes' : 'required')
                             . '|file|mimetypes:' . self::VIDEO_MIMETYPES . '|max:' . self::MAX_VIDEO_KB,
            'thumbnail'   => $optional . '|image|max:2048',
            'duration'    => $optional . '|integer|min:0',
            'grade'       => $optional . '|integer|min:0|max:12',
            'language'    => $partial ? 'sometimes|in:amharic,afan_oromo,english,tigrinya,somali,afar,other'
                                      : 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
            'sort_order'  => $optional . '|integer|min:0',
            'is_active'   => $optional . '|boolean',
        ];
    }

    private function fillFromRequest(Video $video, Request $request, array $data): void
    {
        foreach (['type_id', 'subject_id', 'chapter_id', 'user_id', 'title', 'description',
                  'duration', 'grade', 'language', 'sort_order', 'is_active'] as $field) {
            if (array_key_exists($field, $data)) {
                $video->{$field} = $data[$field];
            }
        }

        if ($request->hasFile('video')) {
            if ($video->file_path) {
                Storage::disk(Video::DISK)->delete($video->file_path);
            }

            $file = $request->file('video');
            $video->file_path = $file->store('videos', Video::DISK);
            $video->mime_type = $file->getMimeType();
            $video->file_size = $file->getSize();
            $video->checksum  = md5_file(Storage::disk(Video::DISK)->path($video->file_path));
        }

        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail_path) {
                Storage::disk(Video::THUMB_DISK)->delete($video->thumbnail_path);
            }
            $video->thumbnail_path = $request->file('thumbnail')->store('videos/thumbnails', Video::THUMB_DISK);
        }

        if (!$video->language) {
            $video->language = 'english';
        }
    }
}
