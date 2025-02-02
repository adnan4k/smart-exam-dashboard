<?php

namespace App\Http\Livewire\Subjects;

use App\Models\Subject;
use Livewire\Attributes\On;
use Livewire\Component;

class SubjectComponent extends Component
{
    public $subjects;

    #[On('refreshTable')]
    public function render()
    {
        $this->subjects = Subject::all();
        return view('livewire.subjects.subject-component');
    }
}
