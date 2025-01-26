<?php

namespace App\Http\Livewire\Sections;

use App\Models\Section;
use Livewire\Attributes\On;
use Livewire\Component;

class SectionComponent extends Component
{
    public $sections;
   
    #[On('refreshTable')]
    public function mount(){
        $this->sections = Section::all();
    }   
    public function render()
    {
        return view('livewire.sections.section-component');
    }
}
