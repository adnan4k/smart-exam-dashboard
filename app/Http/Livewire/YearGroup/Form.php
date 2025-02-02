<?php

namespace App\Http\Livewire\YearGroups;

use App\Models\YearGroup;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class YearGroupForm extends Component
{
    public $year;
    public $is_edit = false;
    public $id;
    public $openModal = false;

    protected $rules = [
        'year' => 'required|integer|unique:year_groups,year',
    ];

    public function saveYearGroup()
    {
        $this->validate();

        if ($this->is_edit) {
            $yearGroup = YearGroup::find($this->id);
            $yearGroup->year = $this->year;
            $yearGroup->save();
            $message = "Year Group Updated Successfully!";
        } else {
            YearGroup::create(['year' => $this->year]);
            $message = "Year Group Created Successfully!";
        }

        Toaster::success($message);
        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    public function resetForm()
    {
        $this->reset(['year', 'is_edit', 'id']);
    }

    #[On('edit-yearGroup')]
    public function edit($data)
    {
        $yearGroup = YearGroup::find($data['yearGroup']);
        $this->id = $yearGroup->id;
        $this->year = $yearGroup->year;
        $this->is_edit = true;
        $this->openModal = true;
    }

    public function render()
    {
        return view('livewire.year-groups.form');
    }
}