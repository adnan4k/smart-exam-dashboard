<?php

namespace App\Http\Livewire\Questions;

use App\Models\Question;
use Livewire\Component;
use Livewire\Attributes\On;

class QuestionComponent extends Component
{
    public $questions = [];

    #[On('refreshTable')]
    public function render()
    {
        // Retrieve questions with their related subject and year group.
        $this->questions = Question::with(['subject', 'yearGroup'])->latest()->get();
        return view('livewire.questions.question-component');
    }
} 