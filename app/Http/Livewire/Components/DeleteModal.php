<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use Masmerise\Toaster\Toaster;

class DeleteModal extends Component
{
    public $openDeleteModal = false;
    public $modelClass;
    public  $itemId;
    protected $listeners = ["deleteModalEvent"=> "showDeleteModal"];
    public function showDeleteModal($model,$itemId){

        $this->modelClass = $model;
        $this->itemId = $itemId;
        $this->openDeleteModal = true;
    }

    public function deleteItem()
    {
        // dd($this->modelClass);
        if ($this->modelClass && $this->itemId) {
            $model = $this->modelClass::find($this->itemId);
            if ($model) {
                $model->delete();
                Toaster::success("Deleted Successfully");
                $this->dispatch('refreshTable'); // Optional: Emit event to refresh table or list
                $this->openDeleteModal = false;
                $this->dispatch('refreshTable');
            }
        }
    }
    public function render()
    {
        return view('livewire.components.delete-modal');
    }
}
