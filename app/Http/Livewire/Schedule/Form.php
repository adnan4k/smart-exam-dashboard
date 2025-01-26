<?php

namespace App\Http\Livewire\Schedule;

use App\Models\Schedule;
use App\Models\Tour;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
    use WithFileUploads;
    public $day_number;
    public $tour_id;
    public $description;
    protected $listeners = ['scheduleModal' => 'scheduleModal'];
    public $openModal = false;
    public $is_edit = false;
    public $id;
    public $tours;

    public function mount(){
      $this->tours = Tour::all();
    }
    public function scheduleModal()
    {
        $this->openModal = true;
    }

    protected $rules = [
        'tour_id' => 'required',
    ];


    public function save()
    {

        $validated =  $this->validate();
        $schedules = $this->is_edit ? Schedule::find($this->id) : new Schedule();

        $schedules->day_number = $this->day_number;

        $schedules->description = $this->description;


        $schedules->tour_id = $this->tour_id;
        $schedules->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";

        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-schedule')]
    public function edit(Schedule $schedule)
    {
        $this->openModal = true;
        $this->tour_id = $schedule->tour_id;
        $this->day_number  = $schedule->day_number;

        $this->description = $schedule->description;
        $this->is_edit = true;
        $this->id = $schedule->id;
    }
    public function render()
    {
        return view('livewire.schedule.form');
    }
}
