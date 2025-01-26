<?php

namespace App\Http\Livewire\Contact;

use App\Models\Contact;
use Livewire\Component;

class ContactComponent extends Component
{
    public $contacts;
    public function mount(){
        $this->contacts = Contact::where('type','contact')->latest()->get();
    }
    public function render()
    {
        return view('livewire.contact.contact-component');
    }
}
