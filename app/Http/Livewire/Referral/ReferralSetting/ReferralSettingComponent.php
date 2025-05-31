<?php

namespace App\Http\Livewire\Referral\ReferralSetting;

use App\Models\ReferralSetting;
use Livewire\Component;
use Livewire\WithPagination;

class ReferralSettingComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $referralSettings = ReferralSetting::latest()->paginate(10);
        return view('livewire.referral.referral-setting.referral-setting-component', [
            'referralSettings' => $referralSettings
        ]);
    }
}
