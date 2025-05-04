<?php

namespace App\Http\Livewire\Subjects;

use App\Models\Subject;
use Livewire\Attributes\On;
use Livewire\Component;

class SubjectComponent extends Component
{
    public $subjects;

    #[On('refreshTable')]
    public function render()
    {
        $this->subjects = Subject::with('type')->get();
        // dd($this->subjects);
        return view('livewire.subjects.subject-component');
    }


    public function deleteSubject($questionId)
    {
        try {
            $question = Subject::findOrFail($questionId);
            $question->delete();
            
            // Refresh the questions list
            $this->subjects = Subject::all();
            
            // Show success message
            session()->flash('message', 'Question deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting question: ' . $e->getMessage());
        }
    }
}
