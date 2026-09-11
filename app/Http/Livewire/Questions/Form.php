<?php

namespace App\Http\Livewire\Questions;

use App\Models\Chapter;
use App\Models\Choice;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Type;
use App\Models\YearGroup;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

/**
 * Batch authoring for the study question bank.
 *
 * The settings that rarely change from one question to the next - exam type,
 * subject, chapter, duration, region - are picked once at the top and applied
 * to every question in the batch. Below them the author stacks as many
 * questions as they need and saves the lot in one transaction, instead of
 * reopening the modal for every single question.
 *
 * Only the question being worked on is expanded; the others collapse to a
 * summary row that says at a glance what is still missing. That is also what
 * keeps a single rich-text editor on the page however big the batch gets.
 *
 * Editing an existing question reuses the same screen with a batch of one.
 */
class Form extends Component
{
    use WithFileUploads;

    public const MAX_QUESTIONS  = 50;
    public const MIN_CHOICES    = 2;
    public const MAX_CHOICES    = 6;
    public const STARTING_CHOICES = 4;

    /** Shared settings: chosen once, written onto every question in the batch. */
    public $type;
    public $subjectId;
    public $chapterId;
    public $yearGroupId;
    public $duration;
    public $scienceType = 'natural';
    public $region;

    /** The batch. One entry per question; see blankDraft() for the shape. */
    public $drafts = [];

    /** Which draft is expanded. Everything else is a collapsed summary row. */
    public $activeIndex = 0;

    /** The shared settings panel folds away once it has been filled in. */
    public $settingsOpen = true;

    /** Feeds the wire:key of each draft so rows keep their identity. */
    public $seq = 0;

    public $openModal = false;
    public $is_edit = false;
    public $questionId;

    protected $listeners = ['questionModal' => 'questionModal'];

