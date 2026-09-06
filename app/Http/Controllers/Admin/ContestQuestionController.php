<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Choice;
use App\Models\ContestQuestion;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Authoring for the contest bank.
 *
 * Contest questions are hidden by the global scope on Question, so the ordinary
 * question screens cannot see or edit them - which is the point, since a leaked
 * paper ends a contest's credibility. This controller is the one place that
 * works with them, and it opts out of the scope explicitly.
 */
class ContestQuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::contestPool()
            ->with('subject:id,name', 'type:id,name')
            ->when($request->input('subject_id'), fn ($q, $id) => $q->where('subject_id', $id))
            ->when($request->input('search'), fn ($q, $s) => $q->where('question_text', 'like', "%{$s}%"))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $usedIds = ContestQuestion::whereIn('question_id', $questions->pluck('id'))
            ->pluck('contest_id', 'question_id');

        return view('contests.questions.index', [
            'questions' => $questions,
            'usedIds'   => $usedIds,
            'subjects'  => Subject::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('contests.questions.form', [
            'question' => new Question(['bank' => 'contest', 'difficulty' => 'medium']),
            'choices'  => collect(),
            'subjects' => Subject::orderBy('name')->get(),
            'chapters' => Chapter::orderBy('name')->get(),
            'types'    => Type::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $question = DB::transaction(function () use ($request, $data) {
            $question = Question::create([
                'question_text'       => $data['question_text'],
                'question_image_path' => $this->upload($request, 'question_image', 'questions/images'),
                'formula'             => $data['formula'] ?? null,
                'explanation'         => $data['explanation'] ?? '',
                'explanation_image_path' => $this->upload($request, 'explanation_image', 'explanations/images'),
                'subject_id'          => $data['subject_id'],
                'chapter_id'          => $data['chapter_id'] ?? null,
                'type_id'             => $data['type_id'],
                'difficulty'          => $data['difficulty'],
                // The two things that keep it out of the study bank until its
                // contest has been run.
                'bank'                => 'contest',
                'released_at'         => null,
            ]);

            $this->saveChoices($question, $data['choices'], (int) $data['correct_choice']);

            return $question;
        });

        return redirect()->route('contest-questions.index')
            ->with('success', 'Question added to the contest bank. Students cannot see it until a contest using it has ended.');
    }

    public function edit($contestQuestion)
    {
        // No model typehint here on purpose: route model binding resolves through
        // the global scope, which hides these questions, so binding would 404
        // before this method ever runs.
        $question = Question::withUnreleased()->with('choices')->findOrFail($contestQuestion);

        abort_unless($question->bank === 'contest' && $question->released_at === null, 404);

        return view('contests.questions.form', [
            'question' => $question,
            'choices'  => $question->choices,
            'subjects' => Subject::orderBy('name')->get(),
            'chapters' => Chapter::orderBy('name')->get(),
            'types'    => Type::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, $contestQuestion)
    {
        $question = Question::withUnreleased()->findOrFail($contestQuestion);

        abort_unless($question->bank === 'contest' && $question->released_at === null, 404);

        if (ContestQuestion::where('question_id', $question->id)
            ->whereHas('contest', fn ($q) => $q->whereHas('attempts'))->exists()) {
            return back()->with('error', 'This question is on a contest students have already sat, so it can no longer be edited.');
        }

        $data = $this->validated($request);

        DB::transaction(function () use ($request, $question, $data) {
            $question->update(array_filter([
                'question_text'       => $data['question_text'],
                'question_image_path' => $this->upload($request, 'question_image', 'questions/images')
                    ?? $question->question_image_path,
                'formula'             => $data['formula'] ?? null,
                'explanation'         => $data['explanation'] ?? '',
                'explanation_image_path' => $this->upload($request, 'explanation_image', 'explanations/images')
                    ?? $question->explanation_image_path,
                'subject_id'          => $data['subject_id'],
                'chapter_id'          => $data['chapter_id'] ?? null,
                'type_id'             => $data['type_id'],
                'difficulty'          => $data['difficulty'],
            ], fn ($v) => $v !== null));

            $question->update(['correct_choice_id' => null, 'answer_id' => null]);
            $question->choices()->delete();

            $this->saveChoices($question, $data['choices'], (int) $data['correct_choice']);
        });

        return redirect()->route('contest-questions.index')->with('success', 'Question updated.');
    }

    public function destroy($contestQuestion)
    {
        $question = Question::withUnreleased()->findOrFail($contestQuestion);

        abort_unless($question->bank === 'contest' && $question->released_at === null, 404);

        if (ContestQuestion::where('question_id', $question->id)->exists()) {
            return back()->with('error', 'This question is on a contest paper. Remove it from the paper first.');
        }

        $question->choices()->delete();
        $question->delete();

        return back()->with('success', 'Question deleted.');
    }

    /**
     * Choices are rewritten wholesale, then the correct one is pointed at.
     * Both correct_choice_id and answer_id are set, matching what the existing
     * question form writes.
     */
    private function saveChoices(Question $question, array $choices, int $correctIndex): void
    {
        foreach (array_values($choices) as $index => $choice) {
            $created = Choice::create([
                'question_id' => $question->id,
                'choice_text' => $choice['text'],
                'formula'     => $choice['formula'] ?? null,
            ]);

            if ($index === $correctIndex) {
                $question->update([
                    'correct_choice_id' => $created->id,
                    'answer_id'         => $created->id,
                ]);
            }
        }
    }

    private function upload(Request $request, string $field, string $path): ?string
    {
        return $request->hasFile($field)
            ? $request->file($field)->store($path, 'public')
            : null;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'question_text'      => 'required|string',
            'formula'            => 'nullable|string',
            'explanation'        => 'nullable|string',
            // subject_id and type_id are NOT NULL on the questions table.
            'subject_id'         => 'required|exists:subjects,id',
            'chapter_id'         => 'nullable|exists:chapters,id',
            'type_id'            => 'required|exists:types,id',
            'difficulty'         => 'required|in:easy,medium,hard',
            'question_image'     => 'nullable|image|max:5120',
            'explanation_image'  => 'nullable|image|max:5120',
            'choices'            => 'required|array|min:2|max:6',
            'choices.*.text'     => 'required|string',
            'choices.*.formula'  => 'nullable|string',
            'correct_choice'     => 'required|integer|min:0',
        ]);
    }
}
