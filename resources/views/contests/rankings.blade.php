<x-layouts.app>
    @include('contests.partials.theme')

    @php
        $fmtTime = function (int $seconds) {
            $h = intdiv($seconds, 3600);
            $m = intdiv($seconds % 3600, 60);
            $s = $seconds % 60;

            return $h ? sprintf('%dh %02dm', $h, $m) : sprintf('%dm %02ds', $m, $s);
        };
    @endphp

    <div class="ct">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                    <!-- Card Header -->
                    <div class="card-header border-b border-slate-100 p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Contest Rankings</h5>
                                <p class="text-xs text-slate-400 mb-0">
                                    Every student who has sat a contest, ranked across all contests by total stars, then total score, then total time.
                                </p>
                            </div>
                            <a href="{{ route('contests.index') }}" class="btn-brand-outline text-xs px-3.5 py-2">
                                <i class="fa-solid fa-trophy text-xs"></i> Contests
                            </a>
                        </div>

                        <!-- Summary tiles -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                            @foreach ([
                                ['Participants', $summary['participants'], 'fa-users', 'bg-blue-50 text-blue-600'],
                                ['Contests Held', $summary['contests'], 'fa-trophy', 'bg-emerald-50 text-[#58706D]'],
                                ['Total Entries', $summary['entries'], 'fa-pen-ruler', 'bg-slate-100 text-slate-600'],
                                ['Stars Earned', $summary['stars'], 'fa-star', 'bg-amber-50 text-amber-600'],
                            ] as [$label, $value, $icon, $tone])
                                <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/70 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $tone }} flex items-center justify-center text-sm flex-shrink-0">
                                        <i class="fa-solid {{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">{{ $label }}</p>
                                        <h5 class="text-lg font-extrabold text-slate-800 mb-0 font-mono">{{ number_format($value) }}</h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Filters -->
                        <form method="GET" class="flex flex-col sm:flex-row items-center gap-2.5 mt-4 pt-3 border-t border-slate-100 max-w-2xl">
                            <div class="relative flex-1 w-full">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="search" name="q" value="{{ $search }}"
                                       class="form-control text-xs w-full" style="padding-left: 2rem !important;" placeholder="Search student name or email...">
                            </div>
                            <select name="type" class="form-select text-xs w-full sm:w-52">
                                <option value="">All exam types</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @selected($typeId === $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-brand text-xs px-4 py-2 w-full sm:w-auto flex-shrink-0">Filter</button>
                            @if ($search !== '' || $typeId)
                                <a href="{{ route('contest-rankings.index') }}" class="text-xs font-semibold text-slate-500 hover:underline flex-shrink-0">Clear</a>
                            @endif
                        </form>
                        <p class="text-[11px] text-slate-400 mt-2 mb-0">
                            Picking an exam type ranks students within that cohort only. Searching never changes a student's rank.
                            Stars include rank bonuses once a contest is finalized; for live contests they are the stars earned so far.
                        </p>
                    </div>

                    <!-- Table -->
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 w-full">
                                <thead>
                                    <tr>
                                        <th class="text-center w-16">Rank</th>
                                        <th>Student</th>
                                        <th class="text-center">Exam Type</th>
                                        <th class="text-center">Contests</th>
                                        <th class="text-center">Wins</th>
                                        <th class="text-center">Best Rank</th>
                                        <th class="text-center">Correct</th>
                                        <th class="text-center">Accuracy</th>
                                        <th class="text-center">Time</th>
                                        <th class="text-center">Stars</th>
                                        <th class="text-center">Coins</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                @forelse ($rows as $row)
                                    <tbody x-data="{ open: false }" class="border-b border-slate-100">
                                        <tr class="hover:bg-slate-50/70 transition cursor-pointer" @click="open = ! open">
                                            <td class="text-center">
                                                @if ($row->rank === 1)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-800 font-extrabold text-xs shadow-xs">
                                                        <i class="fa-solid fa-crown text-[10px] mr-0.5 text-amber-600"></i>1
                                                    </span>
                                                @elseif ($row->rank === 2)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-extrabold text-xs">2</span>
                                                @elseif ($row->rank === 3)
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-50 text-amber-900 border border-amber-200 font-extrabold text-xs">3</span>
                                                @else
                                                    <span class="text-xs font-bold text-slate-500 font-mono">{{ $row->rank }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-full bg-[#58706D]/10 text-[#58706D] font-bold text-xs flex items-center justify-center flex-shrink-0 uppercase">
                                                        {{ mb_substr($row->name ?? '?', 0, 2) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-bold text-slate-800 mb-0 truncate">{{ $row->name }}</p>
                                                        <p class="text-[11px] text-slate-400 mb-0 truncate">
                                                            {{ $row->email }}@if ($row->institution) &middot; {{ $row->institution }}@endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-subtle-brand">{{ $row->type_name ?? '—' }}</span>
                                            </td>
                                            <td class="text-center text-xs font-bold text-slate-700 ct-num">{{ $row->contests }}</td>
                                            <td class="text-center text-xs font-bold ct-num {{ $row->wins ? 'text-amber-700' : 'text-slate-400' }}">
                                                @if ($row->wins)<i class="fa-solid fa-crown text-[10px] text-amber-500"></i>@endif {{ $row->wins }}
                                            </td>
                                            <td class="text-center text-xs font-semibold text-slate-600 ct-num">{{ $row->best_rank ? '#' . $row->best_rank : '—' }}</td>
                                            <td class="text-center text-xs text-slate-700 ct-num">{{ $row->correct }}<span class="text-slate-400">/{{ $row->questions }}</span></td>
                                            <td class="text-center text-xs font-semibold text-slate-700 ct-num">{{ $row->accuracy }}%</td>
                                            <td class="text-center text-xs text-slate-500 ct-num">{{ $fmtTime($row->time_taken) }}</td>
                                            <td class="text-center text-xs ct-star ct-num">
                                                <i class="fa-solid fa-star text-[10px] text-amber-500"></i> {{ number_format($row->stars) }}
                                            </td>
                                            <td class="text-center text-xs text-slate-600 ct-num">{{ number_format($row->coins) }}</td>
                                            <td class="text-center text-slate-400">
                                                <i class="fa-solid fa-chevron-down text-[10px] transition" :class="open && 'rotate-180'"></i>
                                            </td>
                                        </tr>

                                        <!-- Per-contest breakdown -->
                                        <tr x-show="open" x-cloak>
                                            <td colspan="12" class="bg-slate-50/70 px-6 py-3">
                                                <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Contest history</p>
                                                <table class="w-full text-xs">
                                                    <thead>
                                                        <tr class="text-[11px] text-slate-400">
                                                            <th class="text-left font-semibold pb-1.5">Contest</th>
                                                            <th class="text-center font-semibold pb-1.5">Date</th>
                                                            <th class="text-center font-semibold pb-1.5">Rank</th>
                                                            <th class="text-center font-semibold pb-1.5">Score</th>
                                                            <th class="text-center font-semibold pb-1.5">Time</th>
                                                            <th class="text-center font-semibold pb-1.5">Stars</th>
                                                            <th class="text-center font-semibold pb-1.5">Coins</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($history->get($row->user_id, collect()) as $attempt)
                                                            @php
                                                                $final = $attempt->contest?->status === 'finalized';
                                                                $stars = $final ? $attempt->stars_awarded : $attempt->question_stars;
                                                            @endphp
                                                            <tr class="border-t border-slate-200/70">
                                                                <td class="py-1.5">
                                                                    @if ($attempt->contest)
                                                                        <a href="{{ route('contests.leaderboard', $attempt->contest_id) }}" class="font-semibold text-[#58706D] hover:underline" @click.stop>
                                                                            {{ $attempt->contest->title }}
                                                                        </a>
                                                                    @endif
                                                                    @if (! $final)
                                                                        <span class="ml-1 text-[10px] font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded px-1.5 py-0.5">provisional</span>
                                                                    @endif
                                                                    @if ($attempt->status === 'expired')
                                                                        <span class="ml-1 text-[10px] font-semibold text-slate-500 bg-slate-100 rounded px-1.5 py-0.5">no answers</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center text-slate-500">{{ $attempt->contest?->starts_at?->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y') }}</td>
                                                                <td class="text-center font-semibold text-slate-700 ct-num">{{ $final && $attempt->rank ? '#' . $attempt->rank : '—' }}</td>
                                                                <td class="text-center text-slate-700 ct-num">{{ $attempt->score }}<span class="text-slate-400">/{{ $attempt->total_questions }}</span></td>
                                                                <td class="text-center text-slate-500 ct-num">{{ $fmtTime((int) $attempt->time_taken_seconds) }}</td>
                                                                <td class="text-center ct-star ct-num">{{ number_format($stars) }}</td>
                                                                <td class="text-center text-slate-600 ct-num">{{ number_format($attempt->coins_awarded) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                @empty
                                    <tbody>
                                        <tr>
                                            <td colspan="12" class="p-0">
                                                <x-empty-state
                                                    :title="$search !== '' ? 'No Matching Students' : 'No Participants Yet'"
                                                    :description="$search !== '' ? 'No ranked student matches that name or email.' : 'Students appear here once they have submitted a contest.'"
                                                    icon="fa-solid fa-ranking-star"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                @endforelse
                            </table>
                        </div>

                        @if ($rows->hasPages())
                            <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                                <div class="text-xs text-slate-500">
                                    Showing {{ $rows->firstItem() }} to {{ $rows->lastItem() }} of {{ $rows->total() }} students
                                </div>
                                <div>{{ $rows->links() }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
