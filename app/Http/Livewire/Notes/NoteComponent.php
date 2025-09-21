<?php

namespace App\Http\Livewire\Notes;

use App\Models\Note;
use Livewire\Attributes\On;
use Livewire\Component;

class NoteComponent extends Component
{
    public $notes;

    #[On('refreshNotes')]
    public function render()
    {
        $this->notes = Note::with(['subject', 'chapter'])->latest()->get();
        return view('livewire.notes.note-component');
    }

    public function deleteNote($noteId)
    {
        try {
            $note = Note::findOrFail($noteId);
            $note->delete();
            $this->notes = Note::with(['subject', 'chapter'])->latest()->get();
            session()->flash('message', 'Note deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting note: ' . $e->getMessage());
        }
    }
} 