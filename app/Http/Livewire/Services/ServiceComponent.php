<?php

namespace App\Http\Livewire\Services;

use App\Models\Service;
use Livewire\Attributes\On;
use Livewire\Component;

class ServiceComponent extends Component
{
   public $services; 
   #[On('refreshTable')]
    public function mount(){
           $this->services = Service::get();
        //    dd(vars: $this->services);
    }
    public function render()
    {
        return view('livewire.services.service-component');
    }
}
