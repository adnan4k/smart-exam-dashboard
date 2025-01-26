<?php

namespace App\Http\Livewire\Packages;

use App\Models\Package;
use Livewire\Attributes\On;
use Livewire\Component;

class PackageComponent extends Component
{
    public $packages;
   
    #[On('refreshTable')]
    public function mount(){
        $this->packages = Package::all();
    }   
    public function render()
    {
        return view('livewire.packages.package-component');
    }
}
