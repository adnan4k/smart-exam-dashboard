<?php

namespace App\Http\Livewire\Referral;

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

    public function openReferralModal()
    {
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

    public function edit($id)
    {
        $setting = ReferralSetting::find($id);
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
        return view('livewire.referral.form', compact('referrals'));
    }
}