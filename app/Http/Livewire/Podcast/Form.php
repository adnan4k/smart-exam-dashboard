<?php

namespace App\Http\Livewire\Podcast;

use App\Models\Podcast;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class Form extends Component
{
    public $title;
    public $description;
    public $episode;
    public $video_id;
    public $is_edit = false;
    public $id = null;
    protected $listeners = ['podcastModal'=>'podcastModal'];
    public $openModal = false;
    public function podcastModal(){
        $this->openModal = true;
     }
    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'episode' => 'required|integer|min:1',
        'video_id' => 'nullable|string|max:255',
    ];

     
    public function save()
    {
        $this->validate();
        $podcast = '';
        if ($this->is_edit) {
            $podcast = Podcast::find($this->id);
         } else {
            $podcast  = new Podcast();
        }
        $podcast->title = $this->title;
        $podcast->description = $this->description;
        $podcast->video_id = $this->video_id;
        $podcast->episode = $this->episode;
        $podcast->save();
        $message = $this->is_edit ? "Edited Successfully!" : "Created Successfully!";
        Toaster::success($message); 
        $this->openModal = false;
        $this->is_edit = false;
        $this->reset();
        $this->dispatch('refreshTable');
        //   $this->redirect('podcast',navigate:true);
    }

    #[On('edit-podcast')]
    public function edit(Podcast $podcast){
      $this->title = $podcast->title;
       $this->description = $podcast->description;
       $this->video_id = $podcast->video_id;
       $this->episode = $podcast->episode;
       $this->is_edit = true;
       $this->openModal = true;
       $this->id = $podcast->id;
    }
    public function render()
    {
        return view('livewire.podcast.form');
    }
}
