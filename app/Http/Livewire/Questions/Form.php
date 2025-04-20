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
        // 'answerText' => 'required|integer',
        'explanation' => 'required|string',
        'explanationImage' => 'nullable|image|max:2048',
        'choices.*.text' => 'required|string',
        'choices.*.image' => 'nullable|image|max:2048',
        'choices.*.formula' => 'nullable|string',
        'duration'=>'nullable'
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

        // Upload question image
        $questionImagePath = $this->questionImage
            ? $this->questionImage->store('questions/images', 'public')
            : null;

        // Upload explanation image
        $explanationImagePath = $this->explanationImage
            ? $this->explanationImage->store('explanations/images', 'public')
            : null;
        
        // Save question (without answer_id first)
        $question = Question::create([
            'subject_id' => $this->subjectId,
            'year_group_id' => $this->yearGroupId,
            'chapter_id'=>$this->chapterId,
            'question_text' => $this->questionText,
            'question_image_path' => $questionImagePath,
            'formula' => $this->formula,
            'explanation' => $this->explanation,
            'explanation_image_path' => $explanationImagePath,
            'type_id'=>$this->type,
            'duration'=>$this->duration
        ]);

        // Save choices and collect their IDs
        $choicesIds = [];
        foreach ($this->choices as $index => $choiceData) {
            $choiceImagePath = $choiceData['image']
                ? $choiceData['image']->store('choices/images', 'public')
                : null;

            $choice = Choice::create([
                'question_id' => $question->id,
                'choice_text' => $choiceData['text'],
                'choice_image_path' => $choiceImagePath,
                'formula' => $choiceData['formula'],
            ]);
            
            $choicesIds[$index] = $choice->id;
        }

        // Update question with the correct answer_id
        // $question->update([
        //     'answer_id' => $choicesIds[$this->answerText] ?? "none"
        // ]);

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
            'formula',  'explanation', 'explanationImage', 'choices'
        ]);
        $this->choices = [
            ['text' => '', 'image' => null, 'formula' => '']
        ];
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
        $this->explanation = $question->explanation;
        $this->type = $question->type_id;
        $this->is_edit = true;
        $this->openModal = true;
        $this->duration = $question->duration;

        // Load choices and set the correct answer index
        $this->choices = [];
        $correctAnswerIndex = null;
        
        foreach ($question->choices as $index => $choice) {
            $this->choices[] = [
                'text' => $choice->choice_text,
                'image' => null,
                'formula' => $choice->formula,
            ];
            
            if ($choice->id == $question->answer_id) {
                $correctAnswerIndex = $index;
            }
        }
        
        // $this->answerText = $correctAnswerIndex;
    }

    public function render()
    {
        return view('livewire.questions.form', [
            'subjects' => Subject::all(),
            'types'=> Type::all(),
            'yearGroups' => YearGroup::all(),
            'chapters'=>Chapter::all()
        ]);
    }
}