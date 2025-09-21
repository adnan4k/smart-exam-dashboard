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
    public function index(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $notes = Note::with(['subject', 'chapter', 'user', 'type'])
            ->where('subject_id', $request->input('subject_id'))
            ->where('chapter_id', $request->input('chapter_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $notes,
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
        ]);

        $note = Note::create([
            'user_id' => $request->input('user_id'),
            'type_id' => $request->input('type_id'),
            'subject_id' => $request->input('subject_id'),
            'chapter_id' => $request->input('chapter_id'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $note->load(['subject', 'chapter', 'user', 'type']),
        ], 201);
    }

    public function show(Note $note)
    {
        return response()->json([
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
        ]);

        $note->update($request->only(['user_id', 'type_id', 'subject_id', 'chapter_id', 'title', 'content']));

        return response()->json([
            'status' => 'success',
            'data' => $note->load(['subject', 'chapter', 'user', 'type']),
        ]);
    }

    public function destroy(Note $note)
    {
        $note->delete();

        return response()->json([
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
            return response()->json([
                'status' => 'error',
                'message' => 'No exam type associated with this user.',
            ], 400);
        }

        $hasActiveSubscription = $user->subscriptions()
            ->where('type_id', $user->type_id)
            ->where('payment_status', 'paid')
            ->exists();

        if (!$hasActiveSubscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active subscription found.',
            ], 403);
        }

        $subject = Subject::findOrFail($request->input('subject_id'));
        if ((int) $subject->type_id !== (int) $user->type_id) {
            return response()->json([
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

        return response()->json([
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
            return response()->json([
                'status' => 'error',
                'message' => 'No exam type associated with this user.',
            ], 400);
        }

        $hasActiveSubscription = $user->subscriptions()
            ->where('type_id', $user->type_id)
            ->where('payment_status', 'paid')
            ->exists();

        if (!$hasActiveSubscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active subscription found.',
            ], 403);
        }

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
                        'grade' => $note->subject->type_id, // Using type_id as grade
                        'chapterId' => (string) $note->chapter_id,
                        'chapterName' => $note->chapter->name,
                        'createdAt' => $note->created_at->toISOString(),
                        'updatedAt' => $note->updated_at->toISOString(),
                    ];
                });

                $chaptersData[] = [
                    'id' => (string) $chapter->id,
                    'name' => $chapter->name,
                    'subjectId' => (string) $subject->id,
                    'grade' => $subject->type_id, // Using type_id as grade
                    'notes' => $notesData,
                ];
            }

            $subjectsData[] = [
                'id' => (string) $subject->id,
                'name' => $subject->name,
                'grade' => $subject->type_id, // Using type_id as grade
                'iconName' => 'book', // Default icon, you can customize this
                'chapters' => $chaptersData,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $subjectsData,
            'is_subscribed' => true,
        ]);
    }
}
