<?php

namespace App\Http\Livewire\Questions;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Type;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class QuestionComponent extends Component
{
    use WithPagination;

    public $selectedSubject;
    public $selectedYear;
    public $selectedType;
    public $searchTerm = '';
    public $subjects;
    public $years;
    public $types;
    protected $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->subjects = Subject::distinct()->get(['name']);
        $this->years = Subject::distinct()->get(['year']);
        $this->types = Type::all();
    }

    public function filterQuestions()
    {
        $query = Question::with(['subject', 'yearGroup', 'type']);

        if ($this->searchTerm) {
            $query->where('question_text', 'like', '%' . $this->searchTerm . '%');
        }

        if ($this->selectedSubject) {
            $query->whereHas('subject', function($q) {
                $q->where('name', $this->selectedSubject);
            });
        }

        if ($this->selectedYear) {
            $query->whereHas('subject', function($q) {
                $q->where('year', $this->selectedYear);
            });
        }

        if ($this->selectedType) {
            $query->where('type_id', $this->selectedType);
        }

        return $query->latest()->paginate(10);
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedSelectedSubject()
    {
        $this->resetPage();
    }

    public function updatedSelectedYear()
    {
        $this->resetPage();
    }

    public function updatedSelectedType()
    {
        $this->resetPage();
    }

    public function deleteQuestion($questionId)
    {
        try {
            $question = Question::findOrFail($questionId);
            $question->choices()->delete();

            $question->forceDelete();
    
            session()->flash('message', 'Question deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting question: ' . $e->getMessage());
        }
    }
    

    #[On('refreshTable')]
    public function render()
    {
        return view('livewire.questions.question-component', [
            'questions' => $this->filterQuestions()
        ]);
    }
} 