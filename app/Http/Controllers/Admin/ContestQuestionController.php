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
use Illuminate\Validation\ValidationException;

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
        return view('contests.questions.create', [
            'subjects' => Subject::orderBy('name')->get(),
            'chapters' => Chapter::orderBy('name')->get(),
            'types'    => Type::orderBy('name')->get(),
        ]);
    }

    /**
     * Bulk entry. The shared settings apply to every question in the batch, so
     * an author banks a whole paper in one visit instead of one round trip per
     * question.
     */
    public function store(Request $request)
    {
        $data = $this->bulkValidated($request);

        $uploadedPaths = [];

        try {
            $count = DB::transaction(function () use ($request, $data, &$uploadedPaths) {
                $created  = 0;
                $position = 1;

                foreach ($data['questions'] as $uid => $q) {
                    // Choice rows are fixed at A-D in the bulk form; unused rows
                    // arrive empty and are dropped here.
                    $keptIndexes = array_values(array_filter(
                        array_keys($q['choices']),
                        fn ($i) => trim((string) ($q['choices'][$i]['text'] ?? '')) !== ''
                    ));

                    if (count($keptIndexes) < 2) {
                        throw ValidationException::withMessages([
                            'questions' => "Question {$position} needs at least 2 answer choices.",
                        ]);
                    }

                    $correctIndex = array_search((int) $q['correct_choice'], $keptIndexes, true);

                    if ($correctIndex === false) {
                        throw ValidationException::withMessages([
                            'questions' => "Question {$position}: the marked correct answer is one of the blank choices.",
                        ]);
                    }

                    $questionImage    = $this->upload($request, "questions.{$uid}.question_image", 'questions/images');
                    $explanationImage = $this->upload($request, "questions.{$uid}.explanation_image", 'explanations/images');

                    if ($questionImage) {
                        $uploadedPaths[] = $questionImage;
                    }
                    if ($explanationImage) {
                        $uploadedPaths[] = $explanationImage;
                    }

                    $question = Question::create([
                        'question_text'          => $q['question_text'],
                        'question_image_path'    => $questionImage,
                        'formula'                => $q['formula'] ?? null,
                        'explanation'            => $q['explanation'] ?? '',
                        'explanation_image_path' => $explanationImage,
                        'subject_id'             => $data['subject_id'],
                        'chapter_id'             => $data['chapter_id'] ?? null,
                        'type_id'                => $data['type_id'],
                        'difficulty'             => $data['difficulty'],
                        // The two things that keep it out of the study bank until
                        // its contest has been run.
                        'bank'                   => 'contest',
                        'released_at'            => null,
                    ]);

                    $orderedChoices = array_map(fn ($i) => $q['choices'][$i], $keptIndexes);

                    $this->saveChoices($question, $orderedChoices, $correctIndex);

                    $created++;
                    $position++;
                }

                return $created;
            });
        } catch (\Throwable $e) {
            // Files land on disk before the transaction commits, so clean them
            // up if the batch fails anywhere in the middle.
            foreach ($uploadedPaths as $path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }

            throw $e;
        }

        return redirect()->route('contest-questions.index')
            ->with('success', "{$count} questions added to the contest bank. Students cannot see them until a contest using them has ended.");
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

    private function bulkValidated(Request $request): array
    {
        return $request->validate([
            'type_id'    => 'required|exists:types,id',
            'subject_id' => 'required|exists:subjects,id',
            'chapter_id' => 'nullable|exists:chapters,id',
            'difficulty' => 'required|in:easy,medium,hard',

            'questions'                     => 'required|array|max:50',
            'questions.*.question_text'     => 'required|string',
            'questions.*.formula'           => 'nullable|string',
            'questions.*.explanation'       => 'nullable|string',
            'questions.*.question_image'    => 'nullable|image|max:5120',
            'questions.*.explanation_image' => 'nullable|image|max:5120',
            'questions.*.choices'           => 'required|array|min:2|max:6',
            'questions.*.choices.*.text'    => 'nullable|string',
            'questions.*.choices.*.formula' => 'nullable|string',
            'questions.*.correct_choice'    => 'required|integer|min:0|max:3',
        ]);
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
