<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Symfony\Component\CssSelector\Node\FunctionNode;

class DeleteModal extends Component
{
    public $show = false;
    protected $listeners = ['openDeleteModal'];
    public $itemId;
    public $model;

    public function openDeleteModal($itemId, $model)
    {
        // dd($itemId,$model);
        $this->itemId = $itemId;
        $this->model = $model;
        $this->show = true;
    }

    public function delete()
    {
        $modelClass = app($this->model);
        $item = $modelClass::find($this->itemId);

        if ($item) {
            $item->delete();
            Toaster::success('Deleted Successfully');
            $this->dispatch('refreshTable'); // Emit an event for parent components to listen to

        }

        $this->resetModal();
    }

    public function resetModal()
    {
        $this->show = false;
        $this->itemId = null;
        $this->model = null;
    }
    public function render()
    {
        return view('livewire.components.delete-modal');
    }
}
