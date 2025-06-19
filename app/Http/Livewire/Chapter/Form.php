<?php

namespace App\Http\Livewire\Chapter;

use App\Models\Chapter;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Masmerise\Toaster\Toaster;

class Form extends Component
{

    use WithFileUploads;

    public $name;
    public $description;
    public $image;
    public $is_edit = false;
    public $id;
    public $openModal = false;
    public $fullScreenImage;
    public $showImageModal = false;
    public $subject_id;

    protected $listeners = ['chapterModal' => 'chapterModal'];

    public function chapterModal()
    {
        $this->openModal = true;
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:1024', // Max 1MB
    ];

    public function saveChapter()
    {
        $this->validate();
    
        if ($this->is_edit) {
            $chapter = Chapter::find($this->id);
            $chapter->name = $this->name;
            $chapter->description = $this->description;
            $chapter->subject_id = $this->subject_id; // Set subject_id
    
            if ($this->image) {
                $imagePath = $this->image->store('chapters', 'public');
                $chapter->image = $imagePath;
            }
    
            $chapter->save();
            $message = "Chapter Updated Successfully!";
        } else {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
                'subject_id' => $this->subject_id, // Set subject_id
            ];
    
            if ($this->image) {
                $data['image'] = $this->image->store('chapters', 'public');
            }
    
            Chapter::create($data);
            $message = "Chapter Created Successfully!";
        }
    
        Toaster::success($message);
        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }
    public function resetForm()
    {
        $this->reset(['name', 'description', 'image', 'is_edit', 'id']);
    }
    #[On('edit-chapter')]
    public function edit($chapter)
    {
        $chapter = Chapter::find($chapter);
        $this->id = $chapter->id;
        $this->name = $chapter->name;
        $this->description = $chapter->description;
        $this->subject_id = $chapter->subject_id; // Load subject_id
        $this->is_edit = true;
        $this->openModal = true;
    }

    public function showImage($id)
    {
        $chapter = Chapter::findOrFail($id);
        if ($chapter && $chapter->image) {
            $this->fullScreenImage = $chapter->image;
            $this->showImageModal = true;
        }
    }
    #[On('refreshTable')]
    public function render()
    {
        return view('livewire.chapter.form');
    }
 
}
