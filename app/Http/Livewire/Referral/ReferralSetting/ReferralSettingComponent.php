<?php

namespace App\Http\Livewire\Referral\ReferralSetting;

use App\Models\ReferralSetting;
use Livewire\Attributes\On;
use Livewire\Component;

class ReferralSettingComponent extends Component
{   #[On('refreshTable')]
    public function render()
    {
        $referralSettings = ReferralSetting::latest()->get();

        return view('livewire.referral.referral-setting.referral-setting-component',compact('referralSettings'));
    }
}
