<div x-data="{ openModal: @entangle('openModal') }" class="flex justify-center px-8">
    <div @click.away="openModal = false"
         x-cloak
         x-show="openModal"
         id="notification-modal"
         tabindex="-1"
         aria-hidden="true"
         class="fixed inset-0 z-50 flex justify-center items-center bg-slate-900/40 backdrop-blur-xs overflow-y-auto p-4">
        
        <div class="relative w-full max-w-xl my-6 bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden">
            <form wire:submit.prevent="saveNotification">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h5 class="font-bold text-slate-800 text-base mb-0 tracking-tight"
                            x-text="@entangle('is_edit') ? 'Edit Announcement' : 'Broadcast Announcement'"></h5>
                        <p class="text-[11px] text-slate-400 mb-0">Push rich notifications with media directly to students.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="action-icon-btn text-slate-400 hover:text-slate-700">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Announcement Title <span class="text-rose-500">*</span></label>
                        <input wire:model="title"
                               class="input-modern text-xs @error('title') border-rose-500 @enderror"
                               type="text"
                               placeholder="e.g. Midterm Simulation Exam Registration Open!">
                        @error('title') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Target Exam Type <span class="text-rose-500">*</span></label>
                        <select wire:model="type_id"
                                class="input-modern bg-white text-xs @error('type_id') border-rose-500 @enderror">
                            <option value="">Select Audience / Exam Type</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <span class="text-[11px] text-slate-400 block mt-1">Only candidates enrolled in this curriculum will receive this alert</span>
                        @error('type_id') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Notification Body <span class="text-rose-500">*</span></label>
                        <textarea wire:model="body"
                                  rows="3"
                                  class="input-modern text-xs @error('body') border-rose-500 @enderror"
                                  placeholder="Write the full message or guidance to be delivered..."></textarea>
                        @error('body') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Banner Image (Optional)</label>
                        <input wire:model="image"
                               type="file"
                               accept="image/*"
                               class="input-modern text-xs">
                        @error('image') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                        
                        @if($existing_image_url)
                            <div class="mt-2.5 p-2 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                                @php
                                    $imageUrl = $existing_image_url;
                                    $isFullUrl = filter_var($imageUrl, FILTER_VALIDATE_URL);
                                    if (!$isFullUrl && !str_starts_with($imageUrl, 'storage/') && !str_starts_with($imageUrl, '/storage/')) {
                                        $imageUrl = asset('storage/' . $imageUrl);
                                    } elseif (!$isFullUrl) {
                                        $imageUrl = asset($imageUrl);
                                    }
                                @endphp
                                <img src="{{ $imageUrl }}" 
                                     alt="Current banner" 
                                     class="w-14 h-14 object-cover rounded-lg border border-slate-200"
                                     onerror="this.style.display='none'">
                                <div class="text-[11px] text-slate-500">
                                    <p class="font-semibold text-slate-700 mb-0.5">Existing Image</p>
                                    <p class="mb-0 text-slate-400">Uploading a new file will replace this media banner.</p>
                                </div>
                            </div>
                        @endif
                        
                        <div wire:loading wire:target="image" class="mt-2 text-xs text-[#58706D] flex items-center gap-2">
                            <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                            <span>Processing image upload...</span>
                        </div>
                        
                        <p class="text-[11px] text-slate-400 mt-1">Supported formats: JPG, PNG, WebP (Max 2MB)</p>
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
                        <i class="fa-solid fa-bullhorn text-xs"></i>
                        <span x-show="!@entangle('is_edit')">Broadcast Now</span>
                        <span x-show="@entangle('is_edit')">Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


