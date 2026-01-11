<?php

namespace App\Http\Livewire\Subjects;

use App\Models\Subject;
use App\Models\Type;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
    public $name;
    public $is_edit = false;
    public $id;
    public $openModal = false;
    public $typeId;
    public $defaultDuration;
    public $year;
    public $region;
    public $isRegional = false; // Add this as a regular property
    public $isSample = false;

    protected $listeners = ['subjectModal' => 'subjectModal'];

    // Add this method to your Livewire component
    public function handleTypeChange($typeId)
    {
        $this->typeId = $typeId;

        // Check if the selected type is 'regional'
        $selectedType = \App\Models\Type::find($typeId);
        $this->isRegional = $selectedType && strtolower($selectedType->name) === 'regional';

        // Reset region when type changes if not regional
        if (!$this->isRegional) {
            $this->region = null;
        }
    }

    protected function checkIfRegional($typeId)
    {
        if (!$typeId) return false;

        $type = Type::find($typeId);
        return $type && strtolower($type->name) === 'regional';
    }

    public function subjectModal()
    {
        $this->openModal = true;
    }

    protected $rules = [
        'name' => 'required',
        'typeId' => 'required|exists:types,id',
        'defaultDuration' => 'required|integer|min:1',
        'region' => 'required_if:isRegional,true',
         'year' => 'required',
         'isSample' => 'boolean'
    ];

    public function saveSubject()
    {
        $this->validate();
        $subjectData = [
            'name' => $this->name,
            'type_id' => $this->typeId,
            'default_duration' => $this->defaultDuration,
            'region' => $this->isRegional ? $this->region : null,
            'year' => $this->year, // Store the year directly
            'is_sample' => $this->isSample,
        ];
        //   dd($subjectData);
        if ($this->is_edit) {
            $subject = Subject::find($this->id);
            $subject->update($subjectData);
            $message = "Subject Updated Successfully!";
        } else {

           $subject  =  Subject::create($subjectData);

            $message = "Subject Created Successfully!";
        }

        Toaster::success($message);
        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    public function resetForm()
    {
        $this->reset(['name', 'typeId', 'year','defaultDuration', 'is_edit', 'id', 'region', 'isRegional', 'isSample']);
    }

    #[On('edit-subject')]
    public function edit($subject)
    {
        $subject = Subject::find($subject);
        $this->id = $subject->id;
        $this->name = $subject->name;
        $this->typeId = $subject->type_id;
        $this->defaultDuration = $subject->default_duration;
        $this->region = $subject->region;
        $this->isRegional = $this->checkIfRegional($subject->type_id);
        $this->year = $subject->year;
        $this->isSample = $subject->is_sample;
        $this->is_edit = true;
        $this->openModal = true;
    }

    public function render()
    {
        return view('livewire.subjects.form', [
            'types' => Type::all(),
        ]);
    }
}
