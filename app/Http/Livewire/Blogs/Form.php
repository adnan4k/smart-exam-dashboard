<?php

namespace App\Http\Livewire\Blogs;

use App\Models\Blog;
use App\Models\Category;
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
    public $content;
    public $image;
    public $author;
    public $category_id;
    public $is_edit;
    public $categories;
    protected $listeners = ['blogModal' => 'blogModal'];
    public $openModal = false;
    public $id;

    public function blogModal()
    {
        $this->openModal = true;
    }
    protected $rules = [
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'image' => 'required',
        'author' => 'required|string',
        'category_id' => 'required',

    ];
    #[On('refreshTable')]
    public function mount()
    {
        $this->categories = Category::get();
        // dd($this->categories);
    }
    public function save()
    {
        $this->validate();
        $blogs = $this->is_edit ? Blog::find($this->id) : new Blog();

        $blogs->title = $this->title;
        $blogs->content = $this->content;
        $blogs->category_id  = $this->category_id;
        $blogs->author = $this->author;
        if ($this->image instanceof UploadedFile) {
            if ($this->is_edit && $blogs->image) {
                // Delete the old image if editing
                Storage::delete($blogs->image);
            }
    
            // Store the new image
            $path = $this->image->store('blogs', 'public');
            $blogs->image = $path;
        } elseif ($this->is_edit) {
            $blogs->image = $this->image;
        }

        $blogs->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";

        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-blog')]
    public function edit(Blog $blog)
    {
        $this->openModal = true;

        $this->title  = $blog->title;
        $this->content = $blog->content;
        $this->category_id = $blog->category_id;
        $this->author = $blog->author;
        $this->is_edit = true;
        $this->image = $blog->image;
        $this->id = $blog->id;
    }

    public function render()
    {
        return view('livewire.blogs.form');
    }
}
