<?php

namespace App\Http\Livewire\Packages;

use App\Models\Package;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\WithFileUploads as LivewireWithFileUploads;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
    use LivewireWithFileUploads;
    public $title;
    public $description;
    public $image;
    public $is_edit;
    public $code;
    protected $listeners = ['packageModal' => 'packageModal'];
    public $openModal = false;
    public $id;

    public function packageModal()
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
        $packages = $this->is_edit ? package::find($this->id) : new package();

        $packages->title = $this->title;
        $packages->description = $this->description;
        $packages->code = $this->code;
      
        if ($this->image instanceof UploadedFile) {
            if ($this->is_edit && $packages->image) {
                // Delete the old image if editing
                Storage::delete($packages->image);
            }
        //  dd($this->image);
            // Store the new image
            $path = $this->image->store('packages', 'public');
            $packages->image = $path;
        } elseif ($this->is_edit) {
            $packages->image = $this->image;
        }

        $packages->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";

        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-package')]
    public function edit(Package $package)
    {
        $this->openModal = true;

        $this->title  = $package->title;
        $this->description = $package->description;
        $this->is_edit = true;
        $this->image = $package->image;
        $this->code = $package->code;
        $this->id = $package->id;
    }

        public function render()
    {
        return view('livewire.packages.form');
    }
}
