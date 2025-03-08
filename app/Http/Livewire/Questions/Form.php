<?php
namespace App\Http\Livewire\Questions;
namespace App\Http\Livewire\Questions;

use App\Models\Question;
use App\Models\Choice;
use App\Models\Subject;
use App\Models\Type;
use App\Models\YearGroup;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

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
    public $answerText;
    public $explanation;
    public $explanationImage;
    public $type;
    public $choices = [
        ['text' => '', 'image' => null, 'formula' => '']
    ];

    public $is_edit = false;
    public $id;
    public $openModal = false;
    protected $listeners = ['questionModal'=>'questionModal'];
    public function questionModal(){
        $this->openModal = true;
     }

    protected $rules = [
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
        // dd($this->all());

        $this->validate();

        // Upload question image
        $questionImagePath = $this->questionImage
            ? $this->questionImage->store('questions/images', 'public')
            : null;

        // Upload explanation image
        $explanationImagePath = $this->explanationImage
            ? $this->explanationImage->store('explanations/images', 'public')
            : null;
        // Save question
        $question = Question::create([
            'subject_id' => $this->subjectId,
            'year_group_id' => $this->yearGroupId,
            'question_text' => $this->questionText,
            'question_image_path' => $questionImagePath,
            'formula' => $this->formula,
            'answer_text' => $this->answerText,
            'explanation' => $this->explanation,
            'explanation_image_path' => $explanationImagePath,
            'type_id'=>$this->type,
        ]);

        // Save choices
        foreach ($this->choices as $choiceData) {
            $choiceImagePath = $choiceData['image']
                ? $choiceData['image']->store('choices/images', 'public')
                : null;

            Choice::create([
                'question_id' => $question->id,
                'choice_text' => $choiceData['text'],
                'choice_image_path' => $choiceImagePath,
                'formula' => $choiceData['formula'],
            ]);
        }

        $message = $this->is_edit ? "Question Updated Successfully!" : "Question Created Successfully!";
        Toaster::success($message);
        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    public function resetForm()
    {
        $this->reset([
            'subjectId', 'yearGroupId', 'questionText', 'questionImage',
            'formula', 'answerText', 'explanation', 'explanationImage', 'choices'
        ]);
        $this->is_edit = false;
    }

    #[On('edit-question')]
    public function edit(Question $question)
    {
        $this->id = $question->id;
        $this->subjectId = $question->subject_id;
        $this->yearGroupId = $question->year_group_id;
        $this->questionText = $question->question_text;
        $this->formula = $question->formula;
        $this->answerText = $question->answer_text;
        $this->explanation = $question->explanation;
        $this->type  = $question->type;
        $this->is_edit = true;
        $this->openModal = true;

        // Load choices
        $this->choices = $question->choices->map(function ($choice) {
            return [
                'text' => $choice->choice_text,
                'image' => null, // Reset image on edit
                'formula' => $choice->formula,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.questions.form', [
            'subjects' => Subject::all(),
             'types'=> Type::all(),

            'yearGroups' => YearGroup::all(),
        ]);
    }
}