    protected $validationAttributes = [
        'type'                     => 'exam type',
        'subjectId'                => 'subject',
        'chapterId'                => 'chapter',
        'drafts.*.text'            => 'question prompt',
        'drafts.*.explanation'     => 'explanation',
        'drafts.*.image'           => 'question image',
        'drafts.*.explanationImage' => 'explanation image',
        'drafts.*.choices.*.text'  => 'choice text',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    /* ---------------------------------------------------------------------
     | Opening and closing
     |--------------------------------------------------------------------*/

    public function questionModal()
    {
        $this->resetForm();
        $this->openModal = true;
    }

    #[On('edit-question')]
    public function edit($questionId)
    {
        $this->resetForm();

        $question = Question::with('choices')->findOrFail($questionId);

        $this->questionId  = $question->id;
        $this->type        = $question->type_id;
        $this->subjectId   = $question->subject_id;
        $this->chapterId   = $question->chapter_id;
        $this->yearGroupId = $question->year_group_id;
        $this->duration    = $question->duration;
        $this->scienceType = $question->science_type ?: 'natural';
        $this->region      = $question->region;

        $choices = $question->choices->values();

        $draft = $this->blankDraft();
        $draft['text']             = $question->question_text;
        $draft['formula']          = $question->formula;
        $draft['imagePath']        = $question->question_image_path;
        $draft['explanation']      = $question->explanation;
        $draft['explanationPath']  = $question->explanation_image_path;
        $draft['choices']          = $choices->map(fn ($choice) => [
            'text'      => $choice->choice_text,
            'formula'   => $choice->formula,
            'imagePath' => $choice->choice_image_path,
        ])->all();

        // A question saved with too few choices (or none at all) still has to be
        // editable, so pad the rows back out to something workable.
        while (count($draft['choices']) < self::MIN_CHOICES) {
            $draft['choices'][] = ['text' => '', 'formula' => '', 'imagePath' => null];
        }

        $correctId = $question->correct_choice_id ?: $question->answer_id;
        $position  = $choices->search(fn ($choice) => (int) $choice->id === (int) $correctId);
        $draft['correct'] = $position === false ? null : $position;

        $this->drafts       = [$draft];
        $this->activeIndex  = 0;
        $this->settingsOpen = false;
        $this->is_edit      = true;
        $this->openModal    = true;
    }

    public function resetForm()
    {
        $this->reset([
            'type', 'subjectId', 'chapterId', 'yearGroupId', 'duration',
            'scienceType', 'region', 'questionId', 'is_edit',
        ]);

        $this->resetErrorBag();

        $this->seq          = 0;
        $this->drafts       = [$this->blankDraft()];
        $this->activeIndex  = 0;
        $this->settingsOpen = true;
    }

    /* ---------------------------------------------------------------------
     | Shared settings
     |--------------------------------------------------------------------*/

    public function toggleSettings()
    {
        $this->settingsOpen = ! $this->settingsOpen;
    }

    public function updatedType()
    {
        // Subjects carry their own exam type, so a type change invalidates the
        // current subject (and therefore the chapter picked under it).
        $this->reset('subjectId', 'chapterId', 'duration');
    }

    public function updatedSubjectId($value)
    {
        $this->chapterId = null;

        $this->duration = $value
            ? optional(Subject::find($value))->default_duration
            : null;
    }

    /* ---------------------------------------------------------------------
     | The batch
     |--------------------------------------------------------------------*/

    public function addQuestion()
    {
        if ($this->is_edit || count($this->drafts) >= self::MAX_QUESTIONS) {
            return;
        }

        $this->drafts[]     = $this->blankDraft();
        $this->activeIndex  = count($this->drafts) - 1;
        $this->settingsOpen = false;
        $this->resetErrorBag();
    }

    /**
     * Most batches are variations on a theme - same choice count, same wording
     * pattern - so copying the question you just wrote beats retyping it.
     */
    public function duplicateQuestion($index)
    {
        if ($this->is_edit || ! isset($this->drafts[$index]) || count($this->drafts) >= self::MAX_QUESTIONS) {
            return;
        }

        $copy = $this->drafts[$index];
        $copy['key']             = 'q' . (++$this->seq);
        $copy['image']           = null;
        $copy['imagePath']       = null;
        $copy['explanationImage'] = null;
        $copy['explanationPath'] = null;

        array_splice($this->drafts, $index + 1, 0, [$copy]);

        $this->activeIndex = $index + 1;
        $this->resetErrorBag();
    }

    public function removeQuestion($index)
    {
        if ($this->is_edit || count($this->drafts) <= 1 || ! isset($this->drafts[$index])) {
            return;
        }

        unset($this->drafts[$index]);
        $this->drafts = array_values($this->drafts);

        $this->activeIndex = max(0, min((int) $this->activeIndex, count($this->drafts) - 1));
        $this->resetErrorBag();
    }

    public function openQuestion($index)
    {
        if (! isset($this->drafts[$index])) {
            return;
        }

        // Clicking the open question again collapses it, so the whole batch can
        // be reviewed as a list.
        $this->activeIndex = (int) $this->activeIndex === (int) $index ? -1 : (int) $index;
    }

    /* ---------------------------------------------------------------------
     | Choices
     |--------------------------------------------------------------------*/

    public function addChoice($index)
    {
        if (! isset($this->drafts[$index]) || count($this->drafts[$index]['choices']) >= self::MAX_CHOICES) {
            return;
        }

        $this->drafts[$index]['choices'][] = ['text' => '', 'formula' => '', 'imagePath' => null];
    }

    public function removeChoice($index, $choiceIndex)
    {
        if (! isset($this->drafts[$index]['choices'][$choiceIndex])) {
            return;
        }

        if (count($this->drafts[$index]['choices']) <= self::MIN_CHOICES) {
            return;
        }

        unset($this->drafts[$index]['choices'][$choiceIndex]);
        $this->drafts[$index]['choices'] = array_values($this->drafts[$index]['choices']);

        // Keep the correct-answer marker pointing at the same choice.
        $correct = $this->drafts[$index]['correct'];

        if ($correct === null || $correct === '') {
            return;
        }

        if ((int) $correct === (int) $choiceIndex) {
            $this->drafts[$index]['correct'] = null;
        } elseif ((int) $correct > (int) $choiceIndex) {
            $this->drafts[$index]['correct'] = (int) $correct - 1;
        }
    }

    public function markCorrect($index, $choiceIndex)
    {
        if (! isset($this->drafts[$index]['choices'][$choiceIndex])) {
            return;
        }

        $this->drafts[$index]['correct'] = (int) $choiceIndex;
    }

    public function clearImage($index, $field)
    {
        if (! isset($this->drafts[$index]) || ! in_array($field, ['image', 'explanationImage', 'imagePath', 'explanationPath'], true)) {
            return;
        }

        $this->drafts[$index][$field] = null;
    }

    /* ---------------------------------------------------------------------
     | Saving
     |--------------------------------------------------------------------*/

    public function saveQuestion()
    {
        $this->normaliseDrafts();
        $this->validateBatch();

        $storedPaths = [];

        try {
            $saved = DB::transaction(function () use (&$storedPaths) {
                $saved = 0;

                foreach ($this->drafts as $draft) {
                    $this->persist($draft, $storedPaths);
                    $saved++;
                }

                return $saved;
            });
        } catch (\Throwable $e) {
            // Uploads land on disk before the transaction commits, so a failure
            // half way through the batch must not leave orphans behind.
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            Toaster::error('Error saving questions: ' . $e->getMessage());

            throw $e;
        }

        Toaster::success($this->is_edit
            ? 'Question updated successfully!'
            : ($saved === 1 ? 'Question added to the bank.' : "{$saved} questions added to the bank."));

        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    private function persist(array $draft, array &$storedPaths): void
    {
        $questionImage    = $this->storeUpload($draft['image'] ?? null, 'questions/images', $storedPaths)
            ?? ($draft['imagePath'] ?? null);
        $explanationImage = $this->storeUpload($draft['explanationImage'] ?? null, 'explanations/images', $storedPaths)
            ?? ($draft['explanationPath'] ?? null);

        $attributes = [
            'subject_id'             => $this->subjectId,
            'year_group_id'          => $this->yearGroupId,
            'chapter_id'             => $this->chapterId ?: null,
            'type_id'                => $this->type,
            'duration'               => $this->duration ?: null,
            'science_type'           => $this->scienceType ?: 'natural',
            'region'                 => $this->region ?: null,
            'question_text'          => trim((string) $draft['text']),
            'formula'                => $draft['formula'] ?: null,
            'question_image_path'    => $questionImage,
            'explanation'            => $draft['explanation'],
            'explanation_image_path' => $explanationImage,
        ];

        if ($this->is_edit) {
            $question = Question::findOrFail($this->questionId);
            $question->update($attributes + ['correct_choice_id' => null, 'answer_id' => null]);
            $question->choices()->delete();
        } else {
            // Study questions are visible the moment they are written; only
            // contest questions stay hidden, and those are authored elsewhere.
            $question = Question::create($attributes + [
                'bank'        => 'study',
                'released_at' => now(),
            ]);
        }

        $keptIndexes = $this->filledChoiceIndexes($draft);
        $correctSlot = array_search((int) $draft['correct'], $keptIndexes, true);

        foreach ($keptIndexes as $slot => $choiceIndex) {
            $choice = Choice::create([
                'question_id'       => $question->id,
                'choice_text'       => trim((string) $draft['choices'][$choiceIndex]['text']),
                'formula'           => $draft['choices'][$choiceIndex]['formula'] ?: null,
                'choice_image_path' => $draft['choices'][$choiceIndex]['imagePath'] ?? null,
            ]);

            if ($slot === $correctSlot) {
                $question->update([
                    'correct_choice_id' => $choice->id,
                    'answer_id'         => $choice->id,
                ]);
            }
        }
    }

    private function storeUpload($file, string $path, array &$storedPaths): ?string
    {
        if (! $file instanceof UploadedFile) {
            return null;
        }

        $stored = $file->store($path, 'public');
        $storedPaths[] = $stored;

        return $stored;
    }

    /* ---------------------------------------------------------------------
     | Validation
     |--------------------------------------------------------------------*/

    protected function rules()
    {
        return [
            'type'                      => 'required|exists:types,id',
            'subjectId'                 => 'required|exists:subjects,id',
            'chapterId'                 => 'nullable|exists:chapters,id',
            'duration'                  => 'nullable|integer|min:1',
            'scienceType'               => 'nullable|in:natural,social',
            'region'                    => 'nullable|string',

            'drafts'                    => 'required|array|min:1|max:' . self::MAX_QUESTIONS,
            'drafts.*.text'             => 'required|string',
            'drafts.*.formula'          => 'nullable|string',
            'drafts.*.explanation'      => 'required|string',
            'drafts.*.image'            => 'nullable|image|max:2048',
            'drafts.*.explanationImage' => 'nullable|image|max:2048',
            'drafts.*.choices'          => 'required|array|min:' . self::MIN_CHOICES . '|max:' . self::MAX_CHOICES,
            'drafts.*.choices.*.text'   => 'nullable|string',
            'drafts.*.choices.*.formula' => 'nullable|string',
        ];
    }

    protected function messages()
    {
        return [
            'drafts.*.text.required'        => 'Write the question prompt.',
            'drafts.*.explanation.required' => 'Write the explanation students see after answering.',
            'type.required'                 => 'Choose the exam type these questions belong to.',
            'subjectId.required'            => 'Choose the subject these questions belong to.',
        ];
    }

    /**
     * An "empty" Quill editor still posts markup, and trailing whitespace in a
     * prompt would defeat the required rules. Clean both up before validating
     * so the form never rejects something the author cannot see.
     */
    private function normaliseDrafts(): void
    {
        foreach ($this->drafts as $i => $draft) {
            $this->drafts[$i]['text'] = trim((string) ($draft['text'] ?? ''));

            if ($this->isBlankHtml($draft['explanation'] ?? '')) {
                $this->drafts[$i]['explanation'] = '';
            }
        }
    }

    private function validateBatch(): void
    {
        $validator = Validator::make(
            [
                'type'        => $this->type,
                'subjectId'   => $this->subjectId,
                'chapterId'   => $this->chapterId,
                'duration'    => $this->duration,
                'scienceType' => $this->scienceType,
                'region'      => $this->region,
                'drafts'      => $this->drafts,
            ],
            $this->rules(),
            $this->messages(),
            $this->validationAttributes
        );

        // Blank choice rows are allowed and simply dropped, so "at least two
        // options, and the correct one is not blank" has to be checked by hand.
        $validator->after(function ($validator) {
            foreach ($this->drafts as $i => $draft) {
                $filled = $this->filledChoiceIndexes($draft);

                if (count($filled) < self::MIN_CHOICES) {
                    $validator->errors()->add(
                        "drafts.{$i}.choices",
                        'Give this question at least ' . self::MIN_CHOICES . ' answer choices.'
                    );
                }

                $correct = $draft['correct'] ?? null;

                if ($correct === null || $correct === '') {
                    $validator->errors()->add("drafts.{$i}.correct", 'Mark one choice as the correct answer.');
                } elseif ($filled && ! in_array((int) $correct, $filled, true)) {
                    $validator->errors()->add("drafts.{$i}.correct", 'The choice marked correct is empty - mark one that has text.');
                }
            }
        });

        if ($validator->fails()) {
            $this->revealFirstProblem($validator->errors()->keys());

            throw new ValidationException($validator);
        }
    }

    /**
     * Errors are useless on a question that is folded shut, so open the first
     * one that has a problem (or the settings panel, if that is where it is).
     */
    private function revealFirstProblem(array $keys): void
    {
        foreach ($keys as $key) {
            if (preg_match('/^drafts\.(\d+)\./', $key, $matches)) {
                $this->activeIndex = (int) $matches[1];

                return;
            }
        }

        $this->settingsOpen = true;
        $this->activeIndex  = 0;
    }

    /* ---------------------------------------------------------------------
     | Helpers shared with the view
     |--------------------------------------------------------------------*/

    private function blankDraft(): array
    {
        return [
            'key'              => 'q' . (++$this->seq),
            'text'             => '',
            'formula'          => '',
            'image'            => null,
            'imagePath'        => null,
            'explanation'      => '',
            'explanationImage' => null,
            'explanationPath'  => null,
            'correct'          => null,
            'choices'          => array_fill(0, self::STARTING_CHOICES, [
                'text' => '', 'formula' => '', 'imagePath' => null,
            ]),
        ];
    }

    private function filledChoiceIndexes(array $draft): array
    {
        $choices = $draft['choices'] ?? [];

        return array_values(array_filter(
            array_keys($choices),
            fn ($i) => trim((string) ($choices[$i]['text'] ?? '')) !== ''
        ));
    }

    private function isBlankHtml($html): bool
    {
        if (! is_string($html)) {
            return true;
        }

        if (preg_match('/<(img|iframe|table)\b/i', $html)) {
            return false;
        }

        return trim(str_replace("\xc2\xa0", ' ', strip_tags($html))) === '';
    }

    /**
     * What each collapsed row reports: ready to save, or exactly what is short.
     */
    private function draftStatus(array $draft): array
    {
        $missing = [];
        $filled  = $this->filledChoiceIndexes($draft);
        $correct = $draft['correct'] ?? null;

        if (trim((string) ($draft['text'] ?? '')) === '') {
            $missing[] = 'prompt';
        }

        if (count($filled) < self::MIN_CHOICES) {
            $missing[] = 'choices';
        }

        if ($correct === null || $correct === '' || ($filled && ! in_array((int) $correct, $filled, true))) {
            $missing[] = 'correct answer';
        }

        if ($this->isBlankHtml($draft['explanation'] ?? '')) {
            $missing[] = 'explanation';
        }

        return [
            'ready'   => $missing === [],
            'missing' => $missing,
            'label'   => $missing === [] ? 'Ready' : 'Needs ' . implode(', ', $missing),
            'filled'  => count($filled),
        ];
    }

    public function render()
    {
        $statuses = [];

        foreach ($this->drafts as $i => $draft) {
            $statuses[$i] = $this->draftStatus($draft);
        }

        return view('livewire.questions.form', [
            'types'        => Type::orderBy('name')->get(),
            'subjects'     => Subject::orderBy('name')->get(),
            'yearGroups'   => YearGroup::all(),
            'chapters'     => Chapter::orderBy('name')->get(),
            'statuses'     => $statuses,
            'readyCount'   => count(array_filter($statuses, fn ($s) => $s['ready'])),
            'maxQuestions' => self::MAX_QUESTIONS,
            'maxChoices'   => self::MAX_CHOICES,
            'minChoices'   => self::MIN_CHOICES,
        ]);
    }
}
