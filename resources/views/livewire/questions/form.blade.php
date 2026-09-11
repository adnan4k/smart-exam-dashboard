<div x-data="{
        openModal: @entangle('openModal'),
        initQuill() {
            // First, completely destroy any existing Quill instance
            if (window.explanationEditor && typeof window.explanationEditor.destroy === 'function') {
                try {
                    window.explanationEditor.destroy();
                } catch (e) {
                    console.log('Error destroying existing editor:', e);
                }
            }
            window.explanationEditor = null;

            // Remove any existing Quill elements from the DOM
            const existingQuillElements = document.querySelectorAll('.ql-editor, .ql-toolbar');
            existingQuillElements.forEach(el => {
                if (el.closest('#explanationEditor')) {
                    el.remove();
                }
            });

            // Recreate the editor container
            const container = document.getElementById('explanationEditor');
            if (!container) {
                console.error('explanationEditor container not found!');
                return false;
            }

            // Clear the container and recreate the editor div
            container.innerHTML = '';

            try {
                window.explanationEditor = new Quill('#explanationEditor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            ['link'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'color': [] }, { 'background': [] }], // Color and background color options
                            ['formula'], // Add formula support
                            ['clean']
                        ]
                    },
                    placeholder: 'Enter detailed explanation...'
                });

                // Set content if editing (explanation exists)
                const explanation = window.Livewire.find('{{ $this->getId() }}').get('explanation');
                if (explanation && explanation.trim() !== '') {
                    // Use setTimeout to ensure the editor is fully ready
                    setTimeout(() => {
                        if (window.explanationEditor && window.explanationEditor.root) {
                            window.explanationEditor.root.innerHTML = explanation;
                        }
                    }, 50);
                }

                window.explanationEditor.on('text-change', function() {
                    window.Livewire.find('{{ $this->getId() }}').set('explanation', window.explanationEditor.root.innerHTML);
                });

                return true;
            } catch (error) {
                console.error('Error initializing explanationEditor:', error);
                return false;
            }
        },

        // Retry initialization with multiple attempts
        initQuillWithRetry() {
            let attempts = 0;
            const maxAttempts = 5;

            const tryInit = () => {
                attempts++;
                if (this.initQuill()) {
                    return;
                }
                if (attempts < maxAttempts) {
                    setTimeout(tryInit, attempts * 100);
                }
            };
            tryInit();
        },

        // Clear Quill editor content
        clearQuill() {
            if (window.explanationEditor && typeof window.explanationEditor.setText === 'function') {
                window.explanationEditor.setText('');
            }
        },

        // Clean up Quill editor completely
        cleanupQuill() {
            if (window.explanationEditor && typeof window.explanationEditor.destroy === 'function') {
                try {
                    window.explanationEditor.destroy();
                } catch (e) {
                    console.log('Error destroying editor during cleanup:', e);
                }
            }
            window.explanationEditor = null;
        }
    }"
    x-init="
        const q = $data;
        $watch('openModal', value => {
            if(value) {
                // Clear the editor content first if it's a new question (not editing)
                if (!window.Livewire.find('{{ $this->getId() }}').get('is_edit')) {
                    setTimeout(() => {
                        q.clearQuill();
                    }, 50);
                }
                setTimeout(() => initQuillWithRetry(), 100);
            } else {
                // Clean up when modal is closed
                setTimeout(() => {
                    q.cleanupQuill();
                }, 100);
            }
        })"
