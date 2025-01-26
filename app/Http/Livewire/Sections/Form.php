<?php

namespace App\Http\Livewire\Sections;
use App\Models\Section;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
    use WithFileUploads;
    public $title;
    public $type;
    public $content;
    public $code;
    public $image;
    public $is_edit;
    protected $listeners = ['sectionModal' => 'sectionModal'];
    public $openModal = false;
    public $id;

    public function sectionModal()
    {
        $this->openModal = true;
    }
    protected $rules = [
        'title' => 'required',
        'content' => 'required',
        'image' => 'required',
    ];
    public function save()
    {
        // dd($this->all());
       $validated =  $this->validate();
        $sections = $this->is_edit ? Section::find($this->id) : new Section();

        $sections->title = $this->title;
        $sections->type = $this->type;
        $sections->code = $this->code;

        $sections->content = $this->content;
      
        if ($this->image instanceof UploadedFile) {
            if ($this->is_edit && $sections->image) {
                // Delete the old image if editing
                Storage::delete($sections->image);
            }
        //  dd($this->image);
            // Store the new image
            $path = $this->image->store('section', 'public');
            $sections->image = $path;
        } elseif ($this->is_edit) {
            $sections->image = $this->image;
        }

        $sections->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";

        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-section')]
    public function edit(Section $section)
    {
        $this->openModal = true;

        $this->title  = $section->title;
        $this->type = $section->type;
        $this->content = $section->content;
        $this->is_edit = true;
        $this->code = $section->code;
        $this->image = $section->image;
        $this->id = $section->id;
    }
    public function render()
    {
        return view('livewire.sections.form');
    }
}
