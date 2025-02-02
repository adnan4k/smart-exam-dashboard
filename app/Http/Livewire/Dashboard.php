<?php

namespace App\Http\Livewire;


use Livewire\Component;

class Dashboard extends Component
{
    public $podcastCount;
    public $vacanyCount;
    public $scholarship;
    public $orderedCounts;

    public function render()
    {
        $this->podcastCount = 0 ;
        $this->vacanyCount = 0;
        $this->scholarship = 0 ;
        $this->orderedCounts = 0;
    //    dd($this->orderedCounts);

        return view('livewire.dashboard');
    }
}
