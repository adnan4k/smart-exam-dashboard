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
        $types = Type::all()->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'decription'=>$type->description,
                'price'=>$type->price,
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

        // Fetch questions with necessary relations
        $questions = Question::whereHas('yearGroup', function ($q) use ($year) {
            $q->where('year', $year);
        })->with(['choices', 'subject', 'yearGroup'])->get();
        
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

        if (!$this->subjects->isSubscribed($user)) {
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

        // Check if user has an active subscription
        $hasActiveSubscription = $user->subscriptions()
            ->where('payment_status', 'paid')
            ->exists();

        // Get questions based on subscription status
        if ($hasActiveSubscription) {
            // For subscribed users, get all questions
            $questions = Question::where('type_id', $user->type_id)
                ->with(['choices', 'subject', 'yearGroup', 'chapter'])
                ->orderBy('id', 'asc')
                ->get();
        } else {
            // Unsubscribed users get a capped preview per subject.
            $subjectIds = Subject::where('type_id', $user->type_id)->pluck('id');
            $questions = collect();
            foreach ($subjectIds as $subjectId) {
                $subjectQuestions = Question::where('type_id', $user->type_id)
                    ->where('subject_id', $subjectId)
                    ->with(['choices', 'subject', 'yearGroup', 'chapter'])
                    ->orderBy('id', 'asc')
                    ->limit(SubjectContentService::FREE_QUESTION_LIMIT)
                    ->get();
                $questions = $questions->concat($subjectQuestions);
            }
        }

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
    

    public function getAllQuestionsGroupedByType()
    {
        // Fetch all questions and group them by type
        $questions = Question::with(['choices', 'subject', 'type','chapter'])->get();

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

  


    

    public function getAllQuestionsGroupedBySubject()
    {
        // Fetch all questions and group them by subject
        $questions = Question::with(['choices', 'subject', 'type'])->get();

        $response = $questions->groupBy(function ($question) {
            return $question->subject->name; // Grouping by subject name
        });

        return $this->jsonResponse([
            'status'   => 'success',
            'response' => $response
        ]);
    }
}

