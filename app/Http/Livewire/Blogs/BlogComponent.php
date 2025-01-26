<?php

namespace App\Http\Livewire\Blogs;

use App\Models\Blog;
use Livewire\Attributes\On;
use Livewire\Component;

class BlogComponent extends Component
{ 
    public $blogs;
    #[On('refreshTable')]
    public function mount(){
        $this->blogs = Blog::with('category')->get();
        // dd($this->blogs);
    }
    public function render()
    {
        return view('livewire.blogs.blog-component');
    }
}
