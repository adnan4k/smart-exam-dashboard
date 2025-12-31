<?php

namespace App\Http\Livewire\Type;

use App\Models\Type;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class TypeComponent extends Component
{
    use WithFileUploads;

    public $name;
    public $description;
    public $image;
    public $is_edit = false;
    public $type_id;
    public $openModal = false;
    public $fullScreenImage;
    public $showImageModal = false;
    public $showDeleteModal = false;
    public $typeToDelete;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'image' => 'nullable|image|max:1024', // Max 1MB
    ];

    public function typeModal()
    {
        $this->openModal = true;
    }

    public function saveType()
    {
        $this->validate();

        if ($this->is_edit) {
            $type = Type::find($this->type_id);
            $type->name = $this->name;
            $type->description = $this->description;
            
            if ($this->image) {
                $imagePath = $this->image->store('types', 'public');
                $type->image = $imagePath;
            }
            
            $type->save();
            $message = "Type Updated Successfully!";
        } else {
            $data = [
                'name' => $this->name,
                'description' => $this->description,
            ];

            if ($this->image) {
                $data['image'] = $this->image->store('types', 'public');
            }

            Type::create($data);
            $message = "Type Created Successfully!";
        }

        Toaster::success($message);
        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    public function resetForm()
    {
        $this->reset(['name', 'description', 'image', 'is_edit', 'type_id']);
    }

    public function edit($id)
    {
        $type = Type::find($id);
        $this->type_id = $type->id;
        $this->name = $type->name;
        $this->description = $type->description;
        $this->is_edit = true;
        $this->openModal = true;
    }

    public function showImage($id)
    {
        $type = Type::findOrFail($id);
        if ($type && $type->image) {
            $this->fullScreenImage = $type->image;
            $this->showImageModal = true;
        }
    }

    public function confirmDelete($id)
    {
        $this->typeToDelete = Type::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->typeToDelete) {
            try {
                $typeName = $this->typeToDelete->name;
                $this->typeToDelete->delete();
                
                $this->showDeleteModal = false;
                $this->typeToDelete = null;
                
                Toaster::success("Type '{$typeName}' has been deleted successfully.");
                $this->dispatch('refreshTable');
            } catch (\Exception $e) {
                Toaster::error('Failed to delete type. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->typeToDelete = null;
    }

    #[On('refreshTable')]
    public function render()
    {
        $types = Type::all();
        return view('livewire.type.type-component', compact('types'));
    }
}
