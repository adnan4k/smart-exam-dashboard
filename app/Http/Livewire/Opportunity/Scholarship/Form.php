<?php

namespace App\Http\Livewire\Opportunity\Scholarship;

use App\Models\Category;
use App\Models\Scholarship;
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
    public $author;
    public $date;
    public $category;
    public $comments = 0; // Default value
    public $image;
    public $link;
    public $is_edit;
    public $id = null;
    protected $listeners = ['scholarshipModal' => 'scholarshipModal'];
    public $openModal = false;
    public $categories = [];
    public $category_id;
    public function scholarshipModal()
    {
        $this->openModal = true;
    }
    protected $rules = [
        'title' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'date' => 'required|date',
        'category_id' => 'required|max:255',
        'image' => 'required|max:2048', // URL of the image
        'link' => 'required|url|max:2048',
    ];
    public function mount()
    {
        $this->categories = Category::all();
    }
    public function save()
    {
        $this->validate();

        $scholarship = $this->is_edit ? Scholarship::findOrFail($this->id) : new Scholarship();

        $scholarship->title = $this->title;
        $scholarship->author = $this->author;
        $scholarship->date = $this->date;
        $scholarship->category_id = $this->category_id;

        if ($this->image instanceof UploadedFile) {
            if ($this->is_edit && $scholarship->image) {
                // Delete the old image if editing
                Storage::delete($scholarship->image);
            }

            // Store the new image
            $path = $this->image->store('scholarships', 'public');
            $scholarship->image = $path;
        } elseif ($this->is_edit) {
            $scholarship->image = $this->image;
        }
        $scholarship->link = $this->link;

        $scholarship->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";
        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-scholarship')]
    public function edit(Scholarship $scholarship)
    {
        $this->title = $scholarship->title;
        $this->author = $scholarship->author;
        $this->date = $scholarship->date;
        $this->category_id = $scholarship->category_id;
        $this->link = $scholarship->link;
        $this->image = $scholarship->image;

        $this->is_edit = true;
        $this->openModal = true;
        $this->id = $scholarship->id;
        $this->dispatch('refreshTable');
    }

    public function render()
    {
        return view('livewire.opportunity.scholarship.form');
    }
}
