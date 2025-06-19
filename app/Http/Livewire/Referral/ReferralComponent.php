<?php

namespace App\Http\Livewire\Referral;

use App\Models\Referral;
use App\Models\ReferralSetting;
use Livewire\Attributes\On;
use Livewire\Component;

class ReferralComponent extends Component
{
    #[On('refreshTable')]
    public function render()
    {
        $referrals = Referral::with(['referrer', 'referred'])->latest()->get();
        $referralSettings = ReferralSetting::latest()->get();
        
        return view('livewire.referral.referral-component', compact('referrals', 'referralSettings'));
    }
    // In app/Http/Livewire/Referral/ReferralComponent.php

public function togglePaymentStatus($referralId)
{
    $referral = \App\Models\Referral::findOrFail($referralId);
    $referral->is_paid = !$referral->is_paid;
    $referral->save();

    session()->flash('message', 'Payment status updated successfully.');
    $this->dispatch('refreshTable');
}
}
