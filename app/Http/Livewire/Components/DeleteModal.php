<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use Masmerise\Toaster\Toaster;

class DeleteModal extends Component
{
    protected $listeners = ['deleteItem' => 'delete'];

    public function delete($params)
    {
        $modelClass = $params['model'];
        $itemId = $params['itemId'];

        if ($modelClass && $itemId) {
            $model = app($modelClass)->find($itemId);

            if ($model) {
                $model->delete();
                Toaster::success('Deleted successfully');
                $this->dispatch('refreshTable');
            } else {
                Toaster::error('Item not found');
            }
        }
    }

    public function render()
    {
        return ''; // No UI/modal needed
    }
}
