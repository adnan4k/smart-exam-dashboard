<?php

namespace App\Http\Livewire\Notes;

use App\Models\Note;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class NoteComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

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

    public function deleteNote($noteId)
    {
        try {
            $note = Note::findOrFail($noteId);
            $note->delete();
            session()->flash('message', 'Note deleted successfully.');
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting note: ' . $e->getMessage());
        }
    }

    public function editNote($noteId)
    {
        $this->dispatch('edit-note', noteId: $noteId);
    }
} 