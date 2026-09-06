<x-layouts.app>
    @include('contests.partials.theme')

    <div class="main-content ct">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card mb-4 mx-4"
                     x-data="paperBuilder({
                         poolUrl: '{{ route('contests.pool', $contest) }}',
                         addUrl: '{{ route('contests.questions.add', $contest) }}',
                         removeUrl: '{{ route('contests.questions.remove', $contest) }}',
                         locked: {{ $contest->attempts()->exists() ? 'true' : 'false' }},
                         csrf: '{{ csrf_token() }}'
                     })"
                     x-init="load()">

                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-0">{{ $contest->title }}</h5>
                                <p class="text-sm ct-muted mb-0">
                                    {{ $contest->type?->name ?? 'All exam types' }} &middot;
                                    {{ $contest->starts_at->format('D, d M Y H:i') }} &middot;
                                    {{ $contest->duration_minutes }} min
                                </p>
                            </div>
                            <div class="text-end">
                                @include('contests.partials.status-badge', ['contest' => $contest])
                                <div class="mt-2">
                                    @if ($contest->status === 'draft')
                                        <form action="{{ route('contests.publish', $contest) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn ct-btn btn-sm mb-0">Publish</button>
                                        </form>
                                    @elseif ($contest->status === 'scheduled' && ! $contest->hasStarted())
                                        <form action="{{ route('contests.unpublish', $contest) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn ct-btn-quiet btn-sm mb-0">Back to draft</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('contests.leaderboard', $contest) }}" class="btn ct-btn-quiet btn-sm mb-0">Leaderboard</a>
                                </div>
                            </div>
                        </div>

                        <template x-if="locked">
                            <div class="alert alert-ct-warn text-xs mt-3 mb-0">
                                Students have already entered this contest, so the paper is frozen.
                            </div>
                        </template>
                    </div>

                    <div class="card-body pt-3">
                        <div class="row">
                            {{-- The paper --}}
                            <div class="col-lg-6 mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">The paper</h6>
                                    <span class="ct-badge ct-badge-finalized" x-text="paper.length + ' question' + (paper.length === 1 ? '' : 's')"></span>
                                </div>

                                <div class="border-radius-md" style="max-height: 32rem; overflow-y: auto;">
                                    <template x-for="(q, i) in paper" :key="q.question_id">
                                        <div class="d-flex align-items-start justify-content-between border-bottom py-2 px-2">
                                            <div class="pe-2">
                                                <p class="text-xs mb-1">
                                                    <span class="text-secondary font-weight-bolder" x-text="(i + 1) + '.'"></span>
                                                    <span x-text="q.text"></span>
                                                </p>
                                                <span class="ct-badge ct-badge-quiet" x-text="q.subject || 'No subject'"></span>
                                                <template x-if="q.difficulty">
                                                    <span class="ct-badge ct-badge-quiet" x-text="q.difficulty"></span>
                                                </template>
                                            </div>
                                            <button type="button" class="btn btn-link p-0 ct-icon-danger" x-show="!locked"
                                                    @click="remove(q.question_id)" title="Remove from paper">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="paper.length === 0">
                                        <p class="text-xs text-secondary text-center py-4 mb-0">
                                            Nothing on the paper yet. Add questions from the contest bank.
                                        </p>
                                    </template>
                                </div>
                            </div>

                            {{-- The contest bank --}}
                            <div class="col-lg-6">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Contest bank</h6>
                                    <span class="text-xxs text-secondary">Unreleased questions only</span>
                                </div>

                                <div class="d-flex gap-2 mb-2">
                                    <input type="search" class="form-control form-control-sm" placeholder="Search"
                                           x-model.debounce.400ms="filters.search" @input="load()">
                                    <select class="form-control form-control-sm" x-model="filters.subject_id" @change="load()">
                                        <option value="">All subjects</option>
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                    <select class="form-control form-control-sm" x-model="filters.difficulty" @change="load()">
                                        <option value="">Any level</option>
                                        <option value="easy">Easy</option>
                                        <option value="medium">Medium</option>
                                        <option value="hard">Hard</option>
                                    </select>
                                </div>

                                <div class="border-radius-md" style="max-height: 29rem; overflow-y: auto;">
                                    <template x-for="q in available" :key="q.question_id">
                                        <div class="d-flex align-items-start justify-content-between border-bottom py-2 px-2">
                                            <div class="pe-2">
                                                <p class="text-xs mb-1" x-text="q.text"></p>
                                                <span class="ct-badge ct-badge-quiet" x-text="q.subject || 'No subject'"></span>
                                                <template x-if="q.difficulty">
                                                    <span class="ct-badge ct-badge-quiet" x-text="q.difficulty"></span>
                                                </template>
                                            </div>
                                            <button type="button" class="btn btn-link p-0 ct-icon-add" x-show="!locked"
                                                    @click="add(q.question_id)" title="Add to paper">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="!loading && available.length === 0">
                                        <div class="text-center py-4 px-3">
                                            <p class="text-xs text-secondary mb-1">No unused contest questions match.</p>
                                            <p class="text-xxs text-secondary mb-0">
                                                Contest papers draw only from questions saved to the contest bank,
                                                so they stay hidden from students until this contest ends.
                                            </p>
                                        </div>
                                    </template>

                                    <template x-if="loading">
                                        <p class="text-xs text-secondary text-center py-4 mb-0">Loading…</p>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <template x-if="error">
                            <div class="alert alert-ct-bad text-xs mt-3 mb-0" x-text="error"></div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function paperBuilder(config) {
            return {
                paper: [],
                available: [],
                filters: { search: '', subject_id: '', difficulty: '' },
                loading: false,
                error: '',
                locked: config.locked,

                async load() {
                    this.loading = true;
                    this.error = '';
                    try {
                        const params = new URLSearchParams(
                            Object.entries(this.filters).filter(([, v]) => v !== '')
                        );
                        const res = await fetch(`${config.poolUrl}?${params}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (! res.ok) throw new Error('Could not load the question bank.');
                        const data = await res.json();
                        this.paper = data.paper;
                        this.available = data.available;
                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                },

                async add(questionId) {
                    await this.send(config.addUrl, 'POST', questionId);
                },

                async remove(questionId) {
                    await this.send(config.removeUrl, 'DELETE', questionId);
                },

                async send(url, method, questionId) {
                    this.error = '';
                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': config.csrf
                            },
                            body: JSON.stringify({ question_id: questionId })
                        });
                        if (! res.ok) {
                            const body = await res.json().catch(() => ({}));
                            throw new Error(body.message || 'That change could not be saved.');
                        }
                        await this.load();
                    } catch (e) {
                        this.error = e.message;
                    }
                }
            };
        }
    </script>
</x-layouts.app>
