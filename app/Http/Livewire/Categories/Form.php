<?php

namespace App\Http\Livewire\Categories;

use App\Models\Category;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
    public $title;
    public $description;
    public $is_edit;
    public $id;
    protected $listeners = ['categoryModal'=>'categoryModal'];
    public $openModal = false;
    public function categoryModal(){
        $this->openModal = true;
     }
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',

    ];
 

    public function save()
    {
        $this->validate();
        $category = '';
        if ($this->is_edit) {
            $category = Category::find($this->id);
        } else {
            $category  = new Category();
        }
        $category->title = $this->title;
        $category->description = $this->description;

        $category->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";
        Toaster::success($message); 
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
     #[On('edit-category')]
     public function edit(Category $category){
        $this->title = $category->title;
         $this->description = $category->description;
         $this->is_edit = true;
         $this->openModal = true;
         $this->id = $category->id;
      }
    public function render()
    {
        return view('livewire.categories.form');
    }
}
