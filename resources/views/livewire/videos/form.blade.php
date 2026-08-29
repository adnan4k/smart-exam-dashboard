<div x-data="{ openModal: @entangle('openModal') }" class="flex justify-center px-8">
    <div x-cloak x-show="openModal"
         class="fixed inset-0 z-50 flex justify-center items-start py-8 bg-black bg-opacity-50 overflow-y-auto"
         wire:ignore.self>
        <div class="relative p-4 w-full max-w-2xl">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="saveVideo">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2">
                        {{ $is_edit ? 'Edit Video' : 'Add New Video' }}
                    </h2>

                    <div class="flex flex-col gap-3 w-full border-gray-400">

                        <!-- Exam Type -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Exam Type <span class="text-xs text-gray-400">(optional)</span></label>
                            <select wire:model.live="typeId"
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">All Exam Types</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('typeId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Subject / Chapter -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Subject <span class="text-xs text-gray-400">(optional)</span></label>
                                <select wire:model.live="subjectId"
                                        class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                    <option value="">No specific subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                @error('subjectId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Chapter <span class="text-xs text-gray-400">(optional)</span></label>
                                <select wire:model="chapterId"
                                        class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                    <option value="">No specific chapter</option>
                                    @php $chaptersToShow = !empty($chaptersForSubject) ? $chaptersForSubject : $allChapters; @endphp
                                    @foreach ($chaptersToShow as $chapter)
                                        <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                    @endforeach
                                </select>
                                @error('chapterId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Title</label>
                            <input wire:model="title" type="text" placeholder="e.g., Chapter 1 - Introduction to Kinematics"
                                   class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('title') border-red-500 @enderror">
                            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Description <span class="text-xs text-gray-400">(optional)</span></label>
                            <textarea wire:model="description" rows="2" placeholder="Short summary of what this video covers"
                                      class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"></textarea>
                            @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Source switch -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Video Source</label>
                            <div class="flex gap-4 mt-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="source" value="url">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Paste a link (YouTube / Vimeo / CDN)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model.live="source" value="upload">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Upload a file</span>
                                </label>
                            </div>
                            @error('source') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- URL -->
                        @if ($source === 'url')
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Video URL</label>
                                <input wire:model="videoUrl" type="url" placeholder="https://www.youtube.com/watch?v=..."
                                       class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('videoUrl') border-red-500 @enderror">
                                @error('videoUrl') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <!-- Upload -->
                        @if ($source === 'upload')
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">
                                    Video File <span class="text-xs text-gray-400">(mp4, mov, avi, mkv, webm — max 500MB)</span>
                                </label>
                                <input type="file" accept="video/*" wire:model="videoFile"
                                       class="w-full py-2 border border-slate-200 rounded-lg px-3 dark:bg-gray-600 dark:text-gray-100">

                                <div wire:loading wire:target="videoFile" class="mt-2 text-sm text-gray-500">
                                    Uploading video…
                                </div>

                                <div x-data="{ progress: 0, uploading: false }"
                                     x-on:livewire-upload-start="uploading = true"
                                     x-on:livewire-upload-finish="uploading = false; progress = 0"
                                     x-on:livewire-upload-error="uploading = false"
                                     x-on:livewire-upload-progress="progress = $event.detail.progress"
                                     class="mt-2">
                                    <div x-show="uploading" class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full" style="background-color:#56C596" :style="`width: ${progress}%`"></div>
                                    </div>
                                    <p x-show="uploading" class="text-xs text-gray-500 mt-1" x-text="`${progress}%`"></p>
                                </div>

                                @if ($videoFile)
                                    <p class="text-xs text-green-600 mt-1">
                                        Ready: {{ $videoFile->getClientOriginalName() }}
                                        ({{ number_format($videoFile->getSize() / 1048576, 1) }} MB)
                                    </p>
                                @elseif ($existingFilePath)
                                    <p class="text-xs text-gray-500 mt-1">
                                        Current file: {{ basename($existingFilePath) }} — choose a new file to replace it.
                                    </p>
                                @endif

                                @error('videoFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        <!-- Thumbnail -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Thumbnail <span class="text-xs text-gray-400">(optional image, max 2MB)</span></label>
                            <input type="file" accept="image/*" wire:model="thumbnail"
                                   class="w-full py-2 border border-slate-200 rounded-lg px-3 dark:bg-gray-600 dark:text-gray-100">
                            @if ($thumbnail)
                                <img src="{{ $thumbnail->temporaryUrl() }}" class="w-24 h-16 object-cover mt-2 rounded-lg">
                            @elseif ($existingThumbnailPath)
                                <img src="{{ Storage::disk('public')->url($existingThumbnailPath) }}" class="w-24 h-16 object-cover mt-2 rounded-lg">
                            @endif
                            @error('thumbnail') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Duration / Grade / Order -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Duration <span class="text-xs text-gray-400">(seconds)</span></label>
                                <input wire:model="duration" type="number" min="0" placeholder="e.g., 610"
                                       class="w-full py-3 border border-slate-200 rounded-lg px-3 dark:bg-gray-600 dark:text-gray-100">
                                @error('duration') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Grade <span class="text-xs text-gray-400">(0-12)</span></label>
                                <input wire:model="grade" type="number" min="0" max="12" placeholder="e.g., 9"
                                       class="w-full py-3 border border-slate-200 rounded-lg px-3 dark:bg-gray-600 dark:text-gray-100">
                                @error('grade') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Order</label>
                                <input wire:model="sortOrder" type="number" min="0"
                                       class="w-full py-3 border border-slate-200 rounded-lg px-3 dark:bg-gray-600 dark:text-gray-100">
                                @error('sortOrder') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Language + Active -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Language</label>
                                <select wire:model="language"
                                        class="w-full py-3 border border-slate-200 rounded-lg px-3 dark:bg-gray-600 dark:text-gray-100">
                                    <option value="english">English</option>
                                    <option value="amharic">Amharic</option>
                                    <option value="afan_oromo">Afan Oromo</option>
                                    <option value="tigrinya">Tigrinya</option>
                                    <option value="somali">Somali</option>
                                    <option value="afar">Afar</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('language') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="flex items-center gap-2 cursor-pointer pb-3">
                                    <input type="checkbox" wire:model="isActive">
                                    <span class="text-gray-600 dark:text-gray-400">Visible in the app</span>
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button style="background-color:#56C596;" type="submit"
                                    wire:loading.attr="disabled" wire:target="saveVideo,videoFile,thumbnail"
                                    class="text-white font-medium rounded-lg text-sm px-5 py-2.5 text-center disabled:opacity-50">
                                <span wire:loading.remove wire:target="saveVideo">{{ $is_edit ? 'Save Changes' : 'Create' }}</span>
                                <span wire:loading wire:target="saveVideo">Saving...</span>
                            </button>
                            <button type="button"
                                    @click="openModal = false" wire:click="resetAfterClose"
                                    class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
