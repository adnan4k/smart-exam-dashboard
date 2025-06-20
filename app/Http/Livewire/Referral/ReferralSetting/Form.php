<?php

namespace App\Http\Livewire\Referral\ReferralSetting;

use App\Models\ReferralSetting;
use Livewire\Component;
use Livewire\Attributes\On;

class Form extends Component
{
    public $openModal = false;
    public $is_edit = false;
    public $referralSettingId;
    public $required_referrals;
    public $reward_amount;
    public $is_active = true;

    protected $rules = [
        'required_referrals' => 'required|integer|min:1',
        'reward_amount' => 'required|numeric|min:0',
        'is_active' => 'boolean'
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->referralSettingId = null;
        $this->required_referrals = null;
        $this->reward_amount = null;
        $this->is_active = true;
        $this->is_edit = false;
    }

    #[On('referralSettingModal-')]
    public function openModal()
    {
        $this->resetForm();
        $this->openModal = true;
    }

    #[On('edit-referralSetting')]
    public function edit($itemId)
    {
        // dd($itemId);
        $referralSetting = ReferralSetting::findOrFail($itemId);
        $this->referralSettingId = $referralSetting->id;
        $this->required_referrals = $referralSetting->required_referrals;
        $this->reward_amount = $referralSetting->reward_amount;
        $this->is_active = $referralSetting->is_active;
        $this->is_edit = true;
        $this->openModal = true;
    }

    public function saveReferralSetting()
    {
        $this->validate();

        if ($this->is_edit) {
            $referralSetting = ReferralSetting::findOrFail($this->referralSettingId);
            $referralSetting->update([
                'required_referrals' => $this->required_referrals,
                'reward_amount' => $this->reward_amount,
                'is_active' => $this->is_active
            ]);
            session()->flash('message', 'Referral setting updated successfully.');
        } else {
            ReferralSetting::create([
                'required_referrals' => $this->required_referrals,
                'reward_amount' => $this->reward_amount,
                'is_active' => $this->is_active
            ]);
            session()->flash('message', 'Referral setting created successfully.');
        }

        $this->openModal = false;
        $this->resetForm();
        $this->dispatch('refreshTable');
    }

    public function render()
    {
        return view('livewire.referral.referral-setting.form');
    }
}