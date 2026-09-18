<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\RespondsWithJson;
use App\Models\User;
use App\Services\SubjectContentService;
use Illuminate\Http\Request;

/**
 * Per-subject download endpoints.
 *
 * The app is offline-first: it renders whatever it has cached, and downloads
 * subjects one at a time on demand. That splits into two calls with very
 * different cost profiles, which is why they are separate endpoints:
 *
 *  - `catalogue` is small and called on every launch. It must never carry
 *    question or note bodies.
 *  - `content` is large and called once per subject the user chooses to
 *    download.
 *
 * Both go through RespondsWithJson so the response carries Content-Length and
 * the app can show a real progress bar instead of a spinner.
 */
class SubjectController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SubjectContentService $subjects)
    {
    }

    /**
     * GET /api/subjects/catalogue?user_id=123
     *
     * One entry per distinct subject name available to this user, with the
     * counts, size estimate and content version the subject grid needs to
     * render download state before anything is downloaded.
     */
    public function catalogue(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if (! $user->type_id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No exam type associated with this user.',
            ], 400);
        }

        return $this->jsonResponse([
            'status' => 'success',
            'is_subscribed' => $this->subjects->isSubscribed($user),
            'free_question_limit' => SubjectContentService::FREE_QUESTION_LIMIT,
            'data' => $this->subjects->catalogue($user)->all(),
        ]);
    }

    /**
     * GET /api/subjects/content?user_id=123&subject=Biology
     *
     * Everything the app needs to make one subject fully usable offline:
     * questions across every year and region filed under that name, plus that
     * subject's notes.
     *
     * Pass `known_version` (the `content_version` from a previous download) to
     * skip the payload when nothing has changed — the response comes back as
     * `status: not_modified` with no bodies attached.
     */
    public function content(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'known_version' => 'nullable|string|max:64',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if (! $user->type_id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No exam type associated with this user.',
            ], 400);
        }

        $subjectName = $request->input('subject');
        $variants = $this->subjects->variantsFor($user, $subjectName);

        if ($variants->isEmpty()) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => "No subject named '{$subjectName}' is available for this user.",
            ], 404);
        }

        $entry = $this->subjects->catalogue($user)
            ->firstWhere('key', $subjectName);

        $knownVersion = $request->input('known_version');

        if ($knownVersion !== null && $entry !== null && $entry['content_version'] === $knownVersion) {
            return $this->jsonResponse([
                'status' => 'not_modified',
                'subject' => $entry,
            ]);
        }

        $isSubscribed = $this->subjects->isSubscribed($user);

        return $this->jsonResponse([
            'status' => 'success',
            'is_subscribed' => $isSubscribed,
            'subject' => $entry,
            'questions' => $this->subjects->questionsFor($variants, $isSubscribed, $user->type_id),
            'notes' => $this->subjects->notesFor($variants, $subjectName, $user->type_id),
        ]);
    }
}
