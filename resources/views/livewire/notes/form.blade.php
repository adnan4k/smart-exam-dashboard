<div x-data="{
    openModal: @entangle('openModal'),
    typeId: @entangle('typeId').defer,
    subjectId: @entangle('subjectId').defer,
    chapterId: @entangle('chapterId').defer,
    title: @entangle('title').defer,
    isEdit: @entangle('is_edit').defer,
    grade: @entangle('grade').defer,
    language: @entangle('language').defer,

    initQuill() {
        if (window.noteEditor) {
            try { window.noteEditor = null; } catch (e) {}
        }
        const container = document.getElementById('noteEditor');
        if (!container) return false;

        try {
            window.noteEditor = new Quill('#noteEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        ['link'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['formula'],
                        ['clean']
                    ]
                },
                placeholder: 'Write note content here...'
            });

            const lw = window.Livewire.find('{{ $this->getId() }}');
            const contentVal = lw ? lw.get('content') : '';
            if (contentVal && contentVal.trim() !== '') {
                setTimeout(() => {
                    if (window.noteEditor && window.noteEditor.root) {
                        window.noteEditor.root.innerHTML = contentVal;
                    }
                }, 100);
            }
            
            // Listen for content changes from Livewire (when editing)
            if (lw) {
                lw.watch('content', (value) => {
                    if (window.noteEditor && window.noteEditor.root && value) {
                        const currentContent = window.noteEditor.root.innerHTML;
                        if (currentContent !== value) {
                            window.noteEditor.root.innerHTML = value;
                        }
                    }
                });
            }
            
            return true;
        } catch (error) {
            console.error('Error initializing noteEditor:', error);
            return false;
        }
    },

    initQuillWithRetry() {
        let attempts = 0;
        const maxAttempts = 5;
        const tryInit = () => {
            attempts++;
            if (this.initQuill()) return;
            if (attempts < maxAttempts) setTimeout(tryInit, attempts * 100);
        };
        tryInit();
    },

    syncContentToLivewire() {
        if (window.noteEditor) {
            const html = window.noteEditor.root.innerHTML;
            const lw = window.Livewire.find('{{ $this->getId() }}');
            if (lw) {
                lw.set('content', html);
                // Force Livewire to sync immediately
                lw.sync();
            }
        }
    }
}"
x-init="
    $watch('openModal', value => {
        if (value) {
            $nextTick(() => { initQuillWithRetry(); });
        } else {
            $wire.call('resetAfterClose');
        }
    });

    // Re-init after Livewire updates DOM
    Livewire.hook('message.processed', () => {
        if (!window.noteEditor && document.querySelector('#noteEditor')) {
            setTimeout(() => { initQuillWithRetry(); }, 50);
        }
    });
"
class="flex justify-center px-8"
>
    @assets
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    @endassets

    <div @click.away="openModal = false"
         x-cloak
         x-show="openModal"
         id="default-modal"
         tabindex="-1"
         aria-hidden="true"
         class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto"
         wire:ignore.self>
        <div x-data="{}" class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700"
                  @submit.prevent="
                    // Sync all form data to Livewire before submission
                    syncContentToLivewire();
                    $wire.set('typeId', typeId);
                    $wire.set('subjectId', subjectId);
                    $wire.set('chapterId', chapterId);
                    $wire.set('title', title);
                    $wire.set('grade', grade);
                    $wire.set('language', language);
                    // Small delay to ensure sync completes, then call saveNote
                    setTimeout(() => {
                        $wire.call('saveNote');
                    }, 50);
                  ">

                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2" x-text="isEdit ? 'Edit Note' : 'Create Note'"></h2>

                    <div class="flex flex-col gap-2 w-full border-gray-400">

                        <!-- Exam Type -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Exam Type</label>
                            <select x-model="typeId"
                                    @change="$wire.call('updateType', $event.target.value)"
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('typeId') border-red-500 @enderror">
                                <option value="">Select Exam Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('typeId') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subject Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Subject <span class="text-xs text-gray-400">(select type first)</span></label>
                            <select x-model="subjectId"
                                    @change="$wire.call('updateSubject', $event.target.value)"
                                    :disabled="!typeId"
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('subjectId') border-red-500 @enderror">
                                <option value="">All Subjects</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subjectId') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Chapter Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Chapter</label>
                            <select x-model="chapterId"
                                    @change="$wire.set('chapterId', $event.target.value)"
                                    :disabled="!subjectId"
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('chapterId') border-red-500 @enderror">
                                <option value="">All Chapters</option>
                                @php
                                    $chaptersToShow = !empty($chaptersForSubject) ? $chaptersForSubject : $allChapters;
                                @endphp
                                @foreach ($chaptersToShow as $chapter)
                                    <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                @endforeach
                            </select>
                            @error('chapterId') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Title Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Title</label>
                            <input x-model="title"
                                   @input.debounce.300ms="$wire.set('title', $event.target.value)"
                                   class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('title') border-red-500 @enderror"
                                   type="text"
                                   placeholder="Enter note title">
                            @error('title') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Grade Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Grade <span class="text-xs text-gray-400">(optional, 0-12)</span></label>
                            <input x-model.number="grade"
                                   @input.debounce.300ms="$wire.set('grade', $event.target.value)"
                                   class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('grade') border-red-500 @enderror"
                                   type="number" min="0" max="12" placeholder="e.g., 9">
                            @error('grade') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Language Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Language <span class="text-xs text-gray-400">(required)</span></label>
                            <select x-model="language"
                                    @change="$wire.set('language', $event.target.value)"
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('language') border-red-500 @enderror">
                                <option value="english">English</option>
                                <option value="amharic">Amharic</option>
                                <option value="afan_oromo">Afan Oromo</option>
                                <option value="tigrinya">Tigrinya</option>
                                <option value="somali">Somali</option>
                                <option value="afar">Afar</option>
                                <option value="other">Other</option>
                            </select>
                            @error('language') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Content Field (Quill) -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Content</label>
                            <div wire:ignore>
                                <div id="noteEditor" class="@error('content') border-2 border-red-500 rounded @enderror" style="height: 200px;"></div>
                            </div>
                            @error('content') 
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button style="background-color:#56C596;" type="submit"
                                    wire:loading.attr="disabled"
                                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:opacity-50">
                                <span wire:loading.remove x-text="isEdit ? 'Save Changes' : 'Create'"></span>
                                <span wire:loading>Saving...</span>
                            </button>
                            <button @click="openModal = false" type="button"
                                    class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
