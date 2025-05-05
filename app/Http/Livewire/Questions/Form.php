<?php

namespace App\Http\Livewire\Questions;

use App\Models\Chapter;
use App\Models\Question;
use App\Models\Choice;
use App\Models\Subject;
use App\Models\Type;
use App\Models\YearGroup;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\DB;

class Form extends Component
{
    use WithFileUploads;

    public $title;
    public $description;
    public $subjectId;
    public $yearGroupId;
    public $questionText;
    public $questionImage;
    public $formula;
    // public $answerText  = 'none';
    public $explanation;
    public $explanationImage;
    public $chapterId;
    public $type;
    public $duration;
    public $filteredChapters = [];
    public $choices = [
        ['text' => '', 'image' => null, 'formula' => '']
    ];

    public $is_edit = false;
    public $id;
    public $openModal = false;
    protected $listeners = ['questionModal' => 'questionModal'];

    public $questionId;
    public $scienceType;
    public $region;
    public $correctChoiceId;

    public $filteredSubjects = [];
    public $subjects = [];
    public function questionModal()
    {
        $this->openModal = true;
    }
    public function loadSubjects()
    {
        $this->reset('subjectId', 'chapterId', 'chapters');
        
        if ($this->type) {
            $this->subjects = Subject::where('type_id', $this->type)->get();
        } else {
            $this->subjects = Subject::all();
        }
    }
    public function loadChapters()
{
    $this->reset('chapterId');
    
    if ($this->subjectId) {
        $this->chapters = Chapter::all();
        
        // Auto-set duration if needed
        $subject = Subject::find($this->subjectId);
        $this->duration = $subject->default_duration ?? null;
    } else {
        $this->chapters = [];
    }
}
    protected $rules = [
        'subjectId' => 'required|exists:subjects,id',
        // 'yearGroupId' => 'required|exists:year_groups,id',
        'chapterId' => 'nullable|exists:chapters,id',
        'type' => 'required|exists:types,id', // Added validation for type
        'questionText' => 'required|string',
        'scienceType' => 'nullable',
        'region' => 'required_if:type,regional',
        'questionImage' => 'nullable|image|max:2048',
        'formula' => 'nullable|string',
        'explanation' => 'required|string',
        'explanationImage' => 'nullable|image|max:2048',
        'choices.*.text' => 'required_without:choices.*.image|string|nullable', // Modified to allow empty text if image exists
        'choices.*.image' => 'nullable|image|max:2048',
        'choices.*.formula' => 'nullable|string',
        'duration' => 'nullable|integer|min:1',
        'correctChoiceId' => 'required|integer|min:0' // Added validation for correct choice
    ];

    public function addChoice()
    {
        $this->choices[] = ['text' => '', 'image' => null, 'formula' => ''];
    }

    public function removeChoice($index)
    {
        unset($this->choices[$index]);
        $this->choices = array_values($this->choices);
    }

    public function saveQuestion()
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $questionData = [
                'subject_id' => $this->subjectId,
                'year_group_id' => $this->yearGroupId,
                'chapter_id' => $this->chapterId,
                'question_text' => $this->questionText,
                'formula' => $this->formula,
                'explanation' => $this->explanation,
                'type_id' => $this->type,
                'duration' => $this->duration,
                'science_type' => $this->scienceType ?? 'natural',
                'region' => $this->region,
            ];

            // Handle question image
            if ($this->questionImage instanceof \Illuminate\Http\UploadedFile) {
                $questionData['question_image_path'] = $this->questionImage->store('questions/images', 'public');
            } elseif ($this->is_edit && !$this->questionImage) {
                // Keep existing image if not changed during edit
                $existing = Question::find($this->questionId);
                $questionData['question_image_path'] = $existing->question_image_path;
            }

            // Handle explanation image
            if ($this->explanationImage instanceof \Illuminate\Http\UploadedFile) {
                $questionData['explanation_image_path'] = $this->explanationImage->store('explanations/images', 'public');
            } elseif ($this->is_edit && !$this->explanationImage) {
                $existing = Question::find($this->questionId);
                $questionData['explanation_image_path'] = $existing->explanation_image_path;
            }

