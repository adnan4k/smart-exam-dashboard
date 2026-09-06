<x-layouts.app>
    @include('contests.partials.theme')

    <div class="main-content ct">
        <div class="row">
            <div class="col-lg-9 col-12 mx-auto">
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

                    <div class="card mb-4 mx-4">
                        <div class="card-header pb-0">
                            <h5 class="mb-0">{{ $isEdit ? 'Edit contest' : 'New contest' }}</h5>
                            <p class="text-sm ct-muted mb-0">
                                Everyone sits the same paper in the same window, so scores are comparable.
                            </p>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Title</label>
                                    <input type="text" name="title" required maxlength="255"
                                           value="{{ old('title', $contest->title) }}"
                                           class="form-control" placeholder="Sunday Challenge — Week 1">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Exam type</label>
                                    <select name="type_id" class="form-control">
                                        <option value="">All exam types</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}" @selected(old('type_id', $contest->type_id) == $type->id)>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="text-xxs text-secondary">Students only see contests for their own exam type.</span>
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Description</label>
                                    <textarea name="description" rows="2" class="form-control"
                                              placeholder="What this round covers">{{ old('description', $contest->description) }}</textarea>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Subject (optional)</label>
                                    <select name="subject_id" class="form-control">
                                        <option value="">Mixed paper</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}" @selected(old('subject_id', $contest->subject_id) == $subject->id)>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Duration (minutes)</label>
                                    <input type="number" name="duration_minutes" min="1" max="600" required
                                           x-model.number="duration" class="form-control">
                                    <span class="text-xxs text-secondary">How long each student gets once they start.</span>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Join window (minutes)</label>
                                    <input type="number" name="join_window_minutes" min="0" max="600" required
                                           x-model.number="joinWindow" class="form-control">
                                    <span class="text-xxs text-secondary">How late a student may still enter.</span>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Starts at</label>
                                    <input type="datetime-local" name="starts_at" required
                                           x-model="startsAt" class="form-control">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-xs font-weight-bolder">Ends at</label>
                                    <input type="datetime-local" name="ends_at" required
                                           x-model="endsAt" class="form-control">
                                    <button type="button" class="btn btn-link p-0 text-xxs" x-show="suggestedEnd"
                                            @click="applySuggestedEnd()">
                                        Use <span x-text="format(suggestedEnd)"></span> (last joiner's full time)
                                    </button>
                                </div>
                            </div>

                            <div class="ct-panel p-3 mt-2" x-show="startsAt">
                                <p class="text-xs font-weight-bolder text-uppercase mb-2">Schedule</p>
                                <p class="text-xs mb-1">
                                    Entry closes at <strong x-text="format(joinClosesAt)"></strong>.
                                </p>
                                <p class="text-xs mb-0">
                                    A student who joins at the last moment needs until
                                    <strong x-text="format(suggestedEnd)"></strong> to get their full
                                    <strong x-text="duration"></strong> minutes. Ending earlier cuts them short.
                                </p>
                            </div>
                        </div>

                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('contests.index') }}" class="btn ct-btn-quiet btn-sm mb-0">Cancel</a>
                            <button type="submit" class="btn ct-btn btn-sm mb-0">
                                {{ $isEdit ? 'Save changes' : 'Create and build paper' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
