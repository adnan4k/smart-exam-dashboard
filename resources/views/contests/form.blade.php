<x-layouts.app>
    @include('contests.partials.theme')

    <div class="ct">
        <div class="row">
            <div class="col-lg-8 col-12 mx-auto">
                @include('contests.partials.flash')

                @php
                    $isEdit  = $contest->exists;
                    $startAt = old('starts_at', optional($contest->starts_at)->format('Y-m-d\TH:i'));
                    $endAt   = old('ends_at', optional($contest->ends_at)->format('Y-m-d\TH:i'));
                @endphp

                <form method="POST"
                      action="{{ $isEdit ? route('contests.update', $contest) : route('contests.store') }}"
                      x-data="{
                          duration: {{ (int) old('duration_minutes', $contest->duration_minutes ?? 40) }},
                          joinWindow: {{ (int) old('join_window_minutes', $contest->join_window_minutes ?? 10) }},
                          startsAt: '{{ $startAt }}',
                          endsAt: '{{ $endAt }}',
                          get joinClosesAt() {
                              if (! this.startsAt) return null;
                              const t = new Date(this.startsAt);
                              t.setMinutes(t.getMinutes() + Number(this.joinWindow));
                              return t;
                          },
                          get suggestedEnd() {
                              if (! this.startsAt) return null;
                              const t = new Date(this.startsAt);
                              t.setMinutes(t.getMinutes() + Number(this.duration) + Number(this.joinWindow));
                              return t;
                          },
                          format(d) {
                              if (! d || isNaN(d)) return '—';
                              return d.toLocaleString(undefined, { weekday:'short', hour:'2-digit', minute:'2-digit' });
                          },
                          toInput(d) {
                              const p = n => String(n).padStart(2,'0');
                              return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
                          },
                          applySuggestedEnd() {
                              if (this.suggestedEnd) this.endsAt = this.toInput(this.suggestedEnd);
                          }
                      }">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6">
                        <div class="border-b border-slate-100 p-5">
                            <div class="flex items-center gap-2 mb-1">
                                <a href="{{ route('contests.index') }}" class="text-xs font-semibold text-[#58706D] hover:underline flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Contests
                                </a>
                                <span class="text-slate-300">&bull;</span>
                                <span class="badge-subtle-brand">{{ $isEdit ? 'Editing' : 'New Setup' }}</span>
                            </div>
                            <h5 class="font-bold text-slate-800 text-lg tracking-tight mb-1">
                                {{ $isEdit ? 'Edit Contest Details' : 'Create New Competition' }}
                            </h5>
                            <p class="text-xs text-slate-400 mb-0">
                                Candidates sit the same paper during an aligned synchronized window for fair competitive evaluation.
                            </p>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Contest Title <span class="text-rose-500">*</span></label>
                                    <input type="text" name="title" required maxlength="255"
                                           value="{{ old('title', $contest->title) }}"
                                           class="input-modern" placeholder="e.g. National Championship — Round 1">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type</label>
                                    <select name="type_id" class="input-modern bg-white">
                                        <option value="">All Exam Types</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}" @selected(old('type_id', $contest->type_id) == $type->id)>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-[11px] text-slate-400 block mt-1">Target cohort visibility</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                                <textarea name="description" rows="2" class="input-modern"
                                          placeholder="Brief description of scope, topics covered, and eligibility...">{{ old('description', $contest->description) }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject (Optional)</label>
                                    <select name="subject_id" class="input-modern bg-white">
                                        <option value="">Mixed Paper</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}" @selected(old('subject_id', $contest->subject_id) == $subject->id)>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-[11px] text-slate-400 block mt-1">Leave blank for multidisciplinary</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Duration (Minutes) <span class="text-rose-500">*</span></label>
                                    <input type="number" name="duration_minutes" min="1" max="600" required
                                           x-model.number="duration" class="input-modern">
                                    <span class="text-[11px] text-slate-400 block mt-1">Time allowed per student</span>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Join Window (Minutes) <span class="text-rose-500">*</span></label>
                                    <input type="number" name="join_window_minutes" min="0" max="600" required
                                           x-model.number="joinWindow" class="input-modern">
                                    <span class="text-[11px] text-slate-400 block mt-1">Leniency period after start</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Starts At <span class="text-rose-500">*</span></label>
                                    <input type="datetime-local" name="starts_at" required
                                           x-model="startsAt" class="input-modern">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ends At <span class="text-rose-500">*</span></label>
                                    <input type="datetime-local" name="ends_at" required
                                           x-model="endsAt" class="input-modern">
                                    <div class="mt-1" x-show="suggestedEnd">
                                        <button type="button" class="text-[11px] text-[#58706D] hover:underline font-semibold flex items-center gap-1"
                                                @click="applySuggestedEnd()">
                                            <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                                            Set to recommended: <span x-text="format(suggestedEnd)"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline Preview Helper -->
                            <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 text-xs mt-3" x-show="startsAt">
                                <div class="flex items-center gap-2 font-bold text-slate-700 mb-2">
                                    <i class="fa-solid fa-clock-rotate-left text-[#58706D]"></i>
                                    <span>Schedule &amp; Window Projection</span>
                                </div>
                                <div class="space-y-1 text-slate-600">
                                    <p class="mb-1">
                                        &bull; Candidates may join between start time and <strong class="text-slate-800" x-text="format(joinClosesAt)"></strong>.
                                    </p>
                                    <p class="mb-0">
                                        &bull; A candidate entering at the deadline needs until
                                        <strong class="text-[#58706D]" x-text="format(suggestedEnd)"></strong> to receive their complete
                                        <strong class="text-slate-800" x-text="duration"></strong> minutes.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="border-t border-slate-100 p-4 bg-slate-50/50 flex items-center justify-between">
                            <a href="{{ route('contests.index') }}" class="btn-brand-outline text-xs px-4 py-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn-brand text-xs px-5 py-2.5 shadow-sm flex items-center gap-1.5">
                                <i class="fa-solid fa-check text-xs"></i>
                                <span>{{ $isEdit ? 'Save Changes' : 'Create & Build Paper' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
