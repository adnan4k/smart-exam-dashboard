<?php

namespace App\Http\Livewire\Categories;

use App\Models\Category;
use Livewire\Attributes\On;
use Livewire\Component;

class CategoryComponent extends Component
{
     public $categories;
     #[On('refreshTable')]
     public function render()
    {
        $this->categories = Category::get();
        return view('livewire.categories.category-component');
    }
}
