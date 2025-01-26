<?php

namespace App\Http\Livewire\Testimony;

use App\Models\Testimony;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
 use WithFileUploads;
    public $name;
    public $position;
    public $content;
    public $image;
    public $is_edit;
    protected $listeners = ['testimonyModal' => 'testimonyModal'];
    public $openModal = false;
    public $id;

    public function testimonyModal()
    {
        $this->openModal = true;
    }
    protected $rules = [
        'name' => 'required|string|max:255',
        'content' => 'required|string',
        'image' => 'required',
    ];
    public function save()
    {

       $validated =  $this->validate();
        $testimonys = $this->is_edit ? Testimony::find($this->id) : new Testimony();

        $testimonys->name = $this->name;
        $testimonys->position = $this->position;

        $testimonys->content = $this->content;
      
        if ($this->image instanceof UploadedFile) {
            if ($this->is_edit && $testimonys->image) {
                // Delete the old image if editing
                Storage::delete($testimonys->image);
            }
        //  dd($this->image);
            // Store the new image
            $path = $this->image->store('testimony', 'public');
            $testimonys->image = $path;
        } elseif ($this->is_edit) {
            $testimonys->image = $this->image;
        }

        $testimonys->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";

        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-testimony')]
    public function edit(Testimony $testimony)
    {
        $this->openModal = true;

        $this->name  = $testimony->name;
        $this->position  = $testimony->position;

        $this->content = $testimony->content;
        $this->is_edit = true;
        $this->image = $testimony->image;
        $this->id = $testimony->id;
    }

    public function render()
    {
        return view('livewire.testimony.form');
    }
}
