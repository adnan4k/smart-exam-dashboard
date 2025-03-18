<?php
namespace App\Http\Livewire\Questions;

use Livewire\Component;
use App\Models\Question;

class QuestionDetailComponent extends Component
{
    public $question;

    protected $listeners = ['view-question-detail' => 'loadQuestion'];

    public function loadQuestion($questionId)
    {
        $this->question = Question::find($questionId);
        // Logic to open the modal or display the details
    }

    public function render()
    {
        return view('questions.detail');
    }
} 