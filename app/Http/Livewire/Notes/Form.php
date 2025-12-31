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

    protected $listeners = [
        'noteModal' => 'noteModal'
    ];

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
    public function edit($noteId = null)
    {
        // Handle event parameter - can be array with 'noteId' key, object, or direct ID
        if (is_array($noteId) && isset($noteId['noteId'])) {
            $noteId = $noteId['noteId'];
        } elseif (is_object($noteId) && isset($noteId->noteId)) {
            $noteId = $noteId->noteId;
        } elseif ($noteId === null) {
            // If no data provided, try to get from request
            $noteId = request()->input('noteId');
            if (!$noteId) {
                session()->flash('error', 'Note ID is required.');
                return;
            }
        }
        
        $note = Note::with(['subject', 'chapter', 'type'])->findOrFail($noteId);
        
        // Set all form fields
        $this->id = $note->id;
        $this->typeId = $note->type_id;
        $this->subjectId = $note->subject_id;
        $this->chapterId = $note->chapter_id;
        $this->title = $note->title;
        $this->content = $note->content;
        $this->grade = $note->grade;
        $this->language = $note->language ?? 'english';
        $this->is_edit = true;

        // Load chapters for the subject
        if ($this->subjectId) {
            // Get chapters from notes table first (most relevant)
            $noteChapters = Chapter::whereIn('id', function ($q) {
                $q->select('chapter_id')
                  ->from('notes')
                  ->where('subject_id', $this->subjectId)
                  ->whereNotNull('chapter_id');
            })->get();
            
            // Also get chapters from questions
            $questionChapters = Chapter::whereIn('id', function ($q) {
                $q->select('chapter_id')
                  ->from('questions')
                  ->where('subject_id', $this->subjectId)
                  ->whereNotNull('chapter_id');
            })->get();
            
            // Merge and get unique chapters
            $this->chaptersForSubject = $noteChapters->merge($questionChapters)->unique('id')->sortBy('name')->values();
            
            // If still no chapters, get all chapters
            if ($this->chaptersForSubject->isEmpty()) {
                $this->chaptersForSubject = Chapter::orderBy('name')->get();
            }
        } else {
            // If no subject, get all chapters
            $this->chaptersForSubject = Chapter::orderBy('name')->get();
        }

        $this->openModal = true;
    }

    public function syncContent()
    {
        // This method can be called from JavaScript to ensure content is synced
        // The content should already be set via $wire.set('content', ...) from the form
        return ['status' => 'synced'];
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

        // Clean content - remove empty HTML tags and whitespace
        if ($content) {
            $cleanedContent = strip_tags($content);
            $cleanedContent = trim($cleanedContent);
            if (empty($cleanedContent)) {
                $this->addError('content', 'The content field is required.');
                Toaster::error('Please enter note content.');
                $this->isSubmitting = false;
                return;
            }
        }

        try {
            // Validate using Livewire's validation which automatically adds errors to error bag
            $this->validate();
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            
            // Collect all error messages and ensure they're in the error bag
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $errorMessages[] = $message;
                    // Ensure error is in Livewire's error bag for display
                    $this->addError($field, $message);
                }
            }
            
            // Show first error as notification
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
            $errorMessage = 'Something went wrong while saving the note.';
            if (config('app.debug')) {
                $errorMessage .= ' ' . $e->getMessage();
            }
            Toaster::error($errorMessage);
            Log::error('Note save error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $data
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