>
    @assets
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    @endassets

    <style>
        [x-cloak] { display: none !important; }

        /* Keep the Quill editor on-brand with the rest of the form */
        #explanationEditorWrap {
            border: 1px solid #cbd5e1;
            border-radius: 0.625rem;
            overflow: hidden;
            background: #ffffff;
            transition: border-color 180ms cubic-bezier(0.4, 0, 0.2, 1), box-shadow 180ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        #explanationEditorWrap:focus-within {
            border-color: var(--color-brand);
            box-shadow: 0 0 0 3px rgba(88, 112, 109, 0.15);
        }
        #explanationEditorWrap .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        #explanationEditor.ql-container.ql-snow {
            border: none;
            font-size: 0.84rem;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        #explanationEditor .ql-editor {
            min-height: 120px;
        }
    </style>

    <!-- Backdrop -->
    <div
        @click.away="openModal = false"
        @keydown.escape.window="openModal = false"
        x-cloak x-show="openModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[1100] overflow-y-auto bg-slate-900/50 backdrop-blur-[2px]"
        role="dialog"
        aria-modal="true"
        :aria-hidden="!openModal"
    >
        <div class="flex min-h-full items-start justify-center p-4 sm:p-6">
            <div
                x-data="{ isEdit: @entangle('is_edit') }"
                x-show="openModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-1 scale-[0.98]"
                class="relative w-full max-w-3xl my-4"
            >
                <form class="relative bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden" wire:submit.prevent="saveQuestion">
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100 bg-slate-50/60">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#eef3f1] text-[#455956] flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-circle-question text-sm"></i>
                            </div>
                            <div>
                                <h2 class="font-bold text-slate-800 text-lg tracking-tight leading-snug"
                                    x-text="isEdit ? 'Edit Question' : 'Create Question'"></h2>
                                <p class="text-xs text-slate-400 mt-0.5"
                                    x-text="isEdit ? 'Update this question, its answer choices, and explanation.' : 'Add a new question to the bank with its answer choices and explanation.'"></p>
                            </div>
                        </div>
                        <button type="button" @click="openModal = false" class="action-icon-btn" title="Close modal">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 space-y-6 max-h-[62vh] overflow-y-auto overscroll-contain">

                        {{-- Section: Question Setup --}}
                        <section class="space-y-4">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-lg bg-[#eef3f1] text-[#455956] flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-sliders text-[10px]"></i>
                                </span>
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Question Setup</h3>
                                <span class="flex-1 h-px bg-slate-100"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type <span class="text-rose-500">*</span></label>
                                    <select wire:model="type" wire:change="loadSubjects()" class="input-modern bg-white text-xs">
                                        <option value="">Select Exam Type</option>
                                        @foreach ($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                    <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject <span class="text-rose-500">*</span></label>
                                    <select wire:model="subjectId" wire:change="loadChapters()" class="input-modern bg-white text-xs">
                                        <option value="">Select Subject</option>
                                        @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }} - {{ $subject->year }} - {{ $subject->region }}</option>
                                        @endforeach
                                    </select>
                                    @error('subjectId')
                                    <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Chapter <span class="text-slate-300">(optional)</span></label>
                                    <select wire:model="chapterId" class="input-modern bg-white text-xs">
                                        <option value="">Select Chapter</option>
                                        @foreach ($chapters as $chapter)
                                        <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Duration <span class="text-slate-300">(minutes, optional)</span></label>
                                    <input type="number" min="1" step="1" wire:model="duration" class="input-modern text-xs" placeholder="e.g. 2">
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        <i class="fa-solid fa-wand-magic-sparkles mr-1"></i>Auto-filled from the selected subject
                                    </p>
                                    @error('duration')
                                    <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Science Type</label>
                                    <div class="flex rounded-lg border border-slate-200 bg-slate-50 p-1 gap-1">
                                        <button type="button" wire:click="$set('scienceType', 'natural')"
                                            class="flex-1 rounded-md px-2.5 py-1.5 text-xs font-semibold transition-colors duration-150 {{ $scienceType === 'natural' ? 'bg-white text-[#455956] shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700 border border-transparent' }}">
                                            <i class="fa-solid fa-flask mr-1.5"></i>Natural
                                        </button>
                                        <button type="button" wire:click="$set('scienceType', 'social')"
                                            class="flex-1 rounded-md px-2.5 py-1.5 text-xs font-semibold transition-colors duration-150 {{ $scienceType === 'social' ? 'bg-white text-[#455956] shadow-sm border border-slate-200' : 'text-slate-500 hover:text-slate-700 border border-transparent' }}">
                                            <i class="fa-solid fa-globe mr-1.5"></i>Social
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Region <span class="text-slate-300">(optional)</span></label>
                                    <select wire:model="region" class="input-modern bg-white text-xs">
                                        <option value="">Select Region</option>
                                        <option value="addis_ababa">Addis Ababa</option>
                                        <option value="afar">Afar</option>
                                        <option value="amhara">Amhara</option>
                                        <option value="benishangul_gumuz">Benishangul-Gumuz</option>
                                        <option value="central_ethiopia">Central Ethiopia</option>
                                        <option value="dire_dawa">Dire Dawa</option>
                                        <option value="gambela">Gambela</option>
                                        <option value="harari">Harari</option>
                                        <option value="oromia">Oromia</option>
                                        <option value="sidama">Sidama</option>
                                        <option value="south_ethiopia">South Ethiopia</option>
                                        <option value="tigray">Tigray</option>
                                    </select>
                                    @error('region')
                                    <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        {{-- Section: Question Content --}}
                        <section class="space-y-4">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-lg bg-[#eef3f1] text-[#455956] flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-file-pen text-[10px]"></i>
                                </span>
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Question Content</h3>
                                <span class="flex-1 h-px bg-slate-100"></span>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Question Text <span class="text-rose-500">*</span></label>
                                    <span class="text-[11px] font-medium text-slate-300" x-text="($wire.questionText || '').length + ' characters'"></span>
                                </div>
                                <textarea wire:model="questionText" placeholder="Type the full question text here..." rows="4"
                                    class="input-modern text-xs resize-none min-h-[100px]"></textarea>
                                @error('questionText')
                                <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">LaTeX Formula <span class="text-slate-300">(optional)</span></label>
                                    <input type="text" wire:model="formula" class="input-modern font-mono text-xs"
                                        placeholder="e.g. \sqrt{x^2 + y^2}">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Question Image <span class="text-slate-300">(optional)</span></label>
                                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50/50 px-3 py-2 transition-colors duration-150 hover:border-[#58706D]/40 hover:bg-[#f0f4f2]/40">
                                        <input type="file" wire:model="questionImage" accept="image/*"
                                            class="block w-full cursor-pointer text-xs text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-[#eef3f1] file:px-4 file:py-2 file:text-xs file:font-semibold file:text-[#455956] hover:file:bg-[#dfeae6]">
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">PNG or JPG, up to 2 MB.</p>
                                    @if ($questionImage)
                                    <div class="mt-2 relative inline-block">
                                        <img src="{{ $questionImage->temporaryUrl() }}" alt="Question preview"
                                            class="h-24 rounded-lg border border-slate-200 object-cover shadow-sm">
                                        <button type="button" wire:click="$set('questionImage', null)" title="Remove image"
                                            class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center hover:bg-rose-600 shadow">
                                            <i class="fa-solid fa-xmark text-[10px]"></i>
                                        </button>
                                    </div>
                                    @endif
                                    @error('questionImage')
                                    <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        {{-- Section: Answer Choices --}}
                        <section class="space-y-3">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="w-6 h-6 rounded-lg bg-[#eef3f1] text-[#455956] flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-list-check text-[10px]"></i>
                                </span>
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Answer Choices <span class="text-rose-500">*</span></h3>
                                <span class="flex-1 h-px bg-slate-100 min-w-8"></span>
                                @if (count($choices) < 6)
                                <button type="button" wire:click="addChoice" class="btn-brand-outline text-xs px-3 py-1.5">
                                    <i class="fa-solid fa-plus text-[10px]"></i> Add Choice
                                </button>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-400 -mt-1.5">Give the question 2&ndash;6 options and mark exactly one as correct. Students see the choices shuffled.</p>

                            <div class="space-y-2.5">
                                @foreach ($choices as $index => $choice)
                                @php $isCorrect = $correctChoiceId !== null && (int) $correctChoiceId === (int) $index; @endphp
                                <div wire:key="choice-{{ $index }}" class="rounded-xl border p-3 transition-all duration-150 {{ $isCorrect ? 'border-[#58706D] bg-[#f0f4f2] shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300' }}">
                                    <div class="flex items-start gap-3">
                                        <span class="w-7 h-7 rounded-lg flex items-center justify-center text-[11px] font-bold shrink-0 transition-colors duration-150 {{ $isCorrect ? 'bg-[#58706D] text-white' : 'bg-slate-100 text-slate-500' }}">
                                            {{ chr(65 + $index) }}
                                        </span>
                                        <div class="flex-1">
                                            <textarea wire:model="choices.{{ $index }}.text" rows="2"
                                                placeholder="Choice {{ chr(65 + $index) }} text..."
                                                class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:border-[#58706D] focus:outline-none bg-white resize-none"></textarea>
                                            @error('choices.'.$index.'.text')
                                            <span class="text-rose-500 text-[11px] font-medium mt-1 block">Choice text is required</span>
                                            @enderror
                                        </div>
                                        <label class="flex flex-col items-center gap-1 shrink-0 cursor-pointer select-none" title="Mark as the correct answer">
                                            <input type="radio" name="correctChoiceId" value="{{ $index }}"
                                                wire:click="$set('correctChoiceId', {{ $index }})"
                                                {{ $isCorrect ? 'checked' : '' }}
                                                class="w-4 h-4 accent-[#58706D]">
                                            <span class="text-[10px] font-semibold {{ $isCorrect ? 'text-[#455956]' : 'text-slate-400' }}">
                                                <i class="fa-solid {{ $isCorrect ? 'fa-circle-check' : 'fa-circle' }} mr-0.5"></i>Correct
                                            </span>
                                        </label>
                                        @if (count($choices) > 2)
                                        <button type="button" wire:click="removeChoice({{ $index }})" class="action-icon-btn btn-danger-icon shrink-0" title="Remove choice">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('correctChoiceId')
                            <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                            @enderror
                        </section>

                        {{-- Section: Explanation --}}
                        <section class="space-y-4">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-lg bg-[#eef3f1] text-[#455956] flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-lightbulb text-[10px]"></i>
                                </span>
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Solution &amp; Explanation</h3>
                                <span class="flex-1 h-px bg-slate-100"></span>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Explanation <span class="text-rose-500">*</span></label>
                                <div wire:ignore>
                                    <div id="explanationEditorWrap">
                                        <div id="explanationEditor" style="height: 180px;"></div>
                                    </div>
                                </div>
                                @error('explanation')
                                <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Explanation Image <span class="text-slate-300">(optional)</span></label>
                                <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50/50 px-3 py-2 transition-colors duration-150 hover:border-[#58706D]/40 hover:bg-[#f0f4f2]/40">
                                    <input type="file" wire:model="explanationImage" accept="image/*"
                                        class="block w-full cursor-pointer text-xs text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-[#eef3f1] file:px-4 file:py-2 file:text-xs file:font-semibold file:text-[#455956] hover:file:bg-[#dfeae6]">
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">PNG or JPG, up to 2 MB.</p>
                                @if ($explanationImage)
                                <div class="mt-2 relative inline-block">
                                    <img src="{{ $explanationImage->temporaryUrl() }}" alt="Explanation preview"
                                        class="h-24 rounded-lg border border-slate-200 object-cover shadow-sm">
                                    <button type="button" wire:click="$set('explanationImage', null)" title="Remove image"
                                        class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center hover:bg-rose-600 shadow">
                                        <i class="fa-solid fa-xmark text-[10px]"></i>
                                    </button>
                                </div>
                                @endif
                                @error('explanationImage')
                                <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </section>
                    </div>

                    <!-- Footer -->
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                        <span class="text-[11px] text-slate-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Fields marked <span class="text-rose-500 font-semibold">*</span> are required</span>
                        </span>
                        <div class="flex items-center gap-2.5">
                            <button type="button" @click="openModal = false" class="btn-brand-outline text-xs">Cancel</button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveQuestion" class="btn-brand shadow-sm">
                                <span wire:loading.remove wire:target="saveQuestion" x-text="isEdit ? 'Save Changes' : 'Create Question'"></span>
                                <span wire:loading wire:target="saveQuestion">
                                    <i class="fa-solid fa-circle-notch fa-spin text-xs"></i> Saving...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
