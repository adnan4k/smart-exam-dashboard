<x-layouts.app>
    @include('contests.partials.theme')

    <div class="main-content ct">
        <div class="row">
            <div class="col-lg-9 col-12 mx-auto">
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

                    <div class="card mb-4 mx-4">
                        <div class="card-header pb-0">
                            <h5 class="mb-0">{{ $isEdit ? 'Edit contest question' : 'New contest question' }}</h5>
                            <p class="text-sm ct-muted mb-0">
                                This stays hidden from students until a contest using it has ended — then it
                                joins the study bank with its explanation.
                            </p>
                        </div>

                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-xs font-weight-bolder">Question</label>
                                <textarea name="question_text" rows="3" required class="form-control"
                                          placeholder="Write the question">{{ old('question_text', $question->question_text) }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Formula (optional)</label>
                                    <input type="text" name="formula" class="form-control"
                                           value="{{ old('formula', $question->formula) }}" placeholder="LaTeX">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Question image (optional)</label>
                                    <input type="file" name="question_image" accept="image/*" class="form-control">
                                    @if ($question->question_image_path)
                                        <span class="text-xxs text-secondary">Replacing the current image.</span>
                                    @endif
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Exam type</label>
                                    <select name="type_id" class="form-control" required>
                                        <option value="">Choose…</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}" @selected(old('type_id', $question->type_id) == $type->id)>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Subject</label>
                                    <select name="subject_id" class="form-control" required>
                                        <option value="">Choose…</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}" @selected(old('subject_id', $question->subject_id) == $subject->id)>{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Chapter</label>
                                    <select name="chapter_id" class="form-control">
                                        <option value="">—</option>
                                        @foreach ($chapters as $chapter)
                                            <option value="{{ $chapter->id }}" @selected(old('chapter_id', $question->chapter_id) == $chapter->id)>{{ $chapter->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Difficulty</label>
                                    <select name="difficulty" class="form-control" required>
                                        @foreach (['easy', 'medium', 'hard'] as $level)
                                            <option value="{{ $level }}" @selected(old('difficulty', $question->difficulty) === $level)>{{ ucfirst($level) }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-xxs text-secondary">Used to balance the paper.</span>
                                </div>
                            </div>

                            {{-- Choices --}}
                            <div class="border-top pt-3 mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label text-xs font-weight-bolder mb-0">Choices</label>
                                    <button type="button" class="btn ct-btn-quiet btn-sm mb-0"
                                            @click="addChoice()" x-show="choices.length < 6">+ Add choice</button>
                                </div>
                                <p class="text-xxs text-secondary">Mark the correct one. Students see these in a random order.</p>

                                <template x-for="(choice, i) in choices" :key="i">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <input type="radio" name="correct_choice" :value="i" x-model.number="correct"
                                               class="form-check-input mt-0" required title="Correct answer">
                                        <input type="text" :name="`choices[${i}][text]`" x-model="choice.text"
                                               class="form-control" placeholder="Choice text" required>
                                        <input type="text" :name="`choices[${i}][formula]`" x-model="choice.formula"
                                               class="form-control" style="max-width: 12rem;" placeholder="Formula (optional)">
                                        <button type="button" class="btn btn-link p-0 ct-icon-danger"
                                                @click="removeChoice(i)" x-show="choices.length > 2" title="Remove">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <div class="border-top pt-3 mt-3">
                                <label class="form-label text-xs font-weight-bolder">Explanation</label>
                                <textarea name="explanation" rows="3" class="form-control"
                                          placeholder="Shown after the contest ends, and when this joins the study bank">{{ old('explanation', $question->explanation) }}</textarea>
                                <label class="form-label text-xs font-weight-bolder mt-3">Explanation image (optional)</label>
                                <input type="file" name="explanation_image" accept="image/*" class="form-control">
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('contest-questions.index') }}" class="btn ct-btn-quiet btn-sm mb-0">Cancel</a>
                            <button type="submit" class="btn ct-btn btn-sm mb-0">
                                {{ $isEdit ? 'Save question' : 'Add to contest bank' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
