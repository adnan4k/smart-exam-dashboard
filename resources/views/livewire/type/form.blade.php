<div x-data="{ openModal: @entangle('openModal') }">
    <div @click.away="openModal = false" x-cloak x-show="openModal" id="type-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div x-data="{ isEdit: @entangle('isEdit') }" class="relative w-full max-w-lg my-6">
            <form class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden" wire:submit.prevent="saveType">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-800" x-text="isEdit ? 'Edit Exam Type' : 'Add New Exam Type'"></h3>
                        <p class="text-xs text-slate-400 mb-0">Define category classification, scope, and optional subscription fee.</p>
                    </div>
                    <button @click="openModal = false" type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type Name <span class="text-rose-500">*</span></label>
                        <input wire:model="name"
                            class="form-control w-full"
                            type="text"
                            placeholder="e.g. National Exam, Regional Assessment, University Entrance">
                        @error('name') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Description</label>
                        <textarea wire:model="description"
                            rows="3"
                            class="form-control w-full"
                            placeholder="Provide a brief summary of this exam category..."></textarea>
                        @error('description') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Access Fee (ETB)</label>
                        <div class="relative">
                            <input wire:model="price"
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control w-full"
                                placeholder="0.00 (leave 0 for free)">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-medium">ETB</span>
                        </div>
                        @error('price') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    <button @click="openModal = false" type="button" class="btn-brand-outline text-xs px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit" class="btn-brand text-xs px-5 py-2 shadow-sm">
                        <span wire:loading.remove wire:target="saveType">
                            <i class="fas fa-check text-xs"></i> <span x-text="isEdit ? 'Update Type' : 'Save Type'"></span>
                        </span>
                        <span wire:loading wire:target="saveType" class="flex items-center gap-1.5">
                            <i class="fas fa-spinner fa-spin text-xs"></i> Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>