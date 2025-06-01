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
}
