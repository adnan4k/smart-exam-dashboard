<?php

namespace App\Http\Livewire\Boiography;

use App\Models\Biography;
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
    public $detail;
    public $image;
    public $is_edit;
    protected $listeners = ['bioModal' => 'bioModal'];
    public $openModal = false;
    public $id;

    public function bioModal()
    {
        $this->openModal = true;
    }
    protected $rules = [
        'name' => 'required|string|max:255', // Name is required, must be a string, max length 255
        'position' => 'required|string|max:255', // Position is required, must be a string, max length 255
        'detail' => 'required|string', // Detail is required and must be a string
        'image' => 'required', // Optional image, must be an image file (max 2MB)
    ];
    public function render()
    {
        return view('livewire.boiography.form');
    }

    public function save()
    {
        $this->validate();
        $biography = $this->is_edit ? Biography::find($this->id) : new Biography();

        $biography->name = $this->name;
        $biography->position = $this->position;

        $biography->detail = $this->detail;

        if ($this->image instanceof UploadedFile) {
            if ($this->is_edit && $biography->image) {
                // Delete the old image if editing
                Storage::delete($biography->image);
            }
    
            // Store the new image
            $path = $this->image->store('biography', 'public');
            $biography->image = $path;
        } elseif ($this->is_edit) {
            $biography->image = $this->image;
        }

        $biography->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";

        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-biography')]
    public function edit(Biography $biography)
    {
        $this->openModal = true;

        $this->name  = $biography->name;
        $this->detail = $biography->detail;
        $this->position = $biography->position;
        $this->is_edit = true;
        $this->image = $biography->image;
        $this->id = $biography->id;
    }
}
