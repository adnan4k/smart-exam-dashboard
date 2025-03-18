<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Question;
use App\Models\Choice;
use App\Models\Subject;
use App\Models\Type;
use App\Models\YearGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class QuestionsController extends Controller
{
    public function create()
    {
        return view('questions.form', [
            'subjects' => Subject::all(),
            'types' => Type::all(),
            'yearGroups' => YearGroup::all(),
            'chapters' => Chapter::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subjectId' => 'required|exists:subjects,id',
            'yearGroupId' => 'required|exists:year_groups,id',
            'questionText' => 'required|string',
            'questionImage' => 'nullable|image|max:2048',
            'formula' => 'nullable|string',
            'answerText' => 'required|string',
            'explanation' => 'required|string',
            'explanationImage' => 'nullable|image|max:2048',
            'choices.*.text' => 'required|string',
            'choices.*.image' => 'nullable|image|max:2048',
            'choices.*.formula' => 'nullable|string',
        ]);

        // Handle file uploads and save question
        $questionImagePath = $request->file('questionImage') ? $request->file('questionImage')->store('questions/images', 'public') : null;
        $explanationImagePath = $request->file('explanationImage') ? $request->file('explanationImage')->store('explanations/images', 'public') : null;

        $question = Question::create([
            'subject_id' => $request->subjectId,
            'year_group_id' => $request->yearGroupId,
            'chapter_id' => $request->chapterId,
            'question_text' => $request->questionText,
            'question_image_path' => $questionImagePath,
            'formula' => $request->formula,
            'answer_text' => $request->answerText,
            'explanation' => $request->explanation,
            'explanation_image_path' => $explanationImagePath,
            'type_id' => $request->type,
        ]);

        foreach ($request->choices as $choiceData) {
            $choiceImagePath = $choiceData['image'] ? $choiceData['image']->store('choices/images', 'public') : null;

            Choice::create([
                'question_id' => $question->id,
                'choice_text' => $choiceData['text'],
                'choice_image_path' => $choiceImagePath,
                'formula' => $choiceData['formula'],
            ]);
        }

        Session::flash('success', 'Question Created Successfully!');
        return redirect()->route('questions.index'); // Redirect to the questions index or wherever appropriate
    }
} 