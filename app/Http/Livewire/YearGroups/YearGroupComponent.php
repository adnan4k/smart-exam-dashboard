<?php

namespace App\Http\Livewire\YearGroups;

use App\Models\YearGroup;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class YearGroupComponent extends Component
{
    public $yearGroups;
    public $showDeleteModal = false;
    public $yearGroupToDelete;

    public function confirmDelete($id)
    {
        $this->yearGroupToDelete = YearGroup::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->yearGroupToDelete) {
            try {
                $yearGroupName = $this->yearGroupToDelete->name ?? "ID: {$this->yearGroupToDelete->id}";
                $this->yearGroupToDelete->delete();
                
                $this->showDeleteModal = false;
                $this->yearGroupToDelete = null;
                
                Toaster::success("Year Group '{$yearGroupName}' has been deleted successfully.");
                $this->dispatch('refreshTable');
            } catch (\Exception $e) {
                Toaster::error('Failed to delete year group. Please try again.');
            }
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->yearGroupToDelete = null;
    }

    #[On('refreshTable')]
    public function render()
    {
        $this->yearGroups = YearGroup::all();
        return view('livewire.year-groups.year-group-component');
    }
 }

