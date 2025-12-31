<?php

namespace App\Http\Livewire\Notes;

use App\Models\Note;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Masmerise\Toaster\Toaster;

class NoteComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $showDeleteModal = false;
    public $noteToDelete;

    #[On('refreshNotes')]
    public function refreshNotes()
    {
        $this->resetPage();
    }

    public function render()
    {
        $notes = Note::with(['subject', 'chapter', 'type', 'user'])
            ->latest()
            ->paginate(10);
        
        return view('livewire.notes.note-component', [
            'notes' => $notes
        ]);
    }

    public function confirmDelete($noteId)
    {
        $this->noteToDelete = Note::with(['subject', 'chapter', 'type'])->findOrFail($noteId);
        $this->showDeleteModal = true;
    }

    public function deleteNote()
    {
        if ($this->noteToDelete) {
            try {
                $noteTitle = $this->noteToDelete->title;
                $this->noteToDelete->delete();
                
                $this->showDeleteModal = false;
                $this->noteToDelete = null;
                $this->resetPage();
                
                Toaster::success("Note '{$noteTitle}' has been deleted successfully.");
            } catch (\Exception $e) {
                Toaster::error('Failed to delete note. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->noteToDelete = null;
    }

    public function editNote($noteId)
    {
        $this->dispatch('edit-note', noteId: $noteId);
    }
} 