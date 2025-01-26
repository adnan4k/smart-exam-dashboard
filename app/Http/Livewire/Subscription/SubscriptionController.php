<?php

namespace App\Http\Livewire\Subscription;

use App\Models\Subscription;
use Livewire\Component;

class SubscriptionController extends Component
{
    public function render()
    {
        $subscriptions = Subscription::latest()->get();
        return view('livewire.subscription.subscription-controller',compact('subscriptions'));
    }
}
