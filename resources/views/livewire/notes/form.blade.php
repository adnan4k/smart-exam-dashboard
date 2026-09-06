<div x-data="{
    openModal: @entangle('openModal'),
    typeId: @entangle('typeId'),
    subjectId: @entangle('subjectId'),
    chapterId: @entangle('chapterId'),
    title: @entangle('title'),
    isEdit: @entangle('is_edit'),
    grade: @entangle('grade'),
    language: @entangle('language'),

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
                // Livewire v3 automatically syncs when using set()
            }
        }
    }
}"
x-init="
    $watch('openModal', value => {
        if (value) {
            $nextTick(() => { 
                initQuillWithRetry();
                // Sync Livewire values to Alpine when modal opens (especially for edit mode)
                setTimeout(() => {
                    if ($wire.get('is_edit')) {
                        typeId = $wire.get('typeId') || null;
                        subjectId = $wire.get('subjectId') || null;
                        chapterId = $wire.get('chapterId') || null;
                        title = $wire.get('title') || '';
                        grade = $wire.get('grade') || null;
                        language = $wire.get('language') || 'english';
                        
                        // Update Quill editor with content
                        const content = $wire.get('content');
                        if (window.noteEditor && content) {
                            window.noteEditor.root.innerHTML = content;
                        }
                    }
                }, 200);
            });
        } else {
            $wire.call('resetAfterClose');
        }
    });

    // Re-init after Livewire updates DOM
    Livewire.hook('message.processed', () => {
        if (!window.noteEditor && document.querySelector('#noteEditor')) {
            setTimeout(() => { initQuillWithRetry(); }, 50);
        }
        
        // Sync values after Livewire updates (for edit mode)
        if ($wire.get('is_edit') && openModal) {
            setTimeout(() => {
                typeId = $wire.get('typeId') || null;
                subjectId = $wire.get('subjectId') || null;
                chapterId = $wire.get('chapterId') || null;
                title = $wire.get('title') || '';
                grade = $wire.get('grade') || null;
                language = $wire.get('language') || 'english';
                
                const content = $wire.get('content');
                if (window.noteEditor && content) {
                    window.noteEditor.root.innerHTML = content;
                }
            }, 100);
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
         class="fixed inset-0 z-50 flex justify-center items-center bg-slate-900/40 backdrop-blur-xs overflow-y-auto p-4"
         wire:ignore.self>
        <div x-data="{}" class="relative w-full max-w-2xl my-8 bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden">
            <form @submit.prevent="
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

                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h5 class="font-bold text-slate-800 text-base mb-0 tracking-tight" x-text="isEdit ? 'Edit Learning Note' : 'Create Learning Note'"></h5>
                        <p class="text-[11px] text-slate-400 mb-0">Compose formatted study material with math equations &amp; rich formatting.</p>
                    </div>
                    <button type="button" @click="openModal = false" class="action-icon-btn text-slate-400 hover:text-slate-700">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar">
                    
                    <!-- Exam Type & Subject -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type <span class="text-rose-500">*</span></label>
                            <select x-model="typeId"
                                    @change="$wire.call('updateType', $event.target.value)"
                                    class="input-modern bg-white text-xs @error('typeId') border-rose-500 @enderror">
                                <option value="">Select Exam Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('typeId') 
                                <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject</label>
                            <select x-model="subjectId"
                                    @change="$wire.call('updateSubject', $event.target.value)"
                                    :disabled="!typeId"
                                    class="input-modern bg-white text-xs disabled:bg-slate-100 disabled:cursor-not-allowed @error('subjectId') border-rose-500 @enderror">
                                <option value="">All Subjects</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subjectId') 
                                <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Chapter & Grade -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Chapter</label>
                            <select x-model="chapterId"
                                    @change="$wire.set('chapterId', $event.target.value)"
                                    :disabled="!subjectId"
                                    class="input-modern bg-white text-xs disabled:bg-slate-100 disabled:cursor-not-allowed @error('chapterId') border-rose-500 @enderror">
                                <option value="">All Chapters</option>
                                @php
                                    $chaptersToShow = !empty($chaptersForSubject) ? $chaptersForSubject : $allChapters;
                                @endphp
                                @foreach ($chaptersToShow as $chapter)
                                    <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                @endforeach
                            </select>
                            @error('chapterId') 
                                <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Grade Level <span class="text-[11px] text-slate-400 font-normal lowercase">(0–12 optional)</span></label>
                            <input x-model.number="grade"
                                   @input.debounce.300ms="$wire.set('grade', $event.target.value)"
                                   class="input-modern text-xs @error('grade') border-rose-500 @enderror"
                                   type="number" min="0" max="12" placeholder="e.g. 9">
                            @error('grade') 
                                <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Note Title -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Note Title <span class="text-rose-500">*</span></label>
                        <input x-model="title"
                               @input.debounce.300ms="$wire.set('title', $event.target.value)"
                               class="input-modern text-xs @error('title') border-rose-500 @enderror"
                               type="text"
                               placeholder="e.g. Chapter 4: Electric Currents and Circuit Analysis">
                        @error('title') 
                            <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Language Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Language <span class="text-rose-500">*</span></label>
                        <select x-model="language"
                                @change="$wire.set('language', $event.target.value)"
                                class="input-modern bg-white text-xs @error('language') border-rose-500 @enderror">
                            <option value="english">English</option>
                            <option value="amharic">Amharic</option>
                            <option value="afan_oromo">Afan Oromo</option>
                            <option value="tigrinya">Tigrinya</option>
                            <option value="somali">Somali</option>
                            <option value="afar">Afar</option>
                            <option value="other">Other</option>
                        </select>
                        @error('language') 
                            <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Rich Content (Quill Editor) -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Note Content &amp; Equations</label>
                        <div wire:ignore class="rounded-xl overflow-hidden border border-slate-200 @error('content') border-rose-500 @enderror bg-white">
                            <div id="noteEditor" style="min-height: 220px;"></div>
                        </div>
                        @error('content') 
                            <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <button @click="openModal = false" type="button" class="btn-brand-outline text-xs px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            class="btn-brand text-xs px-5 py-2.5 shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span wire:loading.remove x-text="isEdit ? 'Save Changes' : 'Create Note'"></span>
                        <span wire:loading>Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
