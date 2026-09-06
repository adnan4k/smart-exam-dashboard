<div x-data="{ openModal: @entangle('openModal') }">
    <div x-cloak x-show="openModal"
         class="fixed inset-0 z-50 flex justify-center items-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         wire:ignore.self>
        <div class="relative w-full max-w-2xl my-6">
            <form class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden" wire:submit.prevent="saveVideo">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">
                            {{ $is_edit ? 'Edit Video Lesson' : 'Upload New Video Lesson' }}
                        </h3>
                        <p class="text-xs text-slate-400 mb-0">Attach instructional media, select syllabus mappings, and upload video files.</p>
                    </div>
                    <button @click="openModal = false" wire:click="resetAfterClose" type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <!-- Exam Type -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type <span class="text-xs text-slate-400 font-normal lowercase">(optional)</span></label>
                        <select wire:model.live="typeId" class="form-select w-full">
                            <option value="">All Exam Types</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('typeId') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Subject / Chapter -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject <span class="text-xs text-slate-400 font-normal lowercase">(optional)</span></label>
                            <select wire:model.live="subjectId" class="form-select w-full">
                                <option value="">No specific subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subjectId') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Chapter <span class="text-xs text-slate-400 font-normal lowercase">(optional)</span></label>
                            <select wire:model="chapterId" class="form-select w-full">
                                <option value="">No specific chapter</option>
                                @php $chaptersToShow = !empty($chaptersForSubject) ? $chaptersForSubject : $allChapters; @endphp
                                @foreach ($chaptersToShow as $chapter)
                                    <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                @endforeach
                            </select>
                            @error('chapterId') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Lesson Title <span class="text-rose-500">*</span></label>
                        <input wire:model="title" type="text" placeholder="e.g. Chapter 1 - Introduction to Kinematics"
                               class="form-control w-full @error('title') border-rose-500 @enderror">
                        @error('title') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Description <span class="text-xs text-slate-400 font-normal lowercase">(optional)</span></label>
                        <textarea wire:model="description" rows="2" placeholder="Brief outline of lesson concepts..."
                                  class="form-control w-full"></textarea>
                        @error('description') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Upload -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Video Stream Asset <span class="text-xs text-slate-400 font-normal lowercase">(mp4, mov, avi, webm — max 500MB)</span>
                        </label>

                        <div x-data="{ uploading: false, progress: 0, sizeMb: 0 }"
                             x-on:livewire-upload-start="uploading = true; progress = 0"
                             x-on:livewire-upload-progress="progress = $event.detail.progress"
                             x-on:livewire-upload-finish="uploading = false; progress = 100"
                             x-on:livewire-upload-error="uploading = false; progress = 0"
                             x-on:livewire-upload-cancel="uploading = false; progress = 0">

                            <input type="file" accept="video/*" wire:model="videoFile"
                                   x-on:change="sizeMb = ($event.target.files[0]?.size ?? 0) / 1048576"
                                   class="form-control text-xs w-full">

                            <div x-show="uploading" style="display:none" class="mt-2.5">
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden"
                                     role="progressbar" aria-valuemin="0" aria-valuemax="100"
                                     :aria-valuenow="progress">
                                    <div class="h-2 rounded-full transition-all duration-150 ease-out bg-[#58706D]"
                                         :style="`width: ${progress}%`"></div>
                                </div>
                                <div class="flex justify-between gap-2 text-[11px] text-slate-500 mt-1">
                                    <span x-text="progress < 100 ? `Uploading video… ${progress}%` : 'Finalizing file stream…'"></span>
                                    <span x-show="sizeMb > 0"
                                          x-text="`${(sizeMb * progress / 100).toFixed(1)} / ${sizeMb.toFixed(1)} MB`"></span>
                                </div>
                            </div>
                        </div>
                        @error('videoFile') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror

                        @if ($existingFilePath && !$videoFile)
                            <div class="mt-2 p-2.5 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between">
                                <span class="text-xs text-slate-600 flex items-center gap-1.5">
                                    <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
                                    Existing file attached: <span class="font-mono text-slate-500 text-[11px]">{{ basename($existingFilePath) }}</span>
                                </span>
                                <span class="text-[11px] text-slate-400">Upload replacement to overwrite</span>
                            </div>
                        @endif
                    </div>

                    <!-- Custom Thumbnail -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Custom Thumbnail <span class="text-xs text-slate-400 font-normal lowercase">(optional, jpg, png — max 4MB)</span></label>
                        <div class="flex items-center gap-3">
                            <input type="file" accept="image/*" wire:model="thumbnail" class="form-control text-xs w-full">
                            @if ($thumbnail)
                                <img src="{{ $thumbnail->temporaryUrl() }}" class="w-12 h-10 object-cover rounded-lg border border-slate-200 flex-shrink-0">
                            @elseif ($existingThumbnailPath)
                                <img src="{{ asset('storage/' . $existingThumbnailPath) }}" class="w-12 h-10 object-cover rounded-lg border border-slate-200 flex-shrink-0">
                            @endif
                        </div>
                        @error('thumbnail') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Duration / Grade / Sort Order -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Duration (Seconds)</label>
                            <input wire:model="duration" type="number" min="0" placeholder="e.g. 720"
                                   class="form-control w-full">
                            @error('duration') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Grade <span class="text-[10px] text-slate-400 font-normal">(0–12)</span></label>
                            <input wire:model="grade" type="number" min="0" max="12" placeholder="e.g. 9"
                                   class="form-control w-full">
                            @error('grade') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Display Order</label>
                            <input wire:model="sortOrder" type="number" min="0" placeholder="0"
                                   class="form-control w-full">
                            @error('sortOrder') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Language + Active -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-center pt-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Spoken Language</label>
                            <select wire:model="language" class="form-select w-full">
                                <option value="english">English</option>
                                <option value="amharic">Amharic</option>
                                <option value="afan_oromo">Afan Oromo</option>
                                <option value="tigrinya">Tigrinya</option>
                                <option value="somali">Somali</option>
                                <option value="afar">Afar</option>
                                <option value="other">Other</option>
                            </select>
                            @error('language') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                        </div>
                        <div class="pt-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="isActive" class="w-4 h-4 rounded text-[#58706D] focus:ring-[#58706D] border-slate-300">
                                <span class="text-xs font-bold text-slate-700">Publish & make visible in the app</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    <button type="button" @click="openModal = false" wire:click="resetAfterClose" class="btn-brand-outline text-xs px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit"
                            wire:loading.attr="disabled" wire:target="saveVideo,videoFile,thumbnail"
                            class="btn-brand text-xs px-5 py-2 shadow-sm disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveVideo">
                            <i class="fas fa-check text-xs"></i> {{ $is_edit ? 'Save Changes' : 'Upload Video' }}
                        </span>
                        <span wire:loading wire:target="saveVideo" class="flex items-center gap-1.5">
                            <i class="fas fa-spinner fa-spin text-xs"></i> Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
