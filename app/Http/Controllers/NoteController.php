<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    /**
     * Helper method to add Content-Length header to JSON response with gzip compression
     */
    private function jsonResponse($data, $status = 200)
    {
        // Disable any output buffering
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Encode to JSON with consistent options
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Calculate uncompressed size
        $uncompressedLength = strlen($json);

        // Check if client accepts gzip (most modern clients do)
        $acceptEncoding = request()->header('Accept-Encoding', '');
        $useGzip = stripos($acceptEncoding, 'gzip') !== false;

        if ($useGzip) {
            // Compress the JSON using gzip
            $compressed = gzencode($json, 9); // Level 9 is maximum compression

            // Calculate compressed size
            $contentLength = strlen($compressed);

            // Create response with compressed JSON
            $response = response($compressed, $status)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Encoding', 'gzip')
                ->header('Content-Length', (string)$contentLength)
                ->header('X-Uncompressed-Size', (string)$uncompressedLength); // Debug header
        } else {
            // No compression - send as-is
            $contentLength = $uncompressedLength;

            $response = response($json, $status)
                ->header('Content-Type', 'application/json; charset=UTF-8')
                ->header('Content-Length', (string)$contentLength);
        }

        // Force the response to not use chunked encoding
        $response->headers->remove('Transfer-Encoding');

        return $response;
    }

    public function index(Request $request)
    {
        // Validate filter parameters
        $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'type_id' => 'nullable|exists:types,id',
            'user_id' => 'nullable|exists:users,id',
            'grade' => 'nullable|integer|min:0|max:12',
            'language' => 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = Note::with(['subject', 'chapter', 'user', 'type']);

        // Apply filters
        if ($request->has('subject_id') && $request->input('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->has('chapter_id') && $request->input('chapter_id')) {
            $query->where('chapter_id', $request->input('chapter_id'));
        }

        if ($request->has('type_id') && $request->input('type_id')) {
            $query->where('type_id', $request->input('type_id'));
        }

        if ($request->has('user_id') && $request->input('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('grade') && $request->input('grade') !== null) {
            $query->where('grade', $request->input('grade'));
        }

        if ($request->has('language') && $request->input('language')) {
            $query->where('language', $request->input('language'));
        }

        // Search filter (searches in title and content)
        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('content', 'like', '%' . $searchTerm . '%');
            });
        }

        // Date range filters
        if ($request->has('date_from') && $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to') && $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Order by
        $query->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->input('per_page', 15);
        $notes = $query->paginate($perPage);

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $notes->items(),
            'pagination' => [
                'current_page' => $notes->currentPage(),
                'last_page' => $notes->lastPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
                'from' => $notes->firstItem(),
                'to' => $notes->lastItem(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type_id' => 'required|exists:types,id',
            'subject_id' => 'required|exists:subjects,id',
            'chapter_id' => 'required|exists:chapters,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'user_id' => 'nullable|exists:users,id',
            'grade' => 'nullable|integer|min:1|max:12',
            'language' => 'required|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
        ]);

        $note = Note::create([
            'user_id' => $request->input('user_id'),
            'type_id' => $request->input('type_id'),
            'subject_id' => $request->input('subject_id'),
            'chapter_id' => $request->input('chapter_id'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'grade' => $request->input('grade'),
            'language' => $request->input('language'),
        ]);

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $note->load(['subject', 'chapter', 'user', 'type']),
        ], 201);
    }

    public function show(Note $note)
    {
        return $this->jsonResponse([
            'status' => 'success',
            'data' => $note->load(['subject', 'chapter', 'user', 'type']),
        ]);
    }

    public function update(Request $request, Note $note)
    {
        $request->validate([
            'type_id' => 'sometimes|exists:types,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'chapter_id' => 'sometimes|exists:chapters,id',
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'user_id' => 'sometimes|nullable|exists:users,id',
            'grade' => 'sometimes|nullable|integer|min:1|max:12',
            'language' => 'sometimes|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
        ]);

        $note->update($request->only(['user_id', 'type_id', 'subject_id', 'chapter_id', 'title', 'content', 'grade', 'language']));

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $note->load(['subject', 'chapter', 'user', 'type']),
        ]);
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return $this->jsonResponse([
            'status' => 'success',
            'message' => 'Note deleted successfully',
        ]);
    }

    public function forUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        if (!$user->type_id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No exam type associated with this user.',
            ], 400);
        }

        $hasActiveSubscription = $user->subscriptions()
            ->where('type_id', $user->type_id)
            ->where('payment_status', 'paid')
            ->exists();

        if (!$hasActiveSubscription) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No active subscription found.',
            ], 403);
        }

        $subject = Subject::findOrFail($request->input('subject_id'));
        if ((int) $subject->type_id !== (int) $user->type_id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Subject does not match user\'s exam type.',
            ], 403);
        }

        $notes = Note::with(['subject', 'chapter', 'type'])
            ->where('subject_id', $request->input('subject_id'))
            ->where('chapter_id', $request->input('chapter_id'))
            ->where(function($q) use ($user) {
                $q->whereNull('type_id')->orWhere('type_id', $user->type_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $notes,
            'is_subscribed' => true,
        ]);
    }

    public function forUserGrouped(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        if (!$user->type_id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No exam type associated with this user.',
            ], 400);
        }

        $hasActiveSubscription = $user->subscriptions()
            ->where('type_id', $user->type_id)
            ->where('payment_status', 'paid')
            ->exists();

        // if (!$hasActiveSubscription) {
        //     return response()->json([
        //         'status' => 'success',
        //         'message' => 'No active subscription found.',
        //         'data' => [],
        //     ], 200);
        // }

        // Get all subjects for the user's exam type
        $subjects = Subject::where('type_id', $user->type_id)
            ->orderBy('name')
            ->get();

        $subjectsData = [];

        foreach ($subjects as $subject) {
            // Get all chapters for this subject that have notes
            $chapters = Chapter::whereHas('notes', function($query) use ($user, $subject) {
                $query->where('subject_id', $subject->id)
                      ->where(function($q) use ($user) {
                          $q->whereNull('type_id')->orWhere('type_id', $user->type_id);
                      });
            })->orderBy('name')->get();

            $chaptersData = [];

            foreach ($chapters as $chapter) {
                // Get notes for this chapter
                $notes = Note::with(['subject', 'chapter', 'type'])
                    ->where('subject_id', $subject->id)
                    ->where('chapter_id', $chapter->id)
                    ->where(function($q) use ($user) {
                        $q->whereNull('type_id')->orWhere('type_id', $user->type_id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get();

                $notesData = $notes->map(function($note) {
                    return [
                        'id' => (string) $note->id,
                        'title' => $note->title,
                        'content' => $note->content,
                        'subjectId' => (string) $note->subject_id,
                        'subjectName' => $note->subject->name,
                        'grade' => $note->grade, // now from notes table
                        'language' => $note->language,
                        'chapterId' => (string) $note->chapter_id,
                        'chapterName' => $note->chapter->name,
                        'createdAt' => $note->created_at->toISOString(),
                        'updatedAt' => $note->updated_at->toISOString(),
                    ];
                });

                $chaptersData[] = [
                    'id' => (string) $chapter->id,
                    'name' => $chapter->name,
                    'notes' => $notesData,
                ];
            }

            $subjectsData[] = [
                'id' => (string) $subject->id,
                'name' => $subject->name,
                'chapters' => $chaptersData,
            ];
        }

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $subjectsData,
        ]);
    }

    /**
     * Advanced filtering endpoint for notes
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function filter(Request $request)
    {
        $request->validate([
            'type_id' => 'nullable|exists:types,id',
            'subject_id' => 'nullable|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'user_id' => 'nullable|exists:users,id',
            'grade' => 'nullable|integer|min:0|max:12',
            'language' => 'nullable|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|in:created_at,updated_at,title,grade',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = Note::with(['subject', 'chapter', 'user', 'type']);

        // Apply filters
        if ($request->has('type_id') && $request->input('type_id')) {
            $query->where('type_id', $request->input('type_id'));
        }

        if ($request->has('subject_id') && $request->input('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        if ($request->has('chapter_id') && $request->input('chapter_id')) {
            $query->where('chapter_id', $request->input('chapter_id'));
        }

        if ($request->has('user_id') && $request->input('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('grade') && $request->input('grade') !== null) {
            $query->where('grade', $request->input('grade'));
        }

        if ($request->has('language') && $request->input('language')) {
            $query->where('language', $request->input('language'));
        }

        // Search filter (searches in title and content)
        if ($request->has('search') && $request->input('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('content', 'like', '%' . $searchTerm . '%');
            });
        }

        // Date range filters
        if ($request->has('date_from') && $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to') && $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $notes = $query->paginate($perPage);

        return $this->jsonResponse([
            'status' => 'success',
            'data' => $notes->items(),
            'pagination' => [
                'current_page' => $notes->currentPage(),
                'last_page' => $notes->lastPage(),
                'per_page' => $notes->perPage(),
                'total' => $notes->total(),
                'from' => $notes->firstItem(),
                'to' => $notes->lastItem(),
            ],
            'filters_applied' => $request->only([
                'type_id', 'subject_id', 'chapter_id', 'user_id', 
                'grade', 'language', 'search', 'date_from', 'date_to'
            ]),
        ]);
    }
}
