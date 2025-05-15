<?php

namespace App\Http\Livewire\YearGroups;

use App\Models\YearGroup;
use Livewire\Attributes\On;
use Livewire\Component;

class YearGroupComponent extends Component
{
    public $yearGroups;


    public function delete($id)
    {
        $type = YearGroup::findOrFail($id);
        $type->delete();

        $this->dispatch('refreshTable');
    }

    #[On('refreshTable')]
    public function render()
    {
        $this->yearGroups = YearGroup::all();
        return view('livewire.year-groups.year-group-component');
    }
 }

