<?php

namespace App\Http\Livewire\Referral;

use App\Models\ReferralSetting;
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
        $referrals = ReferralSetting::latest()->get();
        return view('livewire.referral.referral-component',compact('referrals'));
    }
}
