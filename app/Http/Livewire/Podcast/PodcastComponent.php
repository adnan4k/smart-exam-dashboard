<?php

namespace App\Http\Livewire\Podcast;

use App\Models\Podcast;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class PodcastComponent extends Component
{
    use WithPagination;


    #[On('refreshTable')]
     public function render()
    {
        // Fetch paginated data
        $podcasts = Podcast::orderBy('created_at', 'desc')->paginate(3);
        // dd($podcasts);
        // Pass the paginated data to the Blade view
        return view('livewire.podcast.podcast-component', [
            'podcasts' => $podcasts,
        ]);
    }
}
