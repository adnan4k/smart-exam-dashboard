<?php

namespace App\Http\Livewire\Opportunity\Scholarship;

use App\Models\Scholarship;
use Livewire\Attributes\On;
use Livewire\Component;

class SholarshipComponent extends Component
{
    public $scholarships;
    #[On('refreshTable')]
    public function mount()
    {
        $this->scholarships = Scholarship::with('category')->latest()->get();
    }
    public function render()
    {
        return view('livewire.opportunity.scholarship.sholarship-component');
    }
}
