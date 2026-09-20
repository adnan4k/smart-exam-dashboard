<x-layouts.app>
    @include('contests.partials.theme')

    <div class="ct">
        <div class="row">
            <div class="col-lg-10 col-12 mx-auto">
                @include('contests.partials.flash')

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 mb-4">
                        <p class="text-xs font-bold text-rose-700 mb-1">Please fix the following:</p>
                        <ul class="text-xs text-rose-600 list-disc list-inside space-y-0.5 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" enctype="multipart/form-data"
                      action="{{ route('contest-questions.store') }}"
                      x-data="bulkQuestions()" novalidate @submit="submit">
                    @csrf

                    <!-- Page header -->
                    <div class="flex items-center gap-2 mb-4">
                        <a href="{{ route('contest-questions.index') }}" class="text-xs font-semibold text-[#58706D] hover:underline flex items-center gap-1">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i> Contest Bank
                        </a>
                        <span class="text-slate-300">&bull;</span>
                        <span class="badge-subtle-brand">Batch Entry</span>
                    </div>
                    <h5 class="font-bold text-slate-800 text-lg tracking-tight mb-1">Add Questions to Contest Bank</h5>
                    <p class="text-xs text-slate-400 mb-6">
                        Set the shared details once, then add as many questions as you need and save them all together.
                        Contest questions remain hidden from students until the competition ends.
                    </p>

                    <!-- Shared settings: chosen once for the whole batch -->
                    <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6">
                        <div class="border-b border-slate-100 p-5">
                            <h6 class="font-bold text-slate-800 text-sm tracking-tight mb-1">Shared Settings</h6>
                            <p class="text-[11px] text-slate-400 mb-0">These apply to every question in this batch &mdash; pick them once.</p>
                        </div>

                        <div class="p-5">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type <span class="text-rose-500">*</span></label>
                                    <select name="type_id" class="input-modern bg-white text-xs" required>
                                        <option value="">Choose…</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}" @selected(old('type_id') == $type->id)>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('type_id') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject <span class="text-rose-500">*</span></label>
                                    <select name="subject_id" class="input-modern bg-white text-xs" required>
                                        <option value="">Choose…</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('subject_id') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Chapter</label>
                                    <select name="chapter_id" class="input-modern bg-white text-xs">
                                        <option value="">— Optional —</option>
                                        @foreach ($chapters as $chapter)
                                            <option value="{{ $chapter->id }}" @selected(old('chapter_id') == $chapter->id)>{{ $chapter->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('chapter_id') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Difficulty <span class="text-rose-500">*</span></label>
                                    <select name="difficulty" class="input-modern bg-white text-xs" required>
                                        @foreach (['easy', 'medium', 'hard'] as $level)
                                            <option value="{{ $level }}" @selected(old('difficulty', 'medium') === $level)>{{ ucfirst($level) }}</option>
                                        @endforeach
                                    </select>
                                    @error('difficulty') <p class="mt-1 text-xs text-rose-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- One card per question -->
                    <template x-for="(q, qi) in questions" :key="q.uid">
                        {{-- Own x-data so x-ref (prompt, previews) stays scoped to this card. --}}
                        <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6"
                             x-data="{ hasMath: false }">
                            <div class="border-b border-slate-100 p-4 flex items-center justify-between">
                                <span class="badge-subtle-brand" x-text="'Question ' + (qi + 1)"></span>
                                <button type="button" class="text-xs font-semibold text-rose-500 hover:text-rose-700 flex items-center gap-1 transition"
                                        @click="removeQuestion(q)" x-show="questions.length > 1">
                                    <i class="fa-solid fa-trash-can text-[10px]"></i> Remove
                                </button>
                            </div>

                            <div class="p-5 space-y-4">
                                <div>
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
                                        </div>
                                    </div>
                                    <textarea :name="`questions[${q.uid}][question_text]`" rows="2" required class="input-modern"
                                              x-ref="prompt" x-model="q.text"
                                              @input="hasMath = ($event.target.value || '').includes('$'); MathText.preview($refs.promptPreview, $event.target.value)"
                                              placeholder="Type the full question text here, with any maths inline: What is $\sqrt{x^2+y^2}$ when $x=3$?"></textarea>
                                    <div class="mt-1.5" x-show="hasMath" x-cloak>
                                        <span class="math-preview"><span class="math-preview-label">Preview</span><span data-math x-ref="promptPreview"></span></span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        <i class="fa-solid fa-square-root-variable mr-1"></i>Wrap maths in <code class="font-mono text-[10px] text-slate-500">$…$</code> to write it inside a sentence &mdash; use <code class="font-mono text-[10px] text-slate-500">\$</code> for a literal dollar sign.
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">LaTeX Formula (Optional)</label>
                                        <input type="text" :name="`questions[${q.uid}][formula]`" class="input-modern font-mono text-xs"
                                               placeholder="e.g. \sqrt{x^2 + y^2}" x-model="q.formula">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Question Image (Optional)</label>
                                        <input type="file" :name="`questions[${q.uid}][question_image]`" accept="image/*" class="input-modern text-xs">
                                    </div>
                                </div>

                                {{-- Answer Choices --}}
                                <div class="border-t border-slate-100 pt-4">
                                    <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Answer Choices <span class="text-rose-500">*</span></label>
                                        <span class="flex-1 h-px bg-slate-100 min-w-8"></span>
                                        <button type="button" class="btn-brand-outline text-xs px-3 py-1.5"
                                                @click="addChoice(q)" x-show="q.choices.length < maxChoices">
                                            <i class="fa-solid fa-plus text-[10px]"></i> Add Choice
                                        </button>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mb-3"><strong class="text-slate-600">Click the row of the correct answer</strong> (or its lettered circle) &mdash; the circle fills in and the row highlights. Two choices is enough; remove any you do not need, or leave them blank and they are dropped on save. Students see these shuffled, and never receive the answer &mdash; it is only used server-side to score the contest.</p>

                                    <div class="space-y-2">
                                        <template x-for="(c, ci) in q.choices" :key="c.uid">
                                            {{-- The whole row is the hit area. The two text fields stop the
                                                 click so typing an option never marks it correct by accident.
                                                 Style overrides are objects, not strings: Alpine replaces the
                                                 whole style attribute when given a string, which wiped the
                                                 static sizing and left nothing to click. --}}
                                            <div class="flex items-start gap-2 rounded-xl"
                                                 @click="q.correct = ci"
                                                 style="padding: 8px; border: 1px solid #e2e8f0; background-color: #f8fafc; border-radius: 12px; cursor: pointer;"
                                                 :style="q.correct === ci ? { borderColor: '#58706D', backgroundColor: '#EDF2F1' } : {}">
                                                <div class="flex items-center gap-1.5" style="padding: 0 8px;">
                                                    <label class="flex items-center justify-center shrink-0 rounded-full font-bold"
                                                           style="position: relative; width: 30px; height: 30px; border: 2px solid #94a3b8; background-color: #ffffff; color: #64748b; font-size: 12px; cursor: pointer; user-select: none;"
                                                           :style="q.correct === ci ? { borderColor: '#58706D', backgroundColor: '#58706D', color: '#ffffff' } : {}"
                                                           :title="'Mark choice ' + String.fromCharCode(65 + ci) + ' as the correct answer'">
                                                        {{-- A real radio, so the circle is clickable by the
                                                             browser itself and reachable by keyboard. It also
                                                             carries the answer index to the server. --}}
                                                        <input type="radio" :name="`questions[${q.uid}][correct_choice]`"
                                                               :value="ci" x-model.number="q.correct"
                                                               style="position: absolute; inset: 0; width: 100%; height: 100%; margin: 0; opacity: 0; cursor: pointer;">
                                                        <span x-text="String.fromCharCode(65 + ci)"></span>
                                                    </label>
                                                </div>
                                                <div class="flex-1 min-w-0" x-data="{ hasMath: false }">
                                                    <div class="flex items-center gap-1.5">
                                                        <input type="text" :name="`questions[${q.uid}][choices][${ci}][text]`" x-model="c.text"
                                                               @click.stop x-ref="choice"
                                                               @input="hasMath = ($event.target.value || '').includes('$'); MathText.preview($refs.choicePreview, $event.target.value)"
                                                               class="flex-1 min-w-0 text-xs py-2 px-3 border border-slate-200 rounded-lg focus:border-[#58706D] focus:outline-none bg-white"
                                                               placeholder="Choice option text...">
                                                        <button type="button" class="math-btn shrink-0" title="Insert inline formula"
                                                                @click.stop="MathText.insert($refs.choice, '$@$')">
                                                            <i class="fa-solid fa-square-root-variable text-[9px]"></i>
                                                        </button>
                                                    </div>
                                                    <div class="mt-1" x-show="hasMath" x-cloak>
                                                        <span class="math-preview"><span data-math x-ref="choicePreview"></span></span>
                                                    </div>
                                                </div>
                                                <div class="w-44">
                                                    <input type="text" :name="`questions[${q.uid}][choices][${ci}][formula]`" x-model="c.formula"
                                                           @click.stop
                                                           class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:border-[#58706D] focus:outline-none bg-white font-mono text-[11px]"
                                                           placeholder="Formula (optional)">
                                                </div>
                                                <div class="shrink-0" style="width: 74px; text-align: right; padding-right: 4px;">
                                                    <span class="font-bold" style="font-size: 10px; letter-spacing: 0.06em; text-transform: uppercase; color: #58706D;"
                                                          x-show="q.correct === ci">
                                                        <i class="fa-solid fa-check"></i> Correct
                                                    </span>
                                                </div>
                                                <button type="button" class="action-icon-btn text-rose-500 hover:bg-rose-50 hover:text-rose-700 shrink-0"
                                                        @click.stop="removeChoice(q, ci)" x-show="q.choices.length > minChoices"
                                                        :title="'Remove choice ' + String.fromCharCode(65 + ci)">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Explanation --}}
                                <details class="border-t border-slate-100 pt-4">
                                    <summary class="cursor-pointer text-xs font-bold text-slate-700 uppercase tracking-wider select-none">
                                        <i class="fa-solid fa-chevron-down text-[10px] mr-1"></i> Explanation / Solution Steps (Optional)
                                    </summary>
                                    <div class="space-y-3 mt-3">
                                        <textarea :name="`questions[${q.uid}][explanation]`" rows="2" class="input-modern"
                                                  placeholder="Detailed solution revealed to students after the contest conclusion..." x-model="q.explanation"></textarea>
                                        <input type="file" :name="`questions[${q.uid}][explanation_image]`" accept="image/*" class="input-modern text-xs">
                                    </div>
                                </details>
                            </div>
                        </div>
                    </template>

                    <button type="button" @click="addQuestion()"
                            class="w-full border-2 border-dashed border-slate-300 rounded-2xl py-4 text-xs font-bold text-slate-500 hover:border-[#58706D] hover:text-[#58706D] transition">
                        <i class="fa-solid fa-plus text-[10px] mr-1"></i> Add Another Question
                    </button>

                    <!-- Inline validation errors. Shown before any server round
                         trip, so a mistake never silently blocks the save and
                         the typed answers stay on the page. -->
                    <div x-show="submitError.length" x-ref="submitError"
                         class="rounded-xl border border-rose-200 bg-rose-50 p-4 mb-4 mt-6">
                        <p class="text-xs font-bold text-rose-700 mb-1">Please fix the following:</p>
                        <ul class="text-xs text-rose-600 list-disc list-inside space-y-0.5 mb-0">
                            <template x-for="problem in submitError" :key="problem">
                                <li x-text="problem"></li>
                            </template>
                        </ul>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between mt-6 mb-4">
                        <a href="{{ route('contest-questions.index') }}" class="btn-brand-outline text-xs px-4 py-2">Cancel</a>
                        <button type="submit" class="btn-brand text-xs px-5 py-2.5 shadow-sm flex items-center gap-1.5">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>Save <span x-text="questions.length"></span> Question(s) to Bank</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bulkQuestions() {
            const MIN_CHOICES = 2;
            const MAX_CHOICES = 6;

            // Rows are keyed by uid, not by index: splicing renumbers every
            // index after the removed one, and Alpine would then reuse the
            // wrong row's nested state (the per-row math preview) for it.
            let choiceSeq = 0;
            const blankChoice = () => ({ uid: 'c' + (++choiceSeq), text: '', formula: '' });
            const blankChoices = () => Array.from({ length: 4 }, blankChoice);

            const blankQuestion = (uid) => ({
                uid,
                text: '',
                formula: '',
                explanation: '',
                correct: null,
                choices: blankChoices(),
            });

            return {
                questions: [blankQuestion('q1')],
                seq: 1,
                submitError: [],
                minChoices: MIN_CHOICES,
                maxChoices: MAX_CHOICES,

                addChoice(q) {
                    if (q.choices.length >= MAX_CHOICES) return;
                    q.choices.push(blankChoice());
                },

                // Two choices is a legitimate question (true/false, and plenty
                // of two-way items), so removal stops there rather than at four.
                removeChoice(q, ci) {
                    if (q.choices.length <= MIN_CHOICES) return;

                    q.choices.splice(ci, 1);

                    // Keep the marker on the choice the user actually picked.
                    // Without this, removing a row above it silently promotes
                    // the next choice to "correct".
                    if (q.correct === null || q.correct === undefined || q.correct === '') return;

                    if (q.correct === ci) {
                        q.correct = null;
                    } else if (q.correct > ci) {
                        q.correct = q.correct - 1;
                    }
                },

                addQuestion() {
                    if (this.questions.length >= 50) return;
                    this.seq++;
                    this.questions.push(blankQuestion('q' + this.seq));
                },

                removeQuestion(q) {
                    if (this.questions.length <= 1) return;
                    this.questions = this.questions.filter((x) => x.uid !== q.uid);
                },

                // Mirrors the server rules exactly, so common mistakes show a
                // message right here instead of a native browser block that
                // never explains itself (or a round trip that wipes the form).
                submit(e) {
                    const problems = [];

                    if (!this.$root.querySelector('[name=type_id]').value) {
                        problems.push('Shared settings: choose the exam type.');
                    }
                    if (!this.$root.querySelector('[name=subject_id]').value) {
                        problems.push('Shared settings: choose the subject.');
                    }

                    this.questions.forEach((q, qi) => {
                        const n = qi + 1;

                        if (!q.text.trim()) {
                            problems.push(`Question ${n}: fill in the question prompt.`);
                        }

                        const filled = q.choices.filter((c) => c.text.trim() !== '');

                        if (filled.length < 2) {
                            problems.push(`Question ${n}: needs at least 2 answer choices.`);
                        }

                        if (q.correct === null || q.correct === undefined || q.correct === '') {
                            problems.push(`Question ${n}: click the row of the correct answer to mark it.`);
                        } else if (!q.choices[q.correct] || !q.choices[q.correct].text.trim()) {
                            problems.push(`Question ${n}: the marked correct answer is one of the blank choices.`);
                        }
                    });

                    if (!problems.length) {
                        return; // let the browser submit; the server re-validates
                    }

                    e.preventDefault();
                    this.submitError = problems;
                    this.$nextTick(() => {
                        this.$refs.submitError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    });
                },
            };
        }
    </script>
</x-layouts.app>
