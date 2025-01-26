<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class ToastNotifications extends Component
{
    public $showDeleteToast = false;
   protected $listeners = ['itemDeleted' => 'showDeleteToastMethod'];
    
   public function showDeleteToastMethod(){
    $this->showDeleteToast = true;
   }
    public function render()
    {
        return view('livewire.components.toast-notifications');
    }
}
