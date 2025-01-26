<?php

namespace App\Http\Livewire\Opportunity\Vacancy;

use App\Models\Vacancy;
use Livewire\Attributes\On;
use Livewire\Component;

class VacancyComponent extends Component
{
    #[On('refreshTable')]
    public function render()
    {
        return view(
            'livewire.opportunity.vacancy.vacancy-component',
            ['vacancies' => Vacancy::latest()->get()]
        );
    }
}
