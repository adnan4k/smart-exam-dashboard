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
    protected $listeners = ['subjectModal'=>'subjectModal'];
    public function subjectModal(){
        $this->openModal = true;
     }
    protected $rules = [
        'name' => 'required|string|max:255|unique:subjects,name',
    ];

    public function saveSubject()
    {
        $this->validate();

        if ($this->is_edit) {
            $subject = Subject::find($this->id);
            $subject->name = $this->name;
            $subject->save();
            $message = "Subject Updated Successfully!";
        } else {
            Subject::create(['name' => $this->name]);
            $message = "Subject Created Successfully!";
        }

        Toaster::success($message);
        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    public function resetForm()
    {
        $this->reset(['name', 'is_edit', 'id']);
    }

    #[On('edit-subject')]
    public function edit($data)
    {
        $subject = Subject::find($data['subject']);
        $this->id = $subject->id;
        $this->name = $subject->name;
        $this->is_edit = true;
        $this->openModal = true;
    }

    public function render()
    {
        return view('livewire.subjects.form');
    }
}