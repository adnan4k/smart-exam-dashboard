<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
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
        // Format response – mapping the subjects and their questions accordingly.
        $questions = Question::whereHas('yearGroup', function ($q) use ($year) {
            $q->where('year', $year);
        })->with(['choices', 'subject', 'yearGroup'])->get();

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
        $questions = Question::where('type', $type)
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
        $questions = Question::with(['choices', 'subject', 'type'])->get();

        $response = $questions->groupBy(function ($question) {
            return $question->type->name; // Assuming 'name' is the field in the types table
        });

        return response()->json([
            'status'   => 'success',
            'response' => $response
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

