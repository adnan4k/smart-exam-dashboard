<?php

namespace App\Http\Livewire\Chapter;

use App\Models\Chapter;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class ChapterComponent extends Component
{
    public $showDeleteModal = false;
    public $chapterToDelete;

    public function confirmDelete($id)
    {
        $this->chapterToDelete = Chapter::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->chapterToDelete) {
            try {
                $chapterName = $this->chapterToDelete->name;
                $this->chapterToDelete->delete();
                
                $this->showDeleteModal = false;
                $this->chapterToDelete = null;
                
                Toaster::success("Chapter '{$chapterName}' has been deleted successfully.");
                $this->dispatch('refreshTable');
            } catch (\Exception $e) {
                Toaster::error('Failed to delete chapter. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->chapterToDelete = null;
    }

    #[On('refreshTable')]
    public function render()
    {
        $chapters = Chapter::all();
        return view('livewire.chapter.chapter-component',compact('chapters'));
    }

    
}
