<?php

namespace App\Http\Livewire\Chapter;

use App\Models\Chapter;
use Livewire\Attributes\On;
use Livewire\Component;

class ChapterComponent extends Component
{



    public function delete($id)
    {
        $type = Chapter::findOrFail($id);
        $type->delete();

        $this->dispatch('refreshTable');
    }

    #[On('refreshTable')]
    public function render()
    {
        $chapters = Chapter::all();
        return view('livewire.chapter.chapter-component',compact('chapters'));
    }

    
}
