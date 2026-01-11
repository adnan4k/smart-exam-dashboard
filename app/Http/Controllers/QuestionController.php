<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;

class QuestionController extends Controller
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
            $compressed = gzencode($json, 6); // Level 6 is good balance between speed and compression

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
        // Validate subject input
        Log::info('Getting questions for subject: ' . $request->subject);
        if (!$request->subject) {
            return $this->jsonResponse([
                'status'  => 'error',
                'message' => 'Invalid subject provided.'
            ], 400);
        }

        $subject = $request->subject;
        // Format response – mapping the years and their questions accordingly.
        $questions = Question::where('subject_id', $subject)
            ->with(['choices', 'yearGroup'])->get();

        $response = $questions->groupBy(function ($question) {
            return $question->yearGroup->year;
        });

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
            // For non-subscribed users, get only 5 random questions as samples
            // For non-subscribed users, get up to 40 questions per subject (all subjects for this type)
            $subjectIds = \App\Models\Subject::where('type_id', $user->type_id)->pluck('id');
            $questions = collect();
            foreach ($subjectIds as $subjectId) {
                $subjectQuestions = Question::where('type_id', $user->type_id)
                    ->where('subject_id', $subjectId)
                    ->with(['choices', 'subject', 'yearGroup', 'chapter'])
                    ->orderBy('id', 'asc')
                    ->limit(40)
                    ->get();
                $questions = $questions->concat($subjectQuestions);
            }
        }

        // Group questions by subject
        $response = $questions->groupBy(function ($question) {
            return optional($question->subject)->name ?? 'Unknown Subject';
        });

        // Transform the questions to include all necessary data
        $response = $response->map(function ($questions) {
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

