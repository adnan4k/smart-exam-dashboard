<x-layouts.app>
    @include('contests.partials.theme')

    <div class="ct">
        <div class="row">
            <div class="col-lg-8 col-12 mx-auto">
                @include('contests.partials.flash')

                @php
                    $isEdit = $question->exists;
                    $existingChoices = old('choices', $choices->map(fn ($c) => [
                        'text' => $c->choice_text, 'formula' => $c->formula,
                    ])->values()->all());
                    if (empty($existingChoices)) {
                        $existingChoices = [['text' => '', 'formula' => ''], ['text' => '', 'formula' => '']];
                    }
                    $correctIndex = old('correct_choice', $isEdit
                        ? $choices->values()->search(fn ($c) => $c->id === $question->correctChoiceId())
                        : 0);
                @endphp

                <form method="POST" enctype="multipart/form-data"
                      action="{{ $isEdit ? route('contest-questions.update', $question->id) : route('contest-questions.store') }}"
                      x-data="{
                          choices: {{ \Illuminate\Support\Js::from($existingChoices) }},
                          correct: {{ (int) ($correctIndex === false ? 0 : $correctIndex) }},
                          addChoice() { if (this.choices.length < 6) this.choices.push({ text: '', formula: '' }); },
                          removeChoice(i) {
                              if (this.choices.length <= 2) return;
                              this.choices.splice(i, 1);
                              if (this.correct >= this.choices.length) this.correct = this.choices.length - 1;
                          }
                      }">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6">
                        <div class="border-b border-slate-100 p-5">
                            <div class="flex items-center gap-2 mb-1">
                                <a href="{{ route('contest-questions.index') }}" class="text-xs font-semibold text-[#58706D] hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Contest Bank
                                </a>
                                <span class="text-slate-300">&bull;</span>
                                <span class="badge-subtle-brand">{{ $isEdit ? 'Editing' : 'New Bank Item' }}</span>
                            </div>
                            <h5 class="font-bold text-slate-800 text-lg tracking-tight mb-1">
                                {{ $isEdit ? 'Edit Contest Bank Question' : 'Add Question to Contest Bank' }}
                            </h5>
                            <p class="text-xs text-slate-400 mb-0">
                                Contest questions remain hidden from students until the competition ends, after which they can be released to the general question pool.
                            </p>
                        </div>

                        <div class="p-6 space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Question Prompt <span class="text-rose-500">*</span></label>
                                <textarea name="question_text" rows="3" required class="input-modern"
                                          placeholder="Type the full question text here...">{{ old('question_text', $question->question_text) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">LaTeX Formula (Optional)</label>
                                    <input type="text" name="formula" class="input-modern font-mono text-xs"
                                           value="{{ old('formula', $question->formula) }}" placeholder="e.g. \sqrt{x^2 + y^2}">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Question Image (Optional)</label>
                                    <input type="file" name="question_image" accept="image/*" class="input-modern text-xs">
                                    @if ($question->question_image_path)
                                        <span class="text-[11px] text-[#58706D] font-medium block mt-1">
                                            <i class="fa-solid fa-image"></i> Current image attached &mdash; uploading a new one replaces it.
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type <span class="text-rose-500">*</span></label>
                                    <select name="type_id" class="input-modern bg-white text-xs" required>
                                        <option value="">Choose…</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}" @selected(old('type_id', $question->type_id) == $type->id)>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject <span class="text-rose-500">*</span></label>
                                    <select name="subject_id" class="input-modern bg-white text-xs" required>
                                        <option value="">Choose…</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}" @selected(old('subject_id', $question->subject_id) == $subject->id)>{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Chapter</label>
                                    <select name="chapter_id" class="input-modern bg-white text-xs">
                                        <option value="">— Optional —</option>
                                        @foreach ($chapters as $chapter)
                                            <option value="{{ $chapter->id }}" @selected(old('chapter_id', $question->chapter_id) == $chapter->id)>{{ $chapter->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Difficulty <span class="text-rose-500">*</span></label>
                                    <select name="difficulty" class="input-modern bg-white text-xs" required>
                                        @foreach (['easy', 'medium', 'hard'] as $level)
                                            <option value="{{ $level }}" @selected(old('difficulty', $question->difficulty) === $level)>{{ ucfirst($level) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Multiple Choice Options --}}
                            <div class="border-t border-slate-100 pt-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-0.5">Answer Choices</label>
                                        <p class="text-[11px] text-slate-400 mb-0">Select the radio button next to the correct answer. Students see these shuffled.</p>
                                    </div>
                                    <button type="button" class="btn-brand-outline text-xs px-3 py-1"
                                            @click="addChoice()" x-show="choices.length < 6">
                                        <i class="fa-solid fa-plus text-[10px]"></i> Add Choice
                                    </button>
                                </div>

                                <div class="space-y-2 mt-3">
                                    <template x-for="(choice, i) in choices" :key="i">
                                        <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 border border-slate-200/70">
                                            <div class="px-2 flex items-center">
                                                <input type="radio" name="correct_choice" :value="i" x-model.number="correct"
                                                       class="w-4 h-4 text-[#58706D] focus:ring-[#58706D] cursor-pointer" required title="Mark as correct answer">
                                            </div>
                                            <div class="flex-1">
                                                <input type="text" :name="`choices[${i}][text]`" x-model="choice.text"
                                                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:border-[#58706D] focus:outline-none bg-white" 
                                                       placeholder="Choice option text..." required>
                                            </div>
                                            <div class="w-44">
                                                <input type="text" :name="`choices[${i}][formula]`" x-model="choice.formula"
                                                       class="w-full text-xs py-2 px-3 border border-slate-200 rounded-lg focus:border-[#58706D] focus:outline-none bg-white font-mono text-[11px]" 
                                                       placeholder="Formula (optional)">
                                            </div>
                                            <button type="button" class="action-icon-btn text-rose-500 hover:bg-rose-50 hover:text-rose-700"
                                                    @click="removeChoice(i)" x-show="choices.length > 2" title="Remove choice">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Explanation --}}
                            <div class="border-t border-slate-100 pt-4 space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Explanation / Solution Steps</label>
                                    <textarea name="explanation" rows="3" class="input-modern"
                                              placeholder="Detailed solution revealed to students after the contest conclusion...">{{ old('explanation', $question->explanation) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Explanation Image (Optional)</label>
                                    <input type="file" name="explanation_image" accept="image/*" class="input-modern text-xs">
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="border-t border-slate-100 p-4 bg-slate-50/50 flex items-center justify-between">
                            <a href="{{ route('contest-questions.index') }}" class="btn-brand-outline text-xs px-4 py-2">Cancel</a>
                            <button type="submit" class="btn-brand text-xs px-5 py-2.5 shadow-sm flex items-center gap-1.5">
                                <i class="fa-solid fa-check text-xs"></i>
                                <span>{{ $isEdit ? 'Save Changes' : 'Add to Contest Bank' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
