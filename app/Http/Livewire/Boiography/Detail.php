<?php

namespace App\Http\Livewire\Boiography;

use App\Models\Biography;
use Livewire\Component;

class Detail extends Component
{
    public $biography;
    public $openModal = false;
    protected $listeners = ['openVacancyDetailModal' => 'openDetailModal'];
    public function openDetailModal(Biography $biography)
    {

        $this->biography = $biography;
        $this->openModal = true;
    }
    public function mount(Biography $biography)
    {
        $this->$biography = $biography;
    }
    public function render()
    {
        return view('livewire.boiography.detail');
    }
}
