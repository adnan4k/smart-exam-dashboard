<?php

namespace App\Http\Livewire\Schedule;

use App\Models\Schedule;
use Livewire\Component;

class ScheduleComponent extends Component
{

    public function render()
    {
        $schedules = Schedule::with('tour')->latest()->get();
        //  dd(vars: $schedules);
        return view('livewire.schedule.schedule-component',
    ['schedules'=>$schedules]);
    }
}
