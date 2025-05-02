<?php

namespace App\Http\Livewire\Subjects;

use App\Models\Subject;
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
    protected $listeners = ['subjectModal'=>'subjectModal'];
    public function subjectModal(){
        $this->openModal = true;
     }
    protected $rules = [
        'name' => 'required|string|max:255|unique:subjects,name',
        'typeId' => 'required|exists:types,id',
        'defaultDuration' => 'required|integer|min:1',
    ];

    public function saveSubject()
    {
        $this->validate();

        if ($this->is_edit) {
            $subject = Subject::find($this->id);
            $subject->name = $this->name;
            $subject->type_id = $this->typeId;
            $subject->default_duration = $this->defaultDuration;
            $subject->save();
            $message = "Subject Updated Successfully!";
        } else {
            Subject::create([
                'name' => $this->name,
                'type_id' => $this->typeId,
                'default_duration' => $this->defaultDuration,
            ]);
            $message = "Subject Created Successfully!";
        }

        Toaster::success($message);
        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    public function resetForm()
    {
        $this->reset(['name', 'typeId', 'defaultDuration', 'is_edit', 'id']);
    }

    #[On('edit-subject')]
    public function edit($data)
    {
        $subject = Subject::find($data['subject']);
        $this->id = $subject->id;
        $this->name = $subject->name;
        $this->typeId = $subject->type_id;
        $this->defaultDuration = $subject->default_duration;
        $this->is_edit = true;
        $this->openModal = true;
    }

    public function render()
    {
        return view('livewire.subjects.form', [
            'types' => \App\Models\Type::all(),
        ]);
    }
}