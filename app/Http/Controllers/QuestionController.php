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

        return response()->json([
            'status' => true,
            'data' => $types
        ]);
    }
     

    public function getQuestionsByYear(Request $request)
    {
        // Validate year input
        Log::info('Getting questions for year: ' . $request->year);
        if (!$request->year) {
            return response()->json([
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
        
        return response()->json([
            'status'   => 'success',
            'response' => $response
        ]);
        
    }

    public function getQuestionsBySubject(Request $request)
    {
        // Validate subject input
        Log::info('Getting questions for subject: ' . $request->subject);
        if (!$request->subject) {
            return response()->json([
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

        return response()->json([
            'status'   => 'success',
            'response' => $response
        ]);
    }

    public function getQuestionsByType(Request $request)
    {
        // Validate type input
        Log::info('Getting questions for type: ' . $request->type);
        if (!$request->type) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid type provided.'
            ], 400);
        }

        $type = $request->type;
        // Format response – mapping the subjects and their questions accordingly.
        $questions = Question::where('type_id', $type)
            ->with(['choices', 'subject', 'yearGroup'])->get();

        $response = $questions->groupBy(function ($question) {
            return $question->subject->name;
        });

        return response()->json([
            'status'   => 'success',
            'response' => $response
        ]);
    }

    public function getAllQuestionsGroupedByType()
    {
        // Fetch all questions and group them by type
        $questions = Question::with(['choices', 'subject','chapter', 'type'])->get();

        $response = $questions->groupBy(function ($question) {
            return $question->type->name; // Assuming 'name' is the field in the types table
        });

        return response()->json([
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

    return response()->json([
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

        return response()->json([
            'status' => 'success',
            'subjects' => $subjects,
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
                'status' => false,
                'message' => 'No exam type associated with this user.'
            ], 400);
        }
    
        // Get random sample questions for the user's type
        $questions = Question::where('type_id', $user->type_id)
            ->with(['choices', 'subject', 'yearGroup', 'chapter'])
            ->inRandomOrder()
            ->limit(5)
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'subject_id' => $question->subject_id,
                    'year_group_id' => $question->year_group_id,
                    'chapter_id' => $question->chapter_id,
                    'question_text' => $question->question_text,
                    'question_image_path' => $question->question_image_path 
                        ? asset('storage/' . $question->question_image_path) 
                        : null,
                    // 'formula' => $question->formula,
                    'answer_text' => $question->answer_text,
                    'explanation' => $question->explanation,
                    'explanation_image_path' => $question->explanation_image_path 
                        ? asset('storage/' . $question->explanation_image_path) 
                        : null,
                    'type_id' => $question->type_id,
                    'duration' => $question->duration,
                    'subject' => $question->subject->name,
                    'year_group' => $question->yearGroup ? $question->yearGroup->name : null,
                    'chapter' => $question->chapter ? $question->chapter->name : null,
                    'choices' => $question->choices->map(function ($choice) {
                        return [
                            'id' => $choice->id,
                            'question_id' => $choice->question_id,
                            'choice_text' => $choice->choice_text,
                            'choice_image_path' => $choice->choice_image_path 
                                ? asset('storage/' . $choice->choice_image_path) 
                                : null,
                            'formula' => $choice->formula,
                        ];
                    }),
                ];
            });
    
        if ($questions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No sample questions available for this exam type.'
            ], 404);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Sample questions retrieved successfully.',
            'type_name' => Type::find($user->type_id)->name,
            'questions_count' => $questions->count(),
            'questions' => $questions
        ]);
    }


    

    public function getAllQuestionsGroupedBySubject()
    {
        // Fetch all questions and group them by subject
        $questions = Question::with(['choices', 'subject', 'type'])->get();

        $response = $questions->groupBy(function ($question) {
            return $question->subject->name; // Grouping by subject name
        });

        return response()->json([
            'status'   => 'success',
            'response' => $response
        ]);
    }
}

