<?php

namespace App\Http\Livewire\Chapter;

use App\Models\Chapter;
use Livewire\Attributes\On;
use Livewire\Component;

class ChapterComponent extends Component
{

    #[On('refreshTable')]
    public function render()
    {
        $chapters = Chapter::all();
        return view('livewire.chapter.chapter-component',compact('chapters'));
    }
}
