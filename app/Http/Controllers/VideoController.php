<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Subject;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
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
     * Mirrors NoteController::forUser — an active paid subscription for the
     * user's own exam type. Downloads leave our control permanently, so this
     * is checked again at the moment the file is served, never trusted from
     * an earlier listing call.
     */
    private function isEntitled(?User $user): bool
    {
        if (!$user || !$user->type_id) {
            return false;
        }

        return $user->subscriptions()
            ->where('type_id', $user->type_id)
            ->where('payment_status', 'paid')
            ->exists();
    }

    private function resolveUser(Request $request): ?User
    {
        return $request->filled('user_id')
            ? User::find($request->input('user_id'))
            : null;
    }

    /**
     * Shape a video for the client: metadata always, the download link only
     * when the caller is entitled to it.
     */
    private function present(Video $video, bool $entitled, ?int $userId): array
    {
        $data = $video->toArray();

        $data['locked']       = !$entitled;
        $data['download_url'] = $entitled ? $video->downloadUrl($userId) : null;

        return $data;
    }

    private function presentMany($videos, bool $entitled, ?int $userId): array
    {
        return collect($videos)->map(fn ($v) => $this->present($v, $entitled, $userId))->values()->all();
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

        if (!$this->isEntitled($user)) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'An active subscription is required to download this video.',
            ], 403);
        }

        // A video scoped to another exam type is not this user's to download.
        if ($video->type_id && (int) $video->type_id !== (int) $user->type_id) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'This video is not available for your exam type.',
            ], 403);
        }

        if (!$video->is_active) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'This video is not currently available.',
            ], 404);
        }

        if (!$video->fileExists()) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Video file is missing on the server.',
            ], 404);
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
            'data'     => $this->presentMany($videos->items(), $entitled, $user?->id),
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
        ]);

        $user = $this->resolveUser($request);
        $entitled = $this->isEntitled($user);
        $subject = Subject::findOrFail($request->input('subject_id'));

        $videos = Video::with(['chapter', 'type'])
            ->active()
            ->forSubject($subject->id)
            ->ordered()
            ->get();

        $chapters = $videos->whereNotNull('chapter_id')
            ->groupBy('chapter_id')
            ->map(fn ($group, $chapterId) => [
                'chapter_id'   => (int) $chapterId,
                'chapter_name' => optional($group->first()->chapter)->name,
                'videos'       => $this->presentMany($group, $entitled, $user?->id),
            ])->values();

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => [
                'subject_id'     => (int) $subject->id,
                'subject_name'   => $subject->name,
                'subject_videos' => $this->presentMany($videos->whereNull('chapter_id'), $entitled, $user?->id),
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
        ]);

        $user = $this->resolveUser($request);
        $entitled = $this->isEntitled($user);
        $chapter = Chapter::findOrFail($request->input('chapter_id'));

        $videos = Video::with(['subject', 'type'])
            ->active()
            ->forChapter($chapter->id)
            ->when($request->filled('subject_id'), fn ($q) => $q->where('subject_id', $request->input('subject_id')))
            ->ordered()
            ->get();

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => [
                'chapter_id'   => (int) $chapter->id,
                'chapter_name' => $chapter->name,
                'videos'       => $this->presentMany($videos, $entitled, $user?->id),
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

        $entitled = $this->isEntitled($user);

        $videos = Video::with(['subject', 'chapter', 'type'])
            ->active()
            ->where(function ($q) use ($user) {
                $q->whereNull('type_id')->orWhere('type_id', $user->type_id);
            })
            ->ordered()
            ->get();

        $subjects = $videos->whereNotNull('subject_id')
            ->groupBy('subject_id')
            ->map(function ($subjectVideos, $subjectId) use ($entitled, $user) {
                $chapters = $subjectVideos->whereNotNull('chapter_id')
                    ->groupBy('chapter_id')
                    ->map(fn ($group, $chapterId) => [
                        'chapter_id'   => (int) $chapterId,
                        'chapter_name' => optional($group->first()->chapter)->name,
                        'videos'       => $this->presentMany($group, $entitled, $user->id),
                    ])->values();

                return [
                    'subject_id'     => (int) $subjectId,
                    'subject_name'   => optional($subjectVideos->first()->subject)->name,
                    'subject_videos' => $this->presentMany($subjectVideos->whereNull('chapter_id'), $entitled, $user->id),
                    'chapters'       => $chapters,
                ];
            })->values();

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => [
                'general_videos' => $this->presentMany($videos->whereNull('subject_id'), $entitled, $user->id),
                'subjects'       => $subjects,
            ],
        ]);
    }

    public function show(Request $request, Video $video)
    {
        $user = $this->resolveUser($request);
        $entitled = $this->isEntitled($user);

        $video->load(['subject', 'chapter', 'type', 'user']);

        return $this->jsonResponse([
            'status'   => 'success',
            'entitled' => $entitled,
            'data'     => $this->present($video, $entitled, $user?->id),
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