            if ($this->is_edit) {
                $question = Question::findOrFail($this->questionId);
                $question->update($questionData);

                // Delete existing choices
                $question->choices()->delete();
            } else {
                $question = Question::create($questionData);
            }

            // Save choices
            foreach ($this->choices as $index => $choiceData) {
                $choiceImagePath = null;

                if (isset($choiceData['image']) && $choiceData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $choiceImagePath = $choiceData['image']->store('choices/images', 'public');
                }

                $choice = Choice::create([
                    'question_id' => $question->id,
                    'choice_text' => $choiceData['text'] ?? null,
                    'choice_image_path' => $choiceImagePath,
                    'formula' => $choiceData['formula'] ?? null,
                ]);

                // Set the correct choice
                if ($index == $this->correctChoiceId) {
                    $question->update(['correct_choice_id' => $choice->id]);
                }
            }

            DB::commit();

            $message = $this->is_edit ? "Question Updated Successfully!" : "Question Created Successfully!";
            Toaster::success($message);
            $this->openModal = false;
            $this->resetForm();
            $this->dispatch('refreshTable');
        } catch (\Exception $e) {
            DB::rollBack();
            Toaster::error('Error saving question: ' . $e->getMessage());
            throw $e; // Optional: re-throw the exception for debugging
        }
    }

    public function resetForm()
    {
        $this->reset([
            'questionId',
            'subjectId',
            'yearGroupId',
            'questionText',
            'questionImage',
            'formula',
            'correctChoiceId',
            'explanation',
            'explanationImage',
            'choices',
            'type',
            'duration',
            'chapterId',
            'scienceType',
            'region'
        ]);
        $this->is_edit = false;
    }

    #[On('edit-question')]
    public function edit($questionId)
    {
        $question = Question::with('choices')->findOrFail($questionId);

        $this->questionId = $question->id;
        $this->subjectId = $question->subject_id;
        $this->yearGroupId = $question->year_group_id;
        $this->chapterId = $question->chapter_id;
        $this->questionText = $question->question_text;
        $this->formula = $question->formula;
        $this->explanation = $question->explanation;
        $this->type = $question->type_id;
        $this->duration = $question->duration;
        $this->scienceType = $question->science_type;
        $this->region = $question->region;

        // Load choices
        $this->choices = $question->choices->map(function ($choice) use ($question) {
            return [
                'text' => $choice->choice_text,
                'formula' => $choice->formula,
                'image' => null // Reset image on edit
            ];
        })->toArray();

        // Set correct choice
        if ($question->correct_choice_id) {
            $correctChoiceIndex = collect($question->choices)->search(function ($choice) use ($question) {
                return $choice->id === $question->correct_choice_id;
            });
            $this->correctChoiceId = $correctChoiceIndex !== false ? $correctChoiceIndex : null;
        }

        $this->is_edit = true;
        $this->openModal = true;
    }

    public function updatedType($value)
    {
        // Filter subjects by selected type
        $this->filteredSubjects = \App\Models\Subject::where('type_id', $value)->get();
        $this->subjectId = null; // Reset subject selection
        $this->filteredChapters = []; // Reset chapters
        $this->chapterId = null; // Reset chapter selection
    }
    public $chapters = [];

    public function updatedSubjectId($value)
    {
        logger()->info("Subject ID updated to: " . $value); // Check storage/logs/laravel.log
        
        if (empty($value)) {
            $this->filteredChapters = [];
            $this->chapterId = null;
            $this->duration = null;
            return;
        }
        
        $subject = Subject::find($value);
        if (!$subject) {
            logger()->error("Subject not found with ID: " . $value);
            return;
        }
        
        $this->duration = $subject->default_duration;
        // $this->filteredChapters = Chapter::where('subject_id', $value)->get();
        $this->chapterId = null;
        
    }
    public function render()
    {
        return view('livewire.questions.form', [
            'types' => Type::all(),
            'subjects' => $this->filteredSubjects ?: Subject::all(),
            'yearGroups' => YearGroup::all(),
            'chapters' =>   Chapter::all(),
        ]);
    }
}
