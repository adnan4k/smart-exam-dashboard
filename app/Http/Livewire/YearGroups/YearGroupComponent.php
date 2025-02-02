<?php

namespace App\Http\Livewire\YearGroups;

use App\Models\YearGroup;
use Livewire\Attributes\On;
use Livewire\Component;

class YearGroupComponent extends Component
{
    public $yearGroups;

    #[On('refreshTable')]
    public function render()
    {
        $this->yearGroups = YearGroup::all();
        return view('livewire.year-groups.year-group-component');
    }
 }

