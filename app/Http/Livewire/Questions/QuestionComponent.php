<?php

namespace App\Http\Livewire\Questions;

use App\Models\Question;
use Livewire\Component;
use Livewire\Attributes\On;

class QuestionComponent extends Component
{
    public $questions = [];


    public function deleteQuestion($questionId)
    {
        try {
            $question = Question::findOrFail($questionId);
            $question->delete();
            
            // Refresh the questions list
            $this->questions = Question::all();
            
            // Show success message
            session()->flash('message', 'Question deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting question: ' . $e->getMessage());
        }
    }

    #[On('refreshTable')]
    public function render()
    {
        // Retrieve questions with their related subject and year group.
        $this->questions = Question::with(['subject', 'yearGroup','type'])->latest()->get();
        // dd($this->questions);
        return view('livewire.questions.question-component');
    }
} 