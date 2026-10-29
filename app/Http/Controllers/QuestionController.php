<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\RespondsWithJson;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Type;
use App\Models\User;
use App\Services\SubjectContentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;

class QuestionController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly SubjectContentService $subjects)
    {
    }

    public function examType()
    {
        // The mobile app hard-casts `decription` and `price` to String, so a null
        // in either column breaks the whole exam-type list during registration.
        $types = Type::all()->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => (string) $type->name,
                'decription' => (string) $type->description,
                'price' => (string) $type->price,
                'image' => $type->image ? asset('storage/' . $type->image) : null
            ];
        });

        return $this->jsonResponse([
            'status' => true,
            'data' => $types
        ]);
    }
     

    public function getQuestionsByYear(Request $request)
    {
        // Validate year input
        Log::info('Getting questions for year: ' . $request->year);
        if (!$request->year) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Invalid year provided.'
            ], 400);
        }

        $year = $request->year;

        // Fetch questions with necessary relations. Every exam type, as before;
        // only subjects the caller's active subscription covers come back in full.
        $questions = $this->entitledQuestions($this->optionalUser($request), null, function ($query) use ($year) {
            $query->whereHas('yearGroup', fn ($q) => $q->where('year', $year))
                ->with(['choices', 'subject', 'yearGroup']);
        })->sortBy('id')->values();
        
        // Map full image URLs
        $questions->transform(function ($question) {
            // Convert image paths to full URLs
            $question->question_image_path = $question->question_image_path 
                ? url('storage/' . $question->question_image_path) 
                : null;
        
            $question->explanation_image_path = $question->explanation_image_path 
                ? url('storage/' . $question->explanation_image_path) 
                : null;
        
            // Map choices' image paths
            if ($question->relationLoaded('choices')) {
                $question->choices->transform(function ($choice) {
                    $choice->choice_image_path = $choice->choice_image_path 
                        ? url('storage/' . $choice->choice_image_path) 
                        : null;
                    return $choice;
                });
            }
        
            return $question;
        });
        
        // Group by subject name
        $response = $questions->groupBy(function ($question) {
            return $question->subject->name;
        });
        
        return $this->jsonResponse([
            'status'   => 'success',
            'response' => $response
        ]);
        
    }

    public function getQuestionsBySubject(Request $request)
    {
        // `user_id` is required: without it this endpoint served every question
        // for a subject to any caller who could guess a subject id, bypassing
        // the subscription cap that getQuestionsByType applies.
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|exists:subjects,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));

        if (!$user->type_id) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'No exam type associated with this user.'
            ], 400);
        }

        Log::info('Getting questions for subject: ' . $request->input('subject'));

        $query = Question::where('subject_id', $request->input('subject'))
            ->where('type_id', $user->type_id)
            ->with(['choices', 'subject', 'yearGroup', 'chapter'])
            ->orderBy('id', 'asc');

        $subject = Subject::find($request->input('subject'));
        $isEntitled = $subject ? $user->canAccessSubject($subject) : $this->subjects->isSubscribed($user);

        if (!$isEntitled) {
            $query->limit(SubjectContentService::FREE_QUESTION_LIMIT);
        }

        // Grouped by year, and run through the shared mapper so image paths are
        // absolute and the subject/chapter relations the app parses are present.
        $response = $query->get()
            ->groupBy(fn ($question) => optional($question->yearGroup)->year ?? 'Unknown')
            ->map(fn ($questions) => $questions->map(
                fn ($question) => $this->subjects->transformQuestion($question)
            )->values());

        return $this->jsonResponse([
            'status'   => 'success',
            'response' => $response
        ]);
    }

    public function getQuestionsByType(Request $request)
    {
        // Use explicit user id from the request payload instead of auth context
        $userId = $request->input('user_id');

        if (!$userId) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'user_id is required in the request body.'
            ], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'User not found.'
            ], 404);
        }

        // Check if user has type_id
        if (!$user->type_id) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No exam type associated with this user.'
            ], 400);
        }

        // Check if user has any active package subscription
        $hasActiveSubscription = $user->hasPaidPackage();

        $questions = $this->entitledQuestions($user, $user->type_id, fn ($query) => $query->with(['choices', 'subject', 'yearGroup', 'chapter']));

        // Group questions by subject
        $response = $questions->groupBy(function ($question) {
            return optional($question->subject)->name ?? 'Unknown Subject';
        });

        // Transform through the shared mapper so this bulk endpoint and the
        // per-subject endpoints can never drift apart in shape.
        $response = $response->map(fn ($questions) => $questions->map(
            fn ($question) => $this->subjects->transformQuestion($question)
        ));

        return $this->jsonResponse([
            'status' => 'success',
            'response' => $response,
            'is_subscribed' => $hasActiveSubscription
        ]);
    }

    public function sampleQuestions(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);
    
        $user = User::findOrFail($request->user_id);
    
        if (!$user->type_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No exam type associated with this user.'
            ], 400);
        }
    
        $questions = Question::where('type_id', $user->type_id)
            ->whereHas('subject', function($q) {
                $q->where('is_sample', true);
            })
            ->with(['choices', 'subject', 'yearGroup', 'chapter'])
            ->orderBy('id', 'asc')
            ->limit(5)
            ->get();
    
        if ($questions->isEmpty()) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No sample questions available for this exam type.'
            ], 404);
        }
    
        $grouped = $questions->groupBy(function ($question) {
            return optional($question->subject)->name ?? 'Unknown Subject';
        });
    
        $response = $grouped->map(function ($questions) {
            return $questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'correct_choice_id' => $question->answer_id,
                    'subject_id' => $question->subject_id,
                    'year_group_id' => $question->year_group_id,
                    'chapter_id' => $question->chapter_id,
                    'question_text' => $question->question_text,
                    'question_image_path' => $question->question_image_path 
                        ? asset('storage/' . $question->question_image_path) 
                        : null,
                    'formula' => $question->formula,
                    'explanation' => $question->explanation,
                    'explanation_image_path' => $question->explanation_image_path 
                        ? asset('storage/' . $question->explanation_image_path) 
                        : null,
                    'created_at' => $question->created_at,
                    'updated_at' => $question->updated_at,
                    'type_id' => $question->type_id,
                    'duration' => $question->duration,
                    'choices' => $question->choices->map(function ($choice) {
                        return [
                            'id' => $choice->id,
                            'question_id' => $choice->question_id,
                            'choice_text' => $choice->choice_text,
                            'choice_image_path' => $choice->choice_image_path 
                                ? asset('storage/' . $choice->choice_image_path) 
                                : null,
                            'formula' => $choice->formula,
                            'created_at' => $choice->created_at,
                            'updated_at' => $choice->updated_at,
                        ];
                    }),
                    'subject' => $question->subject,
                    'chapter' => $question->chapter,
                    'year_group' => $question->yearGroup,
                ];
            });
        });
    
        return $this->jsonResponse([
            'status' => 'success',
            'response' => $response
        ]);
    }
    

    public function getAllQuestionsGroupedByType(Request $request)
    {
        $questions = $this->entitledQuestions($this->optionalUser($request), null, fn ($query) => $query->with(['choices', 'subject', 'type', 'chapter']))
            ->sortBy('id')
            ->values();

        $response = $questions->groupBy(function ($question) {
            return $question->type->name; // Assuming 'name' is the field in the types table
        });

        return $this->jsonResponse([
            'status'   => 'success',
            'response' => $response
        ]);
    }
    public function availableChapters(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    $user = User::findOrFail($request->user_id);

    // Get all chapter_ids from questions with the user's type_id
    $chapterIds = Question::where('type_id', $user->type_id)
        ->pluck('chapter_id')
        ->unique();

    $chapters = Chapter::whereIn('id', $chapterIds)->get();

        return $this->jsonResponse([
            'status' => 'success',
            'chapters' => $chapters,
        ]);
}


    /**
     * @deprecated Superseded by GET /api/subjects/catalogue, which groups the
     * (name, year, region) rows into one entry per subject and carries the
     * counts, size estimate and content version the app needs. Kept because
     * removing a public route is a breaking change.
     */
    public function availableSubjects(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        // Get all subject_ids from questions with the user's type_id
        $subjectIds = Question::where('type_id', $user->type_id)
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->get();

        return $this->jsonResponse([
            'status' => 'success',
            'subjects' => $subjects,
        ]);
    }

  


    

    public function getAllQuestionsGroupedBySubject(Request $request)
    {
        $questions = $this->entitledQuestions($this->optionalUser($request), null, fn ($query) => $query->with(['choices', 'subject', 'type']))
            ->sortBy('id')
            ->values();

        $response = $questions->groupBy(function ($question) {
            return $question->subject->name; // Grouping by subject name
        });

        return $this->jsonResponse([
            'status'   => 'success',
            'response' => $response
        ]);
    }

    /**
     * The questions a caller is allowed to see, decided per subject:
     * everything for a subject the user's active subscription covers (or a
     * sample subject), otherwise that subject's free preview. A null `$user`
     * gets only previews; a null `$typeId` means every exam type.
     *
     * The preview is pinned to the subject's first FREE_QUESTION_LIMIT questions
     * overall, before `$scope` narrows them, so filtering by year cannot be used
     * to page through a different 40 questions per year.
     */
    private function entitledQuestions(?User $user, ?int $typeId, ?callable $scope = null)
    {
        $subjects = $typeId
            ? Subject::where('type_id', $typeId)->get()
            : Subject::whereIn('id', Question::whereNotNull('subject_id')->distinct()->pluck('subject_id'))->get();

        if ($subjects->isEmpty()) {
            return collect();
        }

        $entitledIds = $user
            ? $user->accessibleSubjectIds($subjects)
            : $subjects->filter(fn (Subject $subject) => (bool) $subject->is_sample)->pluck('id');

        // A locked subject's preview is its questions up to and including the
        // FREE_QUESTION_LIMIT-th one, so it is expressed as an id cutoff.
        $lockedIds = $subjects->pluck('id')->diff($entitledIds)->values();
        $previewCutoffs = $lockedIds->isEmpty() ? collect() : Question::query()
            ->whereIn('subject_id', $lockedIds)
            ->when($typeId, fn ($q) => $q->where('type_id', $typeId))
            ->orderBy('id')
            ->toBase()
            ->get(['id', 'subject_id'])
            ->groupBy('subject_id')
            ->map(fn ($rows) => $rows->take(SubjectContentService::FREE_QUESTION_LIMIT)->last()->id);

        // One query for every subject. Ordered by subject, then id, which is the
        // order the old one-query-per-subject loop produced.
        $query = Question::query()
            ->when($typeId, fn ($q) => $q->where('type_id', $typeId))
            ->where(function ($q) use ($entitledIds, $previewCutoffs) {
                $q->whereIn('subject_id', $entitledIds);

                foreach ($previewCutoffs as $subjectId => $cutoff) {
                    $q->orWhere(fn ($preview) => $preview->where('subject_id', $subjectId)->where('id', '<=', $cutoff));
                }
            })
            ->orderBy('subject_id')
            ->orderBy('id');

        if ($scope) {
            $scope($query);
        }

        return $query->get();
    }

    /**
     * These endpoints never required a user, so `user_id` stays optional and an
     * unknown one is treated as anonymous rather than turned into a new error.
     */
    private function optionalUser(Request $request): ?User
    {
        return $request->filled('user_id') ? User::find($request->input('user_id')) : null;
    }
}

