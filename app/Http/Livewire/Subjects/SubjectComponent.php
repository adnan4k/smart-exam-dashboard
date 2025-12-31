<?php

namespace App\Http\Livewire\Subjects;

use App\Models\Subject;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class SubjectComponent extends Component
{
    public $subjects;
    public $showDeleteModal = false;
    public $subjectToDelete;

    #[On('refreshTable')]
    public function render()
    {
        $this->subjects = Subject::with('type')->get();
        // dd($this->subjects);
        return view('livewire.subjects.subject-component');
    }


    public function confirmDelete($subjectId)
    {
        $this->subjectToDelete = Subject::with('type')->findOrFail($subjectId);
        $this->showDeleteModal = true;
    }

    public function deleteSubject()
    {
        if ($this->subjectToDelete) {
            try {
                $subjectName = $this->subjectToDelete->name;
                $this->subjectToDelete->delete();
                
                $this->showDeleteModal = false;
                $this->subjectToDelete = null;
                
                // Refresh the subjects list
                $this->subjects = Subject::with('type')->get();
                
                Toaster::success("Subject '{$subjectName}' has been deleted successfully.");
            } catch (\Exception $e) {
                Toaster::error('Failed to delete subject. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->subjectToDelete = null;
    }
}
