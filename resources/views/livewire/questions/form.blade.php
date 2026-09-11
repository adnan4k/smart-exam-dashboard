{{--
    Batch question authoring.

    Shared settings are picked once at the top, then questions are stacked
    below them and saved together. Only the question being worked on is
    expanded - the rest collapse to a summary row that says what is missing -
    which is also what keeps a single Quill instance on the page no matter how
    many questions the batch holds.
--}}
<div
    x-data="{
        openModal: @entangle('openModal'),
        confirmDiscard: false,
        editor: null,

        /* The editor wrapper carries a wire:key tied to the open question, so
           Livewire hands us a brand new node whenever the author switches
           questions and x-init remounts Quill onto it. */
        mountEditor(wrapper, wire, index, initialHtml) {
            this.destroyEditor();

            const target = wrapper.querySelector('[data-editor-target]');

            if (! target || typeof Quill === 'undefined') {
                return;
            }

            const editor = new Quill(target, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        ['link'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['formula'],
                        ['clean'],
                    ],
                },
                placeholder: 'Explain how the correct answer is reached...',
            });

            if (initialHtml) {
                editor.root.innerHTML = initialHtml;
            }

            /* Deferred on purpose: a round trip per keystroke would fight the
               editor. The value rides along with the next Livewire request,
               which is always the save, the next question, or a blur. */
            editor.on('text-change', () => {
                wire.set('drafts.' + index + '.explanation', editor.root.innerHTML, false);
            });

            /* Leaving the editor is the moment to sync for real, so the
               'ready / needs explanation' badges never lie about this question. */
            editor.root.addEventListener('blur', () => {
                wire.set('drafts.' + index + '.explanation', editor.root.innerHTML, true);
            });

            this.editor = editor;

            /* Opening a question further down a long batch should bring it into
               view rather than leaving the author scrolled somewhere else. */
            wrapper.closest('[data-draft-card]')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        },

        destroyEditor() {
            if (this.editor && typeof this.editor.destroy === 'function') {
                try { this.editor.destroy(); } catch (e) {}
            }
            this.editor = null;
        },

        /* Closing a half typed batch throws away real work, so ask first. */
        hasContent(wire) {
            try {
                return (wire.drafts || []).some((draft) => {
                    const text = (draft.text || '').trim();
                    const explanation = (draft.explanation || '').replace(/<[^>]*>/g, '').trim();
                    const answered = (draft.choices || []).some((c) => (c.text || '').trim() !== '');

                    return text !== '' || explanation !== '' || answered;
                });
            } catch (e) {
                return false;
            }
        },

        attemptClose(wire) {
            if (this.hasContent(wire)) {
                this.confirmDiscard = true;

                return;
            }

            this.close();
        },

        close() {
            this.confirmDiscard = false;
            this.destroyEditor();
            this.openModal = false;
        },
    }"
    x-init="$watch('openModal', (open) => { if (! open) { $data.confirmDiscard = false; $data.destroyEditor(); } })"
