<div x-data="{ openModal: @entangle('openModal') }"
     class="flex justify-center px-8">

    <div @click.away="openModal = false"
         x-cloak
         x-show="openModal"
         id="default-modal"
         tabindex="-1"
         aria-hidden="true"
         class="fixed inset-0 z-50 flex justify-center items-center bg-slate-900/40 backdrop-blur-xs overflow-y-auto p-4">
        
        <div x-data="{ isEdit: @entangle('is_edit') }"
             class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden my-6">
            
            <form wire:submit.prevent="saveReferralSetting">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h5 class="font-bold text-slate-800 text-base mb-0 tracking-tight" x-text="isEdit ? 'Edit Milestone Rule' : 'New Milestone Rule'"></h5>
                        <p class="text-[11px] text-slate-400 mb-0">Set required user conversions and reward payout in ETB.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="action-icon-btn text-slate-400 hover:text-slate-700">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Required Referrals <span class="text-rose-500">*</span></label>
                        <input wire:model="required_referrals"
                               type="number" min="1"
                               class="input-modern text-xs @error('required_referrals') border-rose-500 @enderror"
                               placeholder="e.g. 5">
                        <span class="text-[11px] text-slate-400 block mt-1">Number of successful candidate invites needed</span>
                        @error('required_referrals') 
                            <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Reward Amount (ETB) <span class="text-rose-500">*</span></label>
                        <input wire:model="reward_amount"
                               type="number" step="0.01" min="0"
                               class="input-modern text-xs @error('reward_amount') border-rose-500 @enderror"
                               placeholder="e.g. 150.00">
                        <span class="text-[11px] text-slate-400 block mt-1">Cash credit disbursed to inviter upon meeting milestone</span>
                        @error('reward_amount') 
                            <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-200/70">
                        <div>
                            <span class="block text-xs font-bold text-slate-800">Rule Active Status</span>
                            <span class="block text-[11px] text-slate-400">Enable or disable this milestone payout</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="is_active" class="sr-only peer">
                            <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#58706D]"></div>
                        </label>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <button @click="openModal = false" type="button" class="btn-brand-outline text-xs px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="btn-brand text-xs px-5 py-2.5 shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span wire:loading.remove x-text="isEdit ? 'Save Changes' : 'Create Rule'"></span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
