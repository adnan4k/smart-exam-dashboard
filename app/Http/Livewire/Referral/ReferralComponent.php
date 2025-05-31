<?php

namespace App\Http\Livewire\Referral;

use App\Models\Referral;
use App\Models\ReferralSetting;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;

class ReferralComponent extends Component
{
    public $referralSetting = [];

    #[On('refreshTable')]
    public function render()
    {
        $referrals = Referral::with(['referrer', 'referred'])->latest()->get();
        $referralSettings = ReferralSetting::latest()->get();
        
        return view('livewire.referral.referral-component', compact('referrals', 'referralSettings'));
    }
}
