<?php

namespace App\Http\Livewire\Notes;

use App\Models\Note;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Type;
use Livewire\Component;
use Livewire\Attributes\On;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Log;

class Form extends Component
{
    public $id;
    public $is_edit = false;
    public $openModal = false;
    public $isSubmitting = false;

    public $typeId;
    public $subjectId;
    public $chapterId;
    public $title;
    public $content;
    public $grade; // new optional grade field
    public $language = 'english'; // language field with default value

    public $chaptersForSubject = [];

    protected $listeners = ['noteModal' => 'noteModal'];

    public function noteModal()
    {
        $this->openModal = true;
    }

    protected $rules = [
        'typeId' => 'required|exists:types,id',
        'subjectId' => 'required|exists:subjects,id',
        'chapterId' => 'required|exists:chapters,id',
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'grade' => 'nullable|integer|min:0|max:12',
        'language' => 'required|in:amharic,afan_oromo,english,tigrinya,somali,afar,other',
    ];

    // ✅ Public method — safe to call via $wire.call()
    public function updateType($value)
    {
        if ($this->isSubmitting) return;

        $this->typeId = $value;
        $this->subjectId = null;
        $this->chapterId = null;
        $this->chaptersForSubject = [];
    }

    // ✅ Public method — safe to call via $wire.call()
    public function updateSubject($value)
    {
        if ($this->isSubmitting) return;

        $this->subjectId = $value;
        $this->chapterId = null;
        $this->chaptersForSubject = Chapter::whereIn('id', function ($q) use ($value) {
            $q->select('chapter_id')
              ->from('questions')
              ->where('subject_id', $value)
              ->whereNotNull('chapter_id');
        })->orderBy('name')->get();
    }

    #[On('edit-note')]
    public function edit($noteId)
    {
        $note = Note::findOrFail($noteId);
        $this->id = $note->id;
        $this->typeId = $note->type_id;
        $this->subjectId = $note->subject_id;
        $this->chapterId = $note->chapter_id;
        $this->title = $note->title;
        $this->content = $note->content;
        $this->grade = $note->grade; // load existing grade
        $this->language = $note->language ?? 'english'; // load existing language
        $this->is_edit = true;

        // Manually trigger subject update to load chapters
        $this->updateSubject($this->subjectId);

        $this->openModal = true;
    }

    public function saveNote()
    {
        $this->isSubmitting = true;
        
        // Capture current state — prevents mid-submit property changes from affecting validation
        $typeId = $this->typeId;
        $subjectId = $this->subjectId;
        $chapterId = $this->chapterId;
        $title = $this->title;
        $content = $this->content;
        $grade = $this->grade;
        $language = $this->language;

        try {
            // Validate using Livewire's validation which automatically adds errors to error bag
            $this->validate();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            
            // Collect all error messages
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = $message;
                }
            }
            
            // Show first error as notification, or show all errors
            if (count($errorMessages) > 0) {
                $firstError = $errorMessages[0];
                if (count($errorMessages) > 1) {
                    $firstError .= ' (and ' . (count($errorMessages) - 1) . ' more error' . (count($errorMessages) > 2 ? 's' : '') . ')';
                }
                Toaster::error($firstError);
            } else {
                Toaster::error('Please fill in all required fields correctly.');
            }
            
            $this->isSubmitting = false;
            return;
        }

        $data = [
            'type_id' => $typeId,
            'subject_id' => $subjectId,
            'chapter_id' => $chapterId,
            'title' => $title,
            'content' => $content,
            'grade' => $grade,
            'language' => $language,
        ];

        try {
            if ($this->is_edit) {
                $note = Note::findOrFail($this->id);
                $note->update($data);
                $message = 'Note Updated Successfully!';
            } else {
                Note::create($data);
                $message = 'Note Created Successfully!';
            }

            Toaster::success($message);

            // Close modal — Alpine will trigger resetAfterClose after this
            $this->openModal = false;
            $this->dispatch('refreshNotes');

        } catch (\Exception $e) {
            Toaster::error('Something went wrong: ' . $e->getMessage());
            Log::error('Note save error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function resetAfterClose()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'id',
            'is_edit',
            'typeId',
            'subjectId',
            'chapterId',
            'title',
            'content',
            'grade',
            'language',
            'isSubmitting'
        ]);
        $this->language = 'english'; // reset to default
        $this->chaptersForSubject = [];
    }

    public function render()
    {
        return view('livewire.notes.form', [
            'types' => Type::orderBy('name')->get(),
            'subjects' => Subject::when($this->typeId, function ($q) {
                $q->where('type_id', $this->typeId);
            })->orderBy('name')->get(),
            'allChapters' => Chapter::orderBy('name')->get(),
        ]);
    }
}