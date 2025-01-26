<?php

namespace App\Http\Livewire\Testimony;

use App\Models\Service;
use App\Models\Testimony;
use Livewire\Attributes\On;
use Livewire\Component;

class TestimonyComponent extends Component
{
    public $testimonies; 
    #[On('refreshTable')]
     public function mount(){
            $this->testimonies = Testimony::get();
     }
    public function render()
    {
        return view('livewire.testimony.testimony-component');
    }
}
