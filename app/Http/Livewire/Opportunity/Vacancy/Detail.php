<?php namespace App\Http\Livewire\Opportunity\Vacancy;

use App\Models\Vacancy;
use Livewire\Component;

class Detail extends Component
{
    public Vacancy $vacancy;

    public function mount(Vacancy $vacancy)
    {
        $this->vacancy = $vacancy;
    }

    public function render()
    {
        return view('livewire.opportunity.vacancy.detail', [
            'vacancy' => $this->vacancy,
        ]);
    }
}
