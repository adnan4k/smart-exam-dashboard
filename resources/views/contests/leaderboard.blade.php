<x-layouts.app>
    <div class="main-content">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card mb-4 mx-4"
                     x-data="standings({
                         url: '{{ route('contests.standings', $contest) }}',
                         endsAt: '{{ $contest->ends_at->toIso8601String() }}',
                         isFinal: {{ $contest->status === 'finalized' ? 'true' : 'false' }}
                     })"
                     x-init="start()">

                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-0">{{ $contest->title }}</h5>
                                <p class="text-sm text-secondary mb-0">
                                    {{ $contest->type?->name ?? 'All exam types' }} &middot;
                                    {{ $contest->starts_at->format('D, d M Y H:i') }}
                                </p>
                            </div>
                            <div class="text-end">
                                @include('contests.partials.status-badge', ['contest' => $contest])

                                <template x-if="! isFinal && secondsLeft > 0">
                                    <p class="text-xs mb-0 mt-2">
                                        Closes in <strong x-text="clock"></strong>
                                    </p>
                                </template>

                                @if ($contest->hasEnded() && $contest->status !== 'finalized')
                                    <form action="{{ route('contests.finalize', $contest) }}" method="POST" class="mt-2">
                                        @csrf
                                        <button type="submit" style="background-color:#56C596;" class="btn text-white btn-sm mb-0">
                                            Finalize &amp; pay out
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-4">
                                <p class="text-xxs text-uppercase text-secondary font-weight-bolder mb-0">Entered</p>
                                <h6 class="mb-0" x-text="summary.entered"></h6>
                            </div>
                            <div class="col-4">
                                <p class="text-xxs text-uppercase text-secondary font-weight-bolder mb-0">Still sitting</p>
                                <h6 class="mb-0" x-text="summary.in_progress"></h6>
                            </div>
                            <div class="col-4">
                                <p class="text-xxs text-uppercase text-secondary font-weight-bolder mb-0">Submitted</p>
                                <h6 class="mb-0" x-text="summary.submitted"></h6>
                            </div>
                        </div>

                        @unless ($contest->status === 'finalized')
                            <p class="text-xxs text-secondary mt-3 mb-0">
                                Standings are provisional until the contest is finalized — students still sitting can change them.
                            </p>
                        @endunless
                    </div>

                    <div class="card-body px-0 pt-3 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Rank</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Student</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Score</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reward</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="row in rows" :key="row.attempt_id">
                                        <tr>
                                            <td class="ps-4">
                                                <span class="text-xs font-weight-bold" x-text="row.rank ?? '—'"></span>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0" x-text="row.name"></p>
                                                <p class="text-xxs text-secondary mb-0" x-text="row.institution || ''"></p>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-xs font-weight-bold" x-text="row.score"></span>
                                                <span class="text-xxs text-secondary" x-text="'/' + row.total"></span>
                                            </td>
                                            <td class="text-center">
                                                <span class="text-xs" x-text="row.time_taken ? formatTime(row.time_taken) : '—'"></span>
                                            </td>
                                            <td class="text-center">
                                                <template x-if="row.stars || row.coins">
                                                    <span class="text-xs">
                                                        <span x-text="row.stars"></span>★
                                                        <template x-if="row.coins">
                                                            <span class="text-secondary" x-text="' · ' + row.coins + ' coins'"></span>
                                                        </template>
                                                    </span>
                                                </template>
                                                <template x-if="! row.stars && ! row.coins">
                                                    <span class="text-xxs text-secondary">—</span>
                                                </template>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-sm"
                                                      :class="{
                                                        'bg-gradient-success': row.status === 'submitted',
                                                        'bg-gradient-warning': row.status === 'in_progress',
                                                        'bg-gradient-secondary': row.status === 'expired',
                                                        'bg-gradient-danger': row.status === 'voided'
                                                      }"
                                                      x-text="row.status.replace('_', ' ')"></span>
                                            </td>
                                            <td class="text-center">
                                                <template x-if="row.status !== 'voided'">
                                                    <form :action="voidUrl(row.attempt_id)" method="POST"
                                                          @submit="return confirm('Void this entry? Any stars already paid are taken back.')">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <button type="submit" class="btn btn-link p-0 m-0 text-red-500 text-xxs" title="Void entry">
                                                            Void
                                                        </button>
                                                    </form>
                                                </template>
                                            </td>
                                        </tr>
                                    </template>

                                    <template x-if="rows.length === 0">
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <p class="text-sm text-secondary mb-0">No entries yet.</p>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Reward bands in force for this contest --}}
                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Reward bands</h6>
                        <p class="text-xs text-secondary mb-0">
                            {{ $contest->rewardRules()->exists() ? 'Set for this contest.' : 'Using the global defaults.' }}
                        </p>
                    </div>
                    <div class="card-body pt-3">
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($rules as $rule)
                                <div class="border-radius-md bg-gray-100 px-3 py-2">
                                    <p class="text-xxs text-uppercase text-secondary font-weight-bolder mb-0">
                                        Rank {{ $rule->rank_from }}{{ $rule->rank_to ? '–' . $rule->rank_to : '+' }}
                                    </p>
                                    <p class="text-sm font-weight-bold mb-0">
                                        {{ $rule->stars }}★
                                        @if ($rule->coins)
                                            <span class="text-xs text-secondary font-weight-normal">· {{ $rule->coins }} coins</span>
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
