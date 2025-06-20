<?php

namespace App\Http\Livewire\Referral;

use App\Models\Referral;
use App\Models\ReferralSetting;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ReferralComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap'; // or 'tailwind' if you use Tailwind

    #[On('refreshTable')]
    public function render()
    {
        $referrals = Referral::with(['referrer', 'referred'])->latest()->paginate(10);
        $referralSettings = ReferralSetting::latest()->get();
        
        return view('livewire.referral.referral-component', compact('referrals', 'referralSettings'));
    }

    public function togglePaymentStatus($referralId)
    {
        $referral = \App\Models\Referral::findOrFail($referralId);
        $referral->is_paid = !$referral->is_paid;
        $referral->save();

        session()->flash('message', 'Payment status updated successfully.');
        $this->dispatch('refreshTable');
    }
}
