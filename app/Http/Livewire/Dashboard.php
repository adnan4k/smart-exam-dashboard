<?php

namespace App\Http\Livewire;

use App\Models\Scholarship;
use App\Models\Tour;
use App\Models\Booking;
use App\Models\Category;
use Livewire\Component;

class Dashboard extends Component
{
    public $podcastCount;
    public $vacanyCount;
    public $scholarship;
    public $orderedCounts;

    public function render()
    {
        $this->podcastCount = Tour::count();
        $this->vacanyCount = Booking::count();
        $this->scholarship = Category::count();
        $this->orderedCounts =  Booking::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();
    //    dd($this->orderedCounts);

        return view('livewire.dashboard');
    }
}
