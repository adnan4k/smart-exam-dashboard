<?php

namespace App\Http\Livewire\Services;

use App\Models\Service;
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
    public $description;
    public $image;
    public $is_edit;
    protected $listeners = ['serviceModal' => 'serviceModal'];
    public $openModal = false;
    public $id;

    public function serviceModal()
    {
        $this->openModal = true;
    }
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'required',
    ];
    public function save()
    {

       $validated =  $this->validate();
        $services = $this->is_edit ? Service::find($this->id) : new Service();

        $services->title = $this->title;
        $services->description = $this->description;
      
        if ($this->image instanceof UploadedFile) {
            if ($this->is_edit && $services->image) {
                // Delete the old image if editing
                Storage::delete($services->image);
            }
        //  dd($this->image);
            // Store the new image
            $path = $this->image->store('services', 'public');
            $services->image = $path;
        } elseif ($this->is_edit) {
            $services->image = $this->image;
        }

        $services->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";

        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-service')]
    public function edit(Service $service)
    {
        $this->openModal = true;

        $this->title  = $service->title;
        $this->description = $service->description;
        $this->is_edit = true;
        $this->image = $service->image;
        $this->id = $service->id;
    }


    public function render()
    {
        return view('livewire.services.form');
    }
}
