<?php

namespace App\Http\Livewire\Tour;

use App\Models\Tour;
use Livewire\Component;

class TourComponent extends Component
{

    public function render()
    {
        $tours = Tour::latest()->paginate(7);
        return view('livewire.tour.tour-component',
    ['tours'=>$tours]
    );
    }
}
