<?php

namespace App\Http\Livewire\Questions;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Type;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Masmerise\Toaster\Toaster;

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
    public $showDeleteModal = false;
    public $questionToDelete;
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

    public function confirmDelete($questionId)
    {
        $this->questionToDelete = Question::with(['subject', 'type'])->find($questionId);
        $this->showDeleteModal = true;
    }

    public function deleteQuestion()
    {
        if ($this->questionToDelete) {
            try {
                $questionText = strip_tags($this->questionToDelete->question_text);
                $questionText = \Str::limit($questionText, 50);
                
                // Delete related choices first
                $this->questionToDelete->choices()->delete();
                
                // Delete the question
                $this->questionToDelete->forceDelete();
                
                $this->showDeleteModal = false;
                $this->questionToDelete = null;
                
                Toaster::success("Question '{$questionText}' has been deleted successfully.");
            } catch (\Exception $e) {
                Toaster::error('Failed to delete question. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->questionToDelete = null;
    }

    #[On('refreshTable')]
    public function render()
    {
        return view('livewire.questions.question-component', [
            'questions' => $this->filterQuestions()
        ]);
    }
} 