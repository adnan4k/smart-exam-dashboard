<div
    x-data="{ openModal: @entangle('openModal') }"
    class="flex justify-center px-8">

    <div
        @click.away="openModal = false"
        x-cloak
        x-show="openModal" id="default-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto">
        <div
            x-data="{isEdit:@entangle('is_edit')}"
            class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="saveReferralSetting">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2" x-text="isEdit ? 'Edit Referral Setting' : 'Create Referral Setting'"></h2>

                    <div class="flex flex-col gap-2 w-full border-gray-400">
                        <!-- Required Referrals Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Required Referrals</label>
                            <input
                                wire:model="required_referrals"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="number">
                            @error('required_referrals') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Reward Amount Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Reward Amount (ETB)</label>
                            <input
                                wire:model="reward_amount"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="number" step="0.01">
                            @error('reward_amount') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Active Status Toggle -->
                        <div class="flex items-center gap-2">
                            <label class="text-gray-600 dark:text-gray-400">Active Status</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button
                                style="background-color:#56C596;"
                                type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <span x-text="isEdit ? 'Update' : 'Create'"></span>
                            </button>
                            <button
                                @click="openModal = false"
                                type="button"
                                class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