>
    @assets
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    @endassets

    <style>
        [x-cloak] { display: none !important; }

        /* Quill drops its toolbar in as a sibling of the editor container, so
           the brand styling has to live on the wrapper around both. */
        .qbf-editor {
            border: 1px solid #cbd5e1;
            border-radius: 0.625rem;
            overflow: hidden;
            background: #ffffff;
            transition: border-color 180ms cubic-bezier(0.4, 0, 0.2, 1), box-shadow 180ms cubic-bezier(0.4, 0, 0.2, 1);
        }
        .qbf-editor:focus-within {
            border-color: var(--color-brand);
            box-shadow: 0 0 0 3px rgba(88, 112, 109, 0.15);
        }
        .qbf-editor .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        .qbf-editor .ql-container.ql-snow {
            border: none;
            font-size: 0.84rem;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .qbf-editor .ql-editor {
            min-height: 140px;
        }
        .qbf-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background-color: #eef3f1;
            color: #455956;
            font-weight: 600;
            font-size: 0.7rem;
            border-radius: 9999px;
            padding: 0.15rem 0.55rem;
        }
        .qbf-chip-empty {
            background-color: #fff7ed;
            color: #b45309;
        }
    </style>

    <!-- Backdrop -->
    <div
        @keydown.escape.window="$data.attemptClose($wire)"
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
        <div class="flex min-h-full items-start justify-center p-4 sm:p-6" @click.self="$data.attemptClose($wire)">
            <div
                x-show="openModal"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-1 scale-[0.98]"
                class="relative w-full max-w-4xl my-4"
            >
                @php
                    $draftCount   = count($drafts);
                    $typeName     = optional($types->firstWhere('id', (int) $type))->name;
                    $subjectModel = $subjects->firstWhere('id', (int) $subjectId);
                    $chapterName  = optional($chapters->firstWhere('id', (int) $chapterId))->name;
                    $settingsHasError = $errors->hasAny(['type', 'subjectId', 'chapterId', 'duration', 'region', 'scienceType']);
                @endphp

                <form class="relative bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden"
                      wire:submit.prevent="saveQuestion">

                    <!-- Header -->
                    <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100 bg-slate-50/60">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#eef3f1] text-[#455956] flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-layer-group text-sm"></i>
                            </div>
                            <div>
                                <h2 class="font-bold text-slate-800 text-lg tracking-tight leading-snug">
                                    {{ $is_edit ? 'Edit Question' : 'Add Questions' }}
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $is_edit
                                        ? 'Update this question, its answer choices and explanation.'
                                        : 'Set the shared details once, then add as many questions as you need and save them together.' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @unless ($is_edit)
                                <span class="badge-subtle-{{ $readyCount === $draftCount ? 'success' : 'neutral' }} hidden sm:inline-block">
                                    {{ $readyCount }}/{{ $draftCount }} ready
                                </span>
                            @endunless
                            <button type="button" @click="$data.attemptClose($wire)" class="action-icon-btn" title="Close">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 space-y-5 max-h-[64vh] overflow-y-auto overscroll-contain">

                        {{-- Step 1: the settings every question in the batch shares --}}
                        <section class="rounded-2xl border {{ $settingsHasError ? 'border-rose-300' : 'border-slate-200' }} overflow-hidden">
                            <button type="button" wire:click="toggleSettings"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-left bg-slate-50/70 hover:bg-slate-50 transition-colors">
                                <span class="w-6 h-6 rounded-lg bg-[#eef3f1] text-[#455956] flex items-center justify-center shrink-0 text-[11px] font-bold">1</span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider">Shared Settings</span>
                                    @if ($settingsOpen)
                                        <span class="block text-[11px] text-slate-400 mt-0.5">Applied to every question you add below.</span>
                                    @else
                                        <span class="flex flex-wrap items-center gap-1.5 mt-1">
                                            <span class="qbf-chip {{ $typeName ? '' : 'qbf-chip-empty' }}">
                                                <i class="fa-solid fa-clipboard-question text-[9px]"></i>{{ $typeName ?: 'No exam type' }}
                                            </span>
                                            <span class="qbf-chip {{ $subjectModel ? '' : 'qbf-chip-empty' }}">
                                                <i class="fa-solid fa-book text-[9px]"></i>{{ $subjectModel->name ?? 'No subject' }}
                                            </span>
                                            @if ($chapterName)
                                                <span class="qbf-chip"><i class="fa-solid fa-bookmark text-[9px]"></i>{{ $chapterName }}</span>
                                            @endif
                                            @if ($duration)
                                                <span class="qbf-chip"><i class="fa-regular fa-clock text-[9px]"></i>{{ $duration }} min</span>
                                            @endif
                                            <span class="qbf-chip"><i class="fa-solid fa-flask text-[9px]"></i>{{ ucfirst($scienceType ?: 'natural') }}</span>
                                        </span>
                                    @endif
                                </span>
                                <span class="text-[11px] font-semibold text-[#58706D] shrink-0">
                                    {{ $settingsOpen ? 'Done' : 'Change' }}
                                    <i class="fa-solid fa-chevron-{{ $settingsOpen ? 'up' : 'down' }} text-[9px] ml-1"></i>
                                </span>
                            </button>

                            @if ($settingsOpen)
                                <div class="p-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="type" class="input-modern bg-white text-xs">
                                            <option value="">Select exam type</option>
                                            @foreach ($types as $examType)
                                                <option value="{{ $examType->id }}">{{ $examType->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('type') <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject <span class="text-rose-500">*</span></label>
                                        <select wire:model.live="subjectId" class="input-modern bg-white text-xs">
                                            <option value="">Select subject</option>
                                            @foreach ($subjects as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->name }} &mdash; {{ $subject->year }}{{ $subject->region ? ' — ' . $subject->region : '' }}</option>
                                            @endforeach
                                        </select>
                                        @error('subjectId') <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Chapter <span class="text-slate-300">(optional)</span></label>
                                        <select wire:model="chapterId" class="input-modern bg-white text-xs">
                                            <option value="">No chapter</option>
                                            @foreach ($chapters as $chapter)
                                                <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('chapterId') <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Duration <span class="text-slate-300">(minutes)</span></label>
                                        <input type="number" min="1" step="1" wire:model="duration" class="input-modern text-xs" placeholder="e.g. 2">
                                        <p class="text-[11px] text-slate-400 mt-1"><i class="fa-solid fa-wand-magic-sparkles mr-1"></i>Auto-filled from the subject</p>
                                        @error('duration') <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
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
                                            <option value="">All regions</option>
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
                                        @error('region') <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            @endif
                        </section>

                        {{-- Step 2: the questions themselves --}}
                        <section class="space-y-3">
                            <div class="flex items-center gap-2.5">
                                <span class="w-6 h-6 rounded-lg bg-[#eef3f1] text-[#455956] flex items-center justify-center shrink-0 text-[11px] font-bold">2</span>
                                <h3 class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                                    {{ $is_edit ? 'Question' : 'Questions (' . $draftCount . ')' }}
                                </h3>
                                <span class="flex-1 h-px bg-slate-100"></span>
                                @unless ($is_edit)
                                    <span class="text-[11px] font-semibold {{ $readyCount === $draftCount ? 'text-emerald-600' : 'text-slate-400' }}">
                                        {{ $readyCount }} of {{ $draftCount }} ready to save
                                    </span>
                                @endunless
                            </div>

                            @foreach ($drafts as $index => $draft)
                                @php
                                    $status    = $statuses[$index];
                                    $isActive  = (int) $activeIndex === (int) $index;
                                    $preview   = \App\Support\Latex::preview($draft['text'], 80);
                                    $hasErrors = $errors->hasAny([
                                        'drafts.' . $index . '.text',
                                        'drafts.' . $index . '.explanation',
                                        'drafts.' . $index . '.choices',
                                        'drafts.' . $index . '.correct',
                                        'drafts.' . $index . '.image',
                                        'drafts.' . $index . '.explanationImage',
                                    ]);
                                @endphp

                                <div wire:key="draft-{{ $draft['key'] }}" data-draft-card
                                     class="rounded-2xl border transition-colors duration-150 {{ $hasErrors ? 'border-rose-300' : ($isActive ? 'border-[#58706D]' : 'border-slate-200') }} bg-white overflow-hidden">

                                    {{-- Summary row: always visible, so a long batch stays reviewable --}}
                                    <div class="flex items-center gap-1.5 px-3 py-2.5 {{ $isActive ? 'bg-[#f0f4f2]' : 'hover:bg-slate-50' }} transition-colors">
                                        <button type="button" wire:click="openQuestion({{ $index }})"
                                                class="flex flex-1 items-center gap-3 text-left min-w-0">
                                            <span class="w-7 h-7 rounded-lg flex items-center justify-center text-[11px] font-bold shrink-0 {{ $status['ready'] ? 'bg-[#58706D] text-white' : 'bg-slate-100 text-slate-500' }}">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-xs font-semibold {{ $preview ? 'text-slate-800' : 'text-slate-400 italic' }}">
                                                    <x-math-text :value="$draft['text']" :limit="80" fallback="Untitled question" />
                                                </span>
                                                <span class="block text-[11px] font-medium mt-0.5 {{ $status['ready'] ? 'text-emerald-600' : 'text-amber-600' }}">
                                                    <i class="fa-solid {{ $status['ready'] ? 'fa-circle-check' : 'fa-circle-exclamation' }} text-[9px] mr-1"></i>{{ $status['label'] }}
                                                </span>
                                            </span>
                                            <i class="fa-solid fa-chevron-{{ $isActive ? 'up' : 'down' }} text-[10px] text-slate-400 shrink-0"></i>
                                        </button>

                                        @unless ($is_edit)
                                            <button type="button" wire:click="duplicateQuestion({{ $index }})"
                                                    class="action-icon-btn shrink-0" title="Duplicate this question">
                                                <i class="fa-regular fa-copy text-xs"></i>
                                            </button>
                                            @if ($draftCount > 1)
                                                <button type="button" wire:click="removeQuestion({{ $index }})"
                                                        class="action-icon-btn btn-danger-icon shrink-0" title="Remove this question">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            @endif
                                        @endunless
                                    </div>

                                    @if ($isActive)
                                        <div class="border-t border-slate-100 p-4 space-y-4">

                                            {{-- Prompt. Maths goes inline, wrapped in $…$, and is
                                                 typeset live in the preview underneath. --}}
                                            <div x-data="{ hasMath: {{ str_contains((string) $draft['text'], '$') ? 'true' : 'false' }} }">
                                                <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Question Prompt <span class="text-rose-500">*</span></label>
                                                    <div class="flex items-center gap-1">
                                                        <button type="button" class="math-btn" title="Insert inline formula"
                                                                @click="MathText.insert($refs.prompt, '$@$')"><i class="fa-solid fa-dollar-sign text-[9px]"></i> Math</button>
                                                        <button type="button" class="math-btn" title="Fraction"
                                                                @click="MathText.insert($refs.prompt, '$\\frac{@}{b}$')">a/b</button>
                                                        <button type="button" class="math-btn" title="Square root"
                                                                @click="MathText.insert($refs.prompt, '$\\sqrt{@}$')">&radic;x</button>
                                                        <button type="button" class="math-btn" title="Power"
                                                                @click="MathText.insert($refs.prompt, '$@^{2}$')">x&sup2;</button>
                                                        <button type="button" class="math-btn" title="Subscript"
                                                                @click="MathText.insert($refs.prompt, '$@_{n}$')">x&#8345;</button>
                                                        <span class="text-[11px] font-medium text-slate-300 ml-1"
                                                              x-text="(($wire.drafts[{{ $index }}] || {}).text || '').length + ' characters'"></span>
                                                    </div>
                                                </div>
                                                <textarea wire:model.blur="drafts.{{ $index }}.text" rows="3" x-ref="prompt"
                                                          @input="hasMath = $event.target.value.includes('$'); MathText.preview($refs.promptPreview, $event.target.value)"
                                                          placeholder="Type the full question text here, with any maths inline: What is $\sqrt{x^2+y^2}$ when $x=3$?"
                                                          class="input-modern text-xs resize-none min-h-[84px]"></textarea>
                                                <div class="mt-1.5" x-show="hasMath" x-cloak>
                                                    <span class="math-preview"><span class="math-preview-label">Preview</span><span data-math x-ref="promptPreview" data-math-src="{{ $draft['text'] }}">{{ $draft['text'] }}</span></span>
                                                </div>
                                                <p class="text-[11px] text-slate-400 mt-1">
                                                    <i class="fa-solid fa-square-root-variable mr-1"></i>Wrap maths in <code class="font-mono text-[10px] text-slate-500">$…$</code> to write it inside a sentence &mdash; use <code class="font-mono text-[10px] text-slate-500">\$</code> for a literal dollar sign.
                                                </p>
                                                @error('drafts.' . $index . '.text')
                                                    <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                <div x-data="{ hasFormula: {{ trim((string) $draft['formula']) !== '' ? 'true' : 'false' }} }">
                                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Display Formula <span class="text-slate-300">(optional)</span></label>
                                                    <input type="text" wire:model.blur="drafts.{{ $index }}.formula" x-ref="formula"
                                                           @input="hasFormula = $event.target.value.trim() !== ''; MathText.preview($refs.formulaPreview, '$' + $event.target.value + '$')"
                                                           class="input-modern font-mono text-xs" placeholder="e.g. \sqrt{x^2 + y^2}">
                                                    <div class="mt-1.5" x-show="hasFormula" x-cloak>
                                                        <span class="math-preview">@php $formulaPreview = trim((string) $draft['formula']) !== '' ? '$' . $draft['formula'] . '$' : ''; @endphp<span data-math x-ref="formulaPreview" data-math-src="{{ $formulaPreview }}">{{ $formulaPreview }}</span></span>
                                                    </div>
                                                    <p class="text-[11px] text-slate-400 mt-1">Stands on its own line, under the question. No <code class="font-mono text-[10px] text-slate-500">$</code> needed here.</p>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Question Image <span class="text-slate-300">(optional)</span></label>
                                                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50/50 px-3 py-2 transition-colors duration-150 hover:border-[#58706D]/40 hover:bg-[#f0f4f2]/40">
                                                        <input type="file" wire:model="drafts.{{ $index }}.image" accept="image/*"
                                                               class="block w-full cursor-pointer text-xs text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-[#eef3f1] file:px-4 file:py-2 file:text-xs file:font-semibold file:text-[#455956] hover:file:bg-[#dfeae6]">
                                                    </div>
                                                    <div wire:loading wire:target="drafts.{{ $index }}.image" class="text-[11px] text-slate-400 mt-1">
                                                        <i class="fa-solid fa-circle-notch fa-spin"></i> Uploading...
                                                    </div>
                                                    @if (is_object($draft['image'] ?? null))
                                                        <div class="mt-2 relative inline-block">
                                                            <img src="{{ $draft['image']->temporaryUrl() }}" alt="Question image preview"
                                                                 class="h-20 rounded-lg border border-slate-200 object-cover shadow-sm">
                                                            <button type="button" wire:click="clearImage({{ $index }}, 'image')" title="Remove image"
                                                                    class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center hover:bg-rose-600 shadow">
                                                                <i class="fa-solid fa-xmark text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    @elseif (! empty($draft['imagePath']))
                                                        <div class="mt-2 relative inline-block">
                                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($draft['imagePath']) }}" alt="Current question image"
                                                                 class="h-20 rounded-lg border border-slate-200 object-cover shadow-sm">
                                                            <button type="button" wire:click="clearImage({{ $index }}, 'imagePath')" title="Remove image"
                                                                    class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center hover:bg-rose-600 shadow">
                                                                <i class="fa-solid fa-xmark text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    @error('drafts.' . $index . '.image')
                                                        <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Choices --}}
                                            <div class="border-t border-slate-100 pt-4">
                                                <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Answer Choices <span class="text-rose-500">*</span></label>
                                                    <span class="flex-1 h-px bg-slate-100 min-w-8"></span>
                                                    @if (count($draft['choices']) < $maxChoices)
                                                        <button type="button" wire:click="addChoice({{ $index }})" class="btn-brand-outline text-xs px-3 py-1.5">
                                                            <i class="fa-solid fa-plus text-[10px]"></i> Add Choice
                                                        </button>
                                                    @endif
                                                </div>
                                                <p class="text-[11px] text-slate-400 mb-3">
                                                    <strong class="text-slate-600">Click the row of the correct answer</strong> to mark it. Leave any unused rows blank &mdash; they are dropped on save. Students always see the choices shuffled.
                                                </p>

                                                <div class="space-y-2">
                                                    @foreach ($draft['choices'] as $choiceIndex => $choice)
                                                        @php $isCorrect = $draft['correct'] !== null && $draft['correct'] !== '' && (int) $draft['correct'] === (int) $choiceIndex; @endphp

                                                        <div wire:key="choice-{{ $draft['key'] }}-{{ $choiceIndex }}"
                                                             wire:click="markCorrect({{ $index }}, {{ $choiceIndex }})"
                                                             class="flex items-start gap-2 rounded-xl border p-2 cursor-pointer transition-colors duration-150 {{ $isCorrect ? 'border-[#58706D] bg-[#f0f4f2]' : 'border-slate-200 bg-slate-50/60 hover:border-slate-300' }}">

                                                            {{-- A real radio keeps the control keyboard reachable; the
                                                                 row's wire:click is what actually records the answer. --}}
                                                            <label class="relative flex items-center justify-center shrink-0 w-7 h-7 mt-0.5 rounded-full text-[11px] font-bold cursor-pointer select-none border-2 transition-colors duration-150 {{ $isCorrect ? 'border-[#58706D] bg-[#58706D] text-white' : 'border-slate-300 bg-white text-slate-500' }}"
                                                                   title="Mark choice {{ chr(65 + $choiceIndex) }} as the correct answer">
                                                                <input type="radio" name="correct-{{ $draft['key'] }}" value="{{ $choiceIndex }}"
                                                                       {{ $isCorrect ? 'checked' : '' }}
                                                                       class="absolute inset-0 w-full h-full m-0 opacity-0 cursor-pointer">
                                                                {{ chr(65 + $choiceIndex) }}
                                                            </label>

                                                            {{-- Answers take inline maths too, so a choice can simply
                                                                 be "$\frac{1}{2}$" without a second field. --}}
                                                            <div class="flex-1 min-w-0"
                                                                 x-data="{ hasMath: {{ str_contains((string) $choice['text'], '$') ? 'true' : 'false' }} }">
                                                                <div class="flex items-center gap-1.5">
                                                                    <input type="text" wire:model.blur="drafts.{{ $index }}.choices.{{ $choiceIndex }}.text" @click.stop
                                                                           x-ref="choice"
                                                                           @input="hasMath = $event.target.value.includes('$'); MathText.preview($refs.choicePreview, $event.target.value)"
                                                                           class="flex-1 min-w-0 text-xs py-2 px-3 border border-slate-200 rounded-lg focus:border-[#58706D] focus:outline-none bg-white"
                                                                           placeholder="Choice {{ chr(65 + $choiceIndex) }} text...">
                                                                    <button type="button" class="math-btn shrink-0" title="Insert inline formula"
                                                                            @click.stop="MathText.insert($refs.choice, '$@$')">
                                                                        <i class="fa-solid fa-square-root-variable text-[9px]"></i>
                                                                    </button>
                                                                </div>
                                                                <div class="mt-1" x-show="hasMath" x-cloak>
                                                                    <span class="math-preview"><span data-math x-ref="choicePreview" data-math-src="{{ $choice['text'] }}">{{ $choice['text'] }}</span></span>
                                                                </div>
                                                            </div>

                                                            <input type="text" wire:model="drafts.{{ $index }}.choices.{{ $choiceIndex }}.formula" @click.stop
                                                                   class="w-28 sm:w-36 shrink-0 text-xs py-2 px-3 border border-slate-200 rounded-lg focus:border-[#58706D] focus:outline-none bg-white font-mono text-[11px]"
                                                                   placeholder="Display formula">

                                                            <span class="w-16 text-right shrink-0 mt-2 text-[10px] font-bold uppercase tracking-wider text-[#58706D]">
                                                                @if ($isCorrect)
                                                                    <i class="fa-solid fa-check"></i> Correct
                                                                @endif
                                                            </span>

                                                            @if (count($draft['choices']) > $minChoices)
                                                                <button type="button" wire:click.stop="removeChoice({{ $index }}, {{ $choiceIndex }})"
                                                                        class="action-icon-btn btn-danger-icon shrink-0" title="Remove this choice">
                                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>

                                                @error('drafts.' . $index . '.choices')
                                                    <span class="text-rose-500 text-[11px] font-medium mt-1.5 block">{{ $message }}</span>
                                                @enderror
                                                @error('drafts.' . $index . '.correct')
                                                    <span class="text-rose-500 text-[11px] font-medium mt-1.5 block">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Explanation --}}
                                            <div class="border-t border-slate-100 pt-4 space-y-4">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Explanation <span class="text-rose-500">*</span></label>
                                                    <div wire:key="editor-{{ $draft['key'] }}" wire:ignore
                                                         x-init="$data.mountEditor($el, $wire, {{ $index }}, @js($draft['explanation']))">
                                                        <div class="qbf-editor">
                                                            <div data-editor-target></div>
                                                        </div>
                                                    </div>
                                                    @error('drafts.' . $index . '.explanation')
                                                        <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Explanation Image <span class="text-slate-300">(optional)</span></label>
                                                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50/50 px-3 py-2 transition-colors duration-150 hover:border-[#58706D]/40 hover:bg-[#f0f4f2]/40">
                                                        <input type="file" wire:model="drafts.{{ $index }}.explanationImage" accept="image/*"
                                                               class="block w-full cursor-pointer text-xs text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-[#eef3f1] file:px-4 file:py-2 file:text-xs file:font-semibold file:text-[#455956] hover:file:bg-[#dfeae6]">
                                                    </div>
                                                    <div wire:loading wire:target="drafts.{{ $index }}.explanationImage" class="text-[11px] text-slate-400 mt-1">
                                                        <i class="fa-solid fa-circle-notch fa-spin"></i> Uploading...
                                                    </div>
                                                    @if (is_object($draft['explanationImage'] ?? null))
                                                        <div class="mt-2 relative inline-block">
                                                            <img src="{{ $draft['explanationImage']->temporaryUrl() }}" alt="Explanation image preview"
                                                                 class="h-20 rounded-lg border border-slate-200 object-cover shadow-sm">
                                                            <button type="button" wire:click="clearImage({{ $index }}, 'explanationImage')" title="Remove image"
                                                                    class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center hover:bg-rose-600 shadow">
                                                                <i class="fa-solid fa-xmark text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    @elseif (! empty($draft['explanationPath']))
                                                        <div class="mt-2 relative inline-block">
                                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($draft['explanationPath']) }}" alt="Current explanation image"
                                                                 class="h-20 rounded-lg border border-slate-200 object-cover shadow-sm">
                                                            <button type="button" wire:click="clearImage({{ $index }}, 'explanationPath')" title="Remove image"
                                                                    class="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center hover:bg-rose-600 shadow">
                                                                <i class="fa-solid fa-xmark text-[10px]"></i>
                                                            </button>
                                                        </div>
                                                    @endif
                                                    @error('drafts.' . $index . '.explanationImage')
                                                        <span class="text-rose-500 text-[11px] font-medium mt-1 block">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @unless ($is_edit)
                                @if ($draftCount < $maxQuestions)
                                    <button type="button" wire:click="addQuestion"
                                            class="w-full border-2 border-dashed border-slate-300 rounded-2xl py-3.5 text-xs font-bold text-slate-500 hover:border-[#58706D] hover:text-[#58706D] transition-colors">
                                        <i class="fa-solid fa-plus text-[10px] mr-1"></i> Add Another Question
                                    </button>
                                @else
                                    <p class="text-[11px] text-slate-400 text-center">
                                        That is the maximum of {{ $maxQuestions }} questions per batch. Save these, then start another batch.
                                    </p>
                                @endif
                            @endunless
                        </section>
                    </div>

                    <!-- Footer -->
                    <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                        <div x-show="confirmDiscard" x-cloak class="flex flex-wrap items-center justify-between gap-3">
                            <span class="text-xs font-semibold text-rose-600">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                Close without saving? Everything typed here will be lost.
                            </span>
                            <div class="flex items-center gap-2.5">
                                <button type="button" @click="confirmDiscard = false" class="btn-brand-outline text-xs">Keep editing</button>
                                <button type="button" @click="$data.close()" class="btn-brand text-xs" style="background-color:#e11d48 !important;border-color:#e11d48 !important;">Discard</button>
                            </div>
                        </div>

                        <div x-show="! confirmDiscard" class="flex flex-wrap items-center justify-between gap-3">
                            <span class="text-[11px] text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info"></i>
                                <span>Fields marked <span class="text-rose-500 font-semibold">*</span> are required</span>
                            </span>
                            <div class="flex items-center gap-2.5">
                                <button type="button" @click="$data.attemptClose($wire)" class="btn-brand-outline text-xs">Cancel</button>
                                <button type="submit" wire:loading.attr="disabled" wire:target="saveQuestion" class="btn-brand shadow-sm">
                                    <span wire:loading.remove wire:target="saveQuestion">
                                        <i class="fa-solid fa-check text-xs mr-1"></i>
                                        {{ $is_edit ? 'Save Changes' : ($draftCount === 1 ? 'Save Question' : 'Save ' . $draftCount . ' Questions') }}
                                    </span>
                                    <span wire:loading wire:target="saveQuestion">
                                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i> Saving...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
