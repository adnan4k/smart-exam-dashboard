<?php

namespace App\Http\Livewire\Referral\ReferralSetting;

use App\Models\ReferralSetting;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Masmerise\Toaster\Toaster;


class Form extends Component
{
    public $required_referrals;
    public $reward_amount;
    public $is_edit = false;
    public $referral_id;
    public $openModal = false;

    protected $rules = [
        'required_referrals' => 'required|integer|min:1',
        'reward_amount' => 'required|numeric|min:0',
    ];
    protected $listeners = ['referralSettingModal'=>'referralSettingModal'];

    public function referralSettingModal()
    {
        // dd('here it is ');
        $this->openModal = true;
    }

    public function saveReferralSetting()
    {
        $this->validate();

        if ($this->is_edit) {
            $setting = ReferralSetting::find($this->referral_id);
            $setting->required_referrals = $this->required_referrals;
            $setting->reward_amount = $this->reward_amount;
            $setting->save();
            $message = "Referral Setting Updated Successfully!";
        } else {
            ReferralSetting::create([
                'required_referrals' => $this->required_referrals,
                'reward_amount' => $this->reward_amount,
            ]);
            $message = "Referral Setting Created Successfully!";
        }

        Toaster::success($message);
        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    public function resetForm()
    {
        $this->reset(['required_referrals', 'reward_amount', 'is_edit', 'referral_id']);
    }

    #[On('edit-referralSetting')]
    public function edit($referralSetting)
    {
        $setting = ReferralSetting::find($referralSetting);
        $this->referral_id = $setting->id;
        $this->required_referrals = $setting->required_referrals;
        $this->reward_amount = $setting->reward_amount;
        $this->is_edit = true;
        $this->openModal = true;
    }

    #[On('refreshTable')]
    public function render()
    {
        $referrals = ReferralSetting::all();
        return view('livewire.referral.referral-setting.form', compact('referrals'));
    }
}