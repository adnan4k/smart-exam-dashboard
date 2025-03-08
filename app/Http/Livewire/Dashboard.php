<?php

namespace App\Http\Livewire;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public $questionCount;
    public $subjectCount;
    public $userCount;
    public $subscriptionCount;
    public $orderedCounts;
    public function render()
    {
        $this->questionCount =  Question::count();
        $this->subjectCount =  Subject::count();
        $this->userCount =  User::count();
        $this->subscriptionCount =  Subscription::count();
        $this->orderedCounts =  0;
        
    //    dd($this->orderedCounts);

        return view('livewire.dashboard');
    }
}
