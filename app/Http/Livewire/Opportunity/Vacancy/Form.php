<?php

namespace App\Http\Livewire\Opportunity\Vacancy;

use App\Models\Vacancy;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
    use WithFileUploads;
    public $title;
    public $description;
    public $experience;
    public $deadline;
    public $location;
    public $jobType;
    public $qualifications;
    public $keyResponsibilities;
    public $languages;
    public $startDate;
    public $email;
    public $phone;
    public $is_edit;
    public $id = null;
    protected $listeners = ['vacancyModal' => 'vacancyModal'];
    public $openModal = false;
    public function vacancyModal()
    {
        $this->openModal = true;
    }
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'experience' => 'required|string|max:255',
        'deadline' => 'required|date|after:today',
        'location' => 'required|string|max:255',
        'jobType' => 'required|string|max:255', // Can use enum or predefined values like 'Full-time', 'Part-time'
        'qualifications' => 'required|string',
        'keyResponsibilities' => 'required|string',
        'languages' => 'required|string|max:255', // Comma-separated languages
        'startDate' => 'required|date|after:today',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20', // Can add regex for phone format validation
    ];

    public function save()
    {
        // dd($this->all());
        $this->validate();

        $vacancy = $this->is_edit ? Vacancy::findOrFail($this->id) : new Vacancy();

        $vacancy->title = $this->title;
        $vacancy->description = $this->description;
        $vacancy->experience = $this->experience;
        $vacancy->deadline = $this->deadline;
        $vacancy->location = $this->location;
        $vacancy->jobType = $this->jobType;
        $vacancy->qualifications = $this->qualifications;
        $vacancy->keyResponsibilities = $this->keyResponsibilities;
        $vacancy->languages = $this->languages;
        $vacancy->startDate = $this->startDate;
        $vacancy->email = $this->email;
        $vacancy->phone = $this->phone;

        $vacancy->save();

        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";
        Toaster::success($message);
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
    }
    #[On('edit-vacancy')]
    public function edit(Vacancy $vacancy)
    {
        $this->title = $vacancy->title;
        $this->description = $vacancy->description;
        $this->experience = $vacancy->experience;
        $this->deadline = $vacancy->deadline;
        $this->location = $vacancy->location;
        $this->jobType = $vacancy->jobType;
        $this->qualifications = $vacancy->qualifications;
        $this->keyResponsibilities = $vacancy->keyResponsibilities;
        $this->languages = $vacancy->languages;
        $this->startDate = $vacancy->startDate;
        $this->email = $vacancy->email;
        $this->phone = $vacancy->phone;

        $this->is_edit = true;
        $this->openModal = true;
        $this->id = $vacancy->id;
    }


    public function render()
    {
        return view('livewire.opportunity.vacancy.form');
    }
}
