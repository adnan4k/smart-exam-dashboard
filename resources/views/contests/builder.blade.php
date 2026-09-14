<x-layouts.app>
    @include('contests.partials.theme')

    <div class="ct">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6"
                     x-data="paperBuilder({
                         poolUrl: '{{ route('contests.pool', $contest) }}',
                         addUrl: '{{ route('contests.questions.add', $contest) }}',
                         removeUrl: '{{ route('contests.questions.remove', $contest) }}',
                         locked: {{ $contest->attempts()->exists() ? 'true' : 'false' }},
                         csrf: '{{ csrf_token() }}'
                     })"
                     x-init="load()">

                    <!-- Contest Header -->
                    <div class="border-b border-slate-100 p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <a href="{{ route('contests.index') }}" class="text-xs font-semibold text-[#58706D] hover:underline flex items-center gap-1">
                                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Contests
                                    </a>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="badge-subtle-brand">{{ $contest->type?->name ?? 'All Exam Types' }}</span>
                                </div>
                                <h5 class="font-bold text-slate-800 text-lg tracking-tight mb-1">{{ $contest->title }}</h5>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                    <span class="flex items-center gap-1.5">
                                        <i class="fa-regular fa-clock text-slate-400"></i>
                                        {{ $contest->starts_at->format('D, M d, Y \a\t H:i') }}
                                    </span>
                                    <span>&bull;</span>
                                    <span class="flex items-center gap-1.5">
                                        <i class="fa-solid fa-stopwatch text-slate-400"></i>
                                        {{ $contest->duration_minutes }} minutes
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:items-end gap-2.5">
                                <div class="flex items-center gap-2">
                                    @include('contests.partials.status-badge', ['contest' => $contest])
                                    <a href="{{ route('contests.leaderboard', $contest) }}" class="btn-brand-outline text-xs px-3.5 py-1.5 flex items-center gap-1.5">
                                        <i class="fa-solid fa-trophy text-amber-500"></i> Standings
                                    </a>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($contest->status === 'draft')
                                        <form action="{{ route('contests.publish', $contest) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-brand text-xs px-3.5 py-1.5 shadow-sm flex items-center gap-1.5">
                                                <i class="fa-solid fa-bullhorn text-[11px]"></i> Publish Contest
                                            </button>
                                        </form>
                                    @elseif ($contest->status === 'scheduled' && ! $contest->hasStarted())
                                        <form action="{{ route('contests.unpublish', $contest) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn-brand-outline text-xs px-3.5 py-1.5">
                                                <i class="fa-solid fa-rotate-left text-[11px]"></i> Back to Draft
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <template x-if="locked">
                            <div class="mt-4 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs flex items-center gap-2">
                                <i class="fa-solid fa-lock text-amber-600"></i>
                                <span>Students have already entered this contest. The question paper is locked and cannot be modified.</span>
                            </div>
                        </template>
                    </div>

                    <!-- Builder Columns -->
                    <div class="p-5">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            
                            {{-- LEFT COLUMN: The Paper --}}
                            <div class="border border-slate-200/90 rounded-xl p-4 bg-slate-50/50 flex flex-col h-[640px]">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-200/80 mb-3 flex-shrink-0">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-[#58706D]/10 text-[#58706D] flex items-center justify-center text-xs font-bold">
                                            <i class="fa-solid fa-file-lines"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-bold text-slate-800 text-sm mb-0">Contest Paper</h6>
                                            <p class="text-[11px] text-slate-400 mb-0">Questions assigned to this exam</p>
                                        </div>
                                    </div>
                                    <span class="badge-subtle-brand text-xs font-bold" x-text="paper.length + (paper.length === 1 ? ' Question' : ' Questions')"></span>
                                </div>

                                <div class="flex-1 overflow-y-auto space-y-2.5 pr-1.5 custom-scrollbar">
                                    <template x-for="(q, i) in paper" :key="q.question_id">
                                        <div class="p-3.5 rounded-xl bg-white border border-slate-200/80 shadow-xs hover:border-[#58706D]/30 transition group flex items-start justify-between gap-3">
                                            <div class="flex items-start gap-2.5 flex-1 min-w-0">
                                                <span class="w-6 h-6 rounded-md bg-slate-100 text-slate-600 text-[11px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5" x-text="i + 1"></span>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-medium text-slate-800 mb-2 leading-relaxed" x-text="q.text"></p>
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600" x-text="q.subject || 'No subject'"></span>
                                                        <template x-if="q.difficulty">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold"
                                                                  :class="{
                                                                      'bg-emerald-50 text-emerald-700': q.difficulty === 'easy',
                                                                      'bg-amber-50 text-amber-700': q.difficulty === 'medium',
                                                                      'bg-rose-50 text-rose-700': q.difficulty === 'hard'
                                                                  }"
                                                                  x-text="q.difficulty"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="action-icon-btn text-rose-500 hover:bg-rose-50 hover:text-rose-700 flex-shrink-0" x-show="!locked"
                                                    @click="remove(q.question_id)" title="Remove question from paper">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="paper.length === 0">
                                        <div class="h-full flex flex-col items-center justify-center text-center p-8">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3 text-lg">
                                                <i class="fa-regular fa-folder-open"></i>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-600 mb-1">No questions on paper yet</p>
                                            <p class="text-[11px] text-slate-400 max-w-xs">Select and add questions from the contest bank on the right.</p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- RIGHT COLUMN: Question Bank --}}
                            <div class="border border-slate-200/90 rounded-xl p-4 bg-white flex flex-col h-[640px]">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3 flex-shrink-0">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-[#58706D] flex items-center justify-center text-xs font-bold">
                                            <i class="fa-solid fa-database"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-bold text-slate-800 text-sm mb-0">Contest Bank</h6>
                                            <p class="text-[11px] text-slate-400 mb-0">Unreleased & secret questions</p>
                                        </div>
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-medium" x-text="available.length + ' available'"></span>
                                </div>

                                <!-- Filter Toolbar -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-3 flex-shrink-0">
                                    <div class="relative">
                                        <input type="search" class="w-full text-xs py-2 pl-7 pr-3 border border-slate-200 rounded-xl focus:border-[#58706D] focus:ring-1 focus:ring-[#58706D] focus:outline-none" 
                                               placeholder="Search questions..."
                                               x-model.debounce.400ms="filters.search" @input="load()">
                                        <i class="fa-solid fa-magnifying-glass text-[11px] text-slate-400 absolute left-2.5 top-3"></i>
                                    </div>
                                    <div>
                                        <select class="w-full text-xs py-2 px-2.5 border border-slate-200 rounded-xl focus:border-[#58706D] focus:ring-1 focus:ring-[#58706D] focus:outline-none bg-white" 
                                                x-model="filters.subject_id" @change="load()">
                                            <option value="">All Subjects</option>
                                            @foreach ($subjects as $subject)
                                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <select class="w-full text-xs py-2 px-2.5 border border-slate-200 rounded-xl focus:border-[#58706D] focus:ring-1 focus:ring-[#58706D] focus:outline-none bg-white" 
                                                x-model="filters.difficulty" @change="load()">
                                            <option value="">Any Level</option>
                                            <option value="easy">Easy</option>
                                            <option value="medium">Medium</option>
                                            <option value="hard">Hard</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Questions List -->
                                <div class="flex-1 overflow-y-auto space-y-2.5 pr-1.5 custom-scrollbar">
                                    <template x-for="q in available" :key="q.question_id">
                                        <div class="p-3.5 rounded-xl bg-slate-50/60 border border-slate-200/70 hover:bg-emerald-50/30 hover:border-[#58706D]/30 transition group flex items-start justify-between gap-3">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-medium text-slate-800 mb-2 leading-relaxed" x-text="q.text"></p>
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-white border border-slate-200 text-slate-600" x-text="q.subject || 'No subject'"></span>
                                                    <template x-if="q.difficulty">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold"
                                                              :class="{
                                                                  'bg-emerald-50 text-emerald-700': q.difficulty === 'easy',
                                                                  'bg-amber-50 text-amber-700': q.difficulty === 'medium',
                                                                  'bg-rose-50 text-rose-700': q.difficulty === 'hard'
                                                              }"
                                                              x-text="q.difficulty"></span>
                                                    </template>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-brand text-xs px-2.5 py-1.5 rounded-lg flex items-center gap-1 flex-shrink-0" x-show="!locked"
                                                    @click="add(q.question_id)" title="Add to paper">
                                                <i class="fa-solid fa-plus text-[10px]"></i> Add
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="!loading && available.length === 0">
                                        <div class="h-full flex flex-col items-center justify-center text-center p-8">
                                            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3 text-lg">
                                                <i class="fa-solid fa-inbox"></i>
                                            </div>
                                            <p class="text-xs font-semibold text-slate-600 mb-1">No matching questions in bank</p>
                                            <p class="text-[11px] text-slate-400 max-w-xs">All unassigned questions matching your filters will appear here.</p>
                                        </div>
                                    </template>

                                    <template x-if="loading">
                                        <div class="flex items-center justify-center py-12 text-slate-400 text-xs gap-2">
                                            <i class="fa-solid fa-spinner fa-spin text-sm text-[#58706D]"></i>
                                            <span>Loading question bank...</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <template x-if="error">
                            <div class="mt-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2" x-text="error"></div>
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
