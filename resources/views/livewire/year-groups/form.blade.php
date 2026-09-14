<div x-data="{ openModal: @entangle('openModal') }">
    <div @click.away="openModal = false" x-cloak x-show="openModal" id="year-group-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div x-data="{isEdit:@entangle('is_edit')}" class="relative w-full max-w-md my-6">
            <form class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden" wire:submit.prevent="saveYearGroup">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-800" x-text="isEdit ? 'Edit Year Group' : 'Create Year Group'"></h3>
                        <p class="text-xs text-slate-400 mb-0">Specify academic cohort year (e.g. 2024).</p>
                    </div>
                    <button @click="openModal = false" type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cohort Year <span class="text-rose-500">*</span></label>
                        <input wire:model="year"
                            class="form-control w-full"
                            type="number"
                            placeholder="e.g. 2024">
                        @error('year') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    <button @click="openModal = false" type="button" class="btn-brand-outline text-xs px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit" class="btn-brand text-xs px-5 py-2 shadow-sm">
                        <span wire:loading.remove wire:target="saveYearGroup">
                            <i class="fas fa-check text-xs"></i> <span x-text="isEdit ? 'Save Changes' : 'Create Year Group'"></span>
                        </span>
                        <span wire:loading wire:target="saveYearGroup" class="flex items-center gap-1.5">
                            <i class="fas fa-spinner fa-spin text-xs"></i> Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>