<?php

namespace App\Http\Livewire\Subscription;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Masmerise\Toaster\Toaster;

class SubscriptionComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $subscriptionId;
    public $selectedStatus;
    public $selectedSubscription;
    public $showModal = false;
    public $fullScreenImage;
    public $showImageModal = false;
    public $showDeleteModal = false;
    public $subscriptionToDelete;

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
        // Eager load related user, package, subjects, and yearGroup data with pagination
        $subscriptions = Subscription::with(['user.type', 'yearGroup', 'package', 'subjects'])
            ->latest('created_at')
            ->orderBy('id', 'desc')
            ->paginate(10);
        
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
        $subscription = Subscription::with(['user.type', 'package', 'subjects'])->findOrFail($subscriptionId);
        $this->subscriptionId = $subscription->id;
        $this->selectedSubscription = $subscription;
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

        if ($this->selectedStatus === 'paid' && $subscription->package_id) {
            $subscription->syncDefaultSubjects();
        }

        session()->flash('message', 'Subscription status updated successfully.');
        Toaster::success('Subscription status updated successfully.');
        // Close the modal after updating
        $this->showModal = false;
        $this->resetPage();
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

    public function confirmDelete($subscriptionId)
    {
        $this->subscriptionToDelete = Subscription::with(['user', 'package'])->findOrFail($subscriptionId);
        $this->showDeleteModal = true;
    }

    public function deleteSubscription()
    {
        if (! $this->subscriptionToDelete) {
            return;
        }

        try {
            $subscription = Subscription::findOrFail($this->subscriptionToDelete->id);
            $image = $subscription->image;

            // subscription_subject rows are removed by the FK cascade.
            $subscription->delete();

            if ($image && Storage::disk('public')->exists($image)) {
                Storage::disk('public')->delete($image);
            }

            $this->showDeleteModal = false;
            $this->subscriptionToDelete = null;
            $this->resetPage();

            Toaster::success('Subscription deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete subscription.', ['id' => $this->subscriptionToDelete->id, 'error' => $e->getMessage()]);
            Toaster::error('Failed to delete subscription. Please try again.');
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->subscriptionToDelete = null;
    }
}
