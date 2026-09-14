<x-layouts.app>
    @include('contests.partials.theme')

    <div class="ct">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6"
                     x-data="standings({
                         url: '{{ route('contests.standings', $contest) }}',
                         endsAt: '{{ $contest->ends_at->toIso8601String() }}',
                         isFinal: {{ $contest->status === 'finalized' ? 'true' : 'false' }}
                     })"
                     x-init="start()">

                    <!-- Card Header -->
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
                                        Started {{ $contest->starts_at->format('D, M d, Y \a\t H:i') }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex flex-col sm:items-end gap-2">
                                <div class="flex items-center gap-2">
                                    @include('contests.partials.status-badge', ['contest' => $contest])

                                    <template x-if="! isFinal && secondsLeft > 0">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-xs">
                                            <i class="fa-solid fa-hourglass-half text-amber-600 animate-pulse text-[11px]"></i>
                                            Closes in <span x-text="clock" class="font-mono font-black"></span>
                                        </span>
                                    </template>
                                </div>

                                @if ($contest->hasEnded() && $contest->status !== 'finalized')
                                    <form action="{{ route('contests.finalize', $contest) }}" method="POST" class="mt-1">
                                        @csrf
                                        <button type="submit" class="btn-brand text-xs px-4 py-2 shadow-sm flex items-center gap-1.5">
                                            <i class="fa-solid fa-award text-xs"></i> Finalize &amp; Pay Out Rewards
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Micro-Stat Tiles -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mt-5">
                            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/70 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Total Entered</p>
                                    <h5 class="text-lg font-extrabold text-slate-800 mb-0 font-mono" x-text="summary.entered">0</h5>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/70 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    <i class="fa-solid fa-pen-ruler"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Still Sitting</p>
                                    <h5 class="text-lg font-extrabold text-slate-800 mb-0 font-mono" x-text="summary.in_progress">0</h5>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/70 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Submitted</p>
                                    <h5 class="text-lg font-extrabold text-slate-800 mb-0 font-mono" x-text="summary.submitted">0</h5>
                                </div>
                            </div>
                        </div>

                        @unless ($contest->status === 'finalized')
                            <p class="text-[11px] text-slate-400 mt-3 mb-0 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-info text-slate-400"></i>
                                Standings are live &amp; provisional until the contest is finalized — active participants can alter positions in real-time.
                            </p>
                        @endunless
                    </div>

                    <!-- Table of Standings -->
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 w-full">
                                <thead>
                                    <tr>
                                        <th class="text-center w-16">Rank</th>
                                        <th>Student / Candidate</th>
                                        <th class="text-center">Score</th>
                                        <th class="text-center">Time Spent</th>
                                        <th class="text-center">Reward</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center w-24"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="row in rows" :key="row.attempt_id">
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="text-center">
                                                <template x-if="row.rank === 1">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-extrabold text-xs shadow-xs">
                                                        <i class="fa-solid fa-crown text-[10px] mr-0.5 text-amber-600"></i>1
                                                    </span>
                                                </template>
                                                <template x-if="row.rank === 2">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-extrabold text-xs">
                                                        2
                                                    </span>
                                                </template>
                                                <template x-if="row.rank === 3">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-50 text-amber-900 border border-amber-200 font-extrabold text-xs">
                                                        3
                                                    </span>
                                                </template>
                                                <template x-if="!row.rank || row.rank > 3">
                                                    <span class="text-xs font-bold text-slate-500 font-mono" x-text="row.rank ?? '—'"></span>
                                                </template>
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-full bg-[#58706D]/10 text-[#58706D] font-bold text-xs flex items-center justify-center flex-shrink-0 uppercase"
                                                         x-text="row.name.substring(0, 2)"></div>
                                                    <div>
                                                        <p class="text-xs font-bold text-slate-800 mb-0" x-text="row.name"></p>
                                                        <p class="text-[11px] text-slate-400 mb-0" x-text="row.institution || 'Independent Student'"></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-xs font-extrabold text-slate-800 font-mono" x-text="row.score"></span>
                                                <span class="text-[11px] text-slate-400 font-mono" x-text="'/' + row.total"></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-xs font-medium text-slate-600 font-mono" x-text="row.time_taken ? formatTime(row.time_taken) : '—'"></span>
                                            </td>
                                            <td class="text-center">
                                                <template x-if="row.stars || row.coins">
                                                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200/80">
                                                        <i class="fa-solid fa-star text-amber-500 text-[10px]"></i>
                                                        <span x-text="row.stars"></span>
                                                        <template x-if="row.coins">
                                                            <span class="text-slate-500 font-normal" x-text="' &bull; ' + row.coins + ' coins'"></span>
                                                        </template>
                                                    </span>
                                                </template>
                                                <template x-if="! row.stars && ! row.coins">
                                                    <span class="text-xs text-slate-300">—</span>
                                                </template>
                                            </td>
                                            <td class="text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wide"
                                                      :class="{
                                                        'bg-emerald-50 text-[#58706D] border border-emerald-200': row.status === 'submitted',
                                                        'bg-blue-50 text-blue-700 border border-blue-200': row.status === 'in_progress',
                                                        'bg-slate-100 text-slate-600': row.status === 'expired',
                                                        'bg-rose-50 text-rose-700 border border-rose-200': row.status === 'voided'
                                                      }"
                                                      x-text="row.status.replace('_', ' ')"></span>
                                            </td>
                                            <td class="text-center">
                                                <template x-if="row.status !== 'voided'">
                                                    <form :action="voidUrl(row.attempt_id)" method="POST"
                                                          @submit="return confirm('Void this entry? Any stars already paid will be revoked.')">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <button type="submit" class="action-icon-btn text-rose-500 hover:bg-rose-50 hover:text-rose-700" title="Void Entry">
                                                            <i class="fa-solid fa-ban text-xs"></i>
                                                        </button>
                                                    </form>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>

                                    <template x-if="rows.length === 0">
                                        <tr>
                                            <td colspan="7" class="text-center py-10">
                                                <div class="flex flex-col items-center justify-center text-slate-400">
                                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-2 text-lg">
                                                        <i class="fa-solid fa-user-clock"></i>
                                                    </div>
                                                    <p class="text-xs font-semibold text-slate-600 mb-0.5">No entries yet</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">Candidate submissions will populate here automatically as they sit the contest.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Reward bands in force for this contest --}}
                <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6">
                    <div class="border-b border-slate-100 p-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-gift"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-slate-800 text-sm mb-0">Reward Bands &amp; Distribution</h6>
                                <p class="text-[11px] text-slate-400 mb-0">
                                    {{ $contest->rewardRules()->exists() ? 'Custom tiers configured for this contest.' : 'Using standard system-wide reward defaults.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex flex-wrap gap-3">
                            @foreach ($rules as $rule)
                                <div class="px-4 py-3 rounded-xl bg-slate-50/80 border border-slate-200/70 min-w-[140px]">
                                    <p class="text-[10px] text-uppercase text-slate-400 font-extrabold tracking-wider mb-1">
                                        Rank {{ $rule->rank_from }}{{ $rule->rank_to ? '–' . $rule->rank_to : '+' }}
                                    </p>
                                    <p class="text-sm font-bold text-slate-800 mb-0 flex items-center gap-1">
                                        <i class="fa-solid fa-star text-amber-500 text-xs"></i>
                                        <span>{{ $rule->stars }} Stars</span>
                                        @if ($rule->coins)
                                            <span class="text-xs text-slate-400 font-normal">&bull; {{ $rule->coins }} coins</span>
                                        @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function standings(config) {
            return {
                rows: [],
                summary: { entered: 0, in_progress: 0, submitted: 0 },
                secondsLeft: 0,
                isFinal: config.isFinal,
                timer: null,
                poller: null,

                start() {
                    this.refresh();
                    this.tick();
                    // Poll while the contest is running; stop once it is settled.
                    this.poller = setInterval(() => {
                        if (this.isFinal) {
                            clearInterval(this.poller);
                            return;
                        }
                        this.refresh();
                    }, 15000);
                    this.timer = setInterval(() => this.tick(), 1000);
                },

                tick() {
                    this.secondsLeft = Math.max(0,
                        Math.floor((new Date(config.endsAt) - new Date()) / 1000));
                },

                get clock() {
                    return this.formatTime(this.secondsLeft);
                },

                formatTime(total) {
                    const h = Math.floor(total / 3600);
                    const m = Math.floor((total % 3600) / 60);
                    const s = total % 60;
                    const p = n => String(n).padStart(2, '0');
                    return h > 0 ? `${h}:${p(m)}:${p(s)}` : `${p(m)}:${p(s)}`;
                },

                voidUrl(attemptId) {
                    return '{{ url('contest-attempts') }}/' + attemptId + '/void';
                },

                async refresh() {
                    try {
                        const res = await fetch(config.url, { headers: { 'Accept': 'application/json' } });
                        if (! res.ok) return;
                        const data = await res.json();
                        this.rows = data.rows;
                        this.isFinal = data.is_final;
                        this.summary = {
                            entered: data.entered,
                            in_progress: data.in_progress,
                            submitted: data.submitted
                        };
                    } catch (e) {
                        // A failed poll is not worth interrupting the page for;
                        // the next one in 15s will pick it up.
                    }
                }
            };
        }
    </script>
</x-layouts.app>
