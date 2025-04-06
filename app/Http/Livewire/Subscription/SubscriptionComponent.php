<?php

namespace App\Http\Livewire\Subscription;

use Livewire\Component;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Masmerise\Toaster\Toaster;

class SubscriptionComponent extends Component
{
    public $subscriptionId;
    public $selectedStatus;
    public $showModal = false;
    public $fullScreenImage;
    public $showImageModal = false;

    // We use the rules property for validation when updating the status.
    protected $rules = [
        'selectedStatus' => 'required|in:pending,paid,failed',
    ];

    /**
     * Render the component view with all subscriptions.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // Eager load related user and yearGroup data
        $subscriptions = Subscription::with('user', 'yearGroup')->get();
        Log::info($subscriptions);
        return view('livewire.subscription.subscription-component', compact('subscriptions'));
    }

    /**
     * Open the modal and load the subscription for editing.
     *
     * @param  int  $subscriptionId
     * @return void
     */
    public function edit($subscriptionId)
    {
        $subscription = Subscription::findOrFail($subscriptionId);
        $this->subscriptionId = $subscription->id;
        $this->selectedStatus = $subscription->payment_status;
        $this->showModal = true;
    }

    /**
     * Update the payment status of the selected subscription.
     *
     * @return void
     */
    public function updateStatus()
    {
        $this->validate();

        $subscription = Subscription::findOrFail($this->subscriptionId);
        $subscription->payment_status = $this->selectedStatus;
        $subscription->save();

        session()->flash('message', 'Subscription status updated successfully.');
        Toaster::success('Subscription status updated successfully.');
        // Close the modal after updating
        $this->showModal = false;
    }

    public function showImage($id)
    {
        $subscription = Subscription::findOrFail($id);
        if ($subscription && $subscription->image) {
            // Set the full screen image (adjust the path if needed)
            $this->fullScreenImage = $subscription->image;
            $this->showImageModal = true;
        }
    }
}
