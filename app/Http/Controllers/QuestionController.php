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
}

