<?php

namespace App\Http\Livewire\Podcast;

use App\Models\Podcast;
use Livewire\Component;

class Detial extends Component
{
    public $podcast;
    public $openModal = false;
    protected $listeners = ['openDetailModal' => 'openDetailModal'];
    public function openDetailModal(Podcast $podcast)
    {
        $this->podcast = $podcast;
        $this->openModal = true;
    }
    public function mount(Podcast $podcast)
    {
        $this->podcast = $podcast;
    }
    public function render()
    {
        return view('livewire.podcast.detial');
    }
}
