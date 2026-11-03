<x-layouts.app>
    @include('contests.partials.pz-styles')

    @php
        $fmtTime = function (int $seconds) {
            $h = intdiv($seconds, 3600);
            $m = intdiv($seconds % 3600, 60);
            $s = $seconds % 60;

            return $h ? sprintf('%dh %02dm', $h, $m) : sprintf('%dm %02ds', $m, $s);
        };
    @endphp

    <div class="pz">
        @include('contests.partials.pz-flash')

        <section class="pz-card">
            <header class="pz-card-head">
                <div class="pz-head-row">
                    <div>
                        <h2 class="pz-title">Contest Rankings</h2>
                        <p class="pz-sub">Every student who has sat a contest, ranked across all contests by total stars, then total score, then total time.</p>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('contest-prizes.index') }}" class="pz-btn pz-btn-outline"><i class="fa-solid fa-gift"></i> Prizes</a>
                        <a href="{{ route('contests.index') }}" class="pz-btn pz-btn-quiet"><i class="fa-solid fa-trophy"></i> Contests</a>
                    </div>
                </div>

                <div class="pz-stats">
                    @foreach ([
                        ['Participants', $summary['participants'], 'fa-users', 'is-blue'],
                        ['Contests held', $summary['contests'], 'fa-trophy', 'is-brand'],
                        ['Total entries', $summary['entries'], 'fa-pen-ruler', 'is-slate'],
                        ['Stars earned', $summary['stars'], 'fa-star', 'is-amber'],
                    ] as [$label, $value, $icon, $tone])
                        <div class="pz-stat">
                            <div class="pz-stat-icon {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div>
                            <div style="min-width: 0;">
                                <p class="pz-stat-label">{{ $label }}</p>
                                <p class="pz-stat-value">{{ number_format($value) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form method="GET" class="pz-filters">
                    <div class="pz-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="q" value="{{ $search }}" class="pz-input" placeholder="Search name or email">
                    </div>
                    <select name="type" class="pz-select">
                        <option value="">All exam types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected($typeId === $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="pz-btn pz-btn-primary">Filter</button>
                    @if ($search !== '' || $typeId)
                        <a href="{{ route('contest-rankings.index') }}" class="pz-btn pz-btn-quiet">Clear</a>
                    @endif
                </form>
                <p class="pz-hint">
                    Choosing an exam type ranks students within that cohort. Searching never changes anyone's rank.
                    Stars include rank bonuses once a contest is finalized; for live contests they are the stars earned so far.
                </p>
            </header>

            <div class="pz-table-wrap">
                <table class="pz-table">
                    <thead>
                        <tr>
                            <th class="pz-c" style="width: 64px;">Rank</th>
                            <th>Student</th>
                            <th class="pz-c pz-hide-lg">Exam type</th>
                            <th class="pz-c">Contests</th>
                            <th class="pz-c">Wins</th>
                            <th class="pz-c">Accuracy</th>
                            <th class="pz-c pz-hide-lg">Time</th>
                            <th class="pz-c">Stars</th>
                            <th class="pz-c">Prize</th>
                            <th style="width: 36px;"></th>
                        </tr>
                    </thead>

                    @forelse ($rows as $row)
                        @php
                            $given   = $prizes->get($row->user_id, collect());
                            $student = $users->get($row->user_id);
                        @endphp
                        <tbody class="pz-group" x-data="{ open: false }">
                            <tr class="pz-clickable" @click="open = ! open">
                                <td class="pz-c">
                                    <span class="pz-rank {{ $row->rank <= 3 ? 'r' . $row->rank : '' }}">
                                        @if ($row->rank === 1)<i class="fa-solid fa-crown"></i>@endif{{ $row->rank }}
                                    </span>
                                </td>
                                <td>
                                    <div class="pz-person">
                                        <div class="pz-avatar">{{ mb_substr($row->name ?? '?', 0, 2) }}</div>
                                        <div class="pz-person-text">
                                            <p class="pz-name">{{ $row->name }}</p>
                                            <p class="pz-meta">{{ $row->email }}@if ($row->institution) · {{ $row->institution }}@endif</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="pz-c pz-hide-lg"><span class="pz-pill is-brand">{{ $row->type_name ?? '—' }}</span></td>
                                <td class="pz-c pz-num pz-strong">{{ $row->contests }}</td>
                                <td class="pz-c pz-num">
                                    @if ($row->wins)
                                        <span style="color: #b45309; font-weight: 700;"><i class="fa-solid fa-crown" style="font-size: 10px; color: #f59e0b;"></i> {{ $row->wins }}</span>
                                    @else
                                        <span class="pz-faint">0</span>
                                    @endif
                                </td>
                                <td class="pz-c pz-num pz-strong">{{ $row->accuracy }}%</td>
                                <td class="pz-c pz-num pz-muted pz-nowrap pz-hide-lg">{{ $fmtTime($row->time_taken) }}</td>
                                <td class="pz-c"><span class="pz-stars"><i class="fa-solid fa-star"></i> {{ number_format($row->stars) }}</span></td>
                                <td class="pz-c pz-nowrap">
                                    <a href="{{ route('contest-prizes.create', ['user' => $row->user_id, 'rank' => $row->rank]) }}" @click.stop
                                       class="pz-btn pz-btn-sm {{ $row->rank <= 3 ? 'pz-btn-primary' : 'pz-btn-outline' }}">
                                        <i class="fa-solid fa-gift"></i> Reward
                                    </a>
                                    @if ($given->isNotEmpty())
                                        <p class="pz-faint" style="font-size: 10.5px; margin-top: 3px;">{{ $given->count() }} given</p>
                                    @endif
                                </td>
                                <td class="pz-c"><i class="fa-solid fa-chevron-down pz-chevron" :class="open && 'is-open'"></i></td>
                            </tr>

                            <tr class="pz-detail" x-show="open" x-cloak>
                                <td colspan="10">
                                    @if ($student)
                                        <div class="pz-detail-grid">
                                            <div>
                                                <span class="pz-label">Contact</span>
                                                @include('contests.prizes.partials.contact', ['user' => $student])
                                            </div>
                                            <div>
                                                <span class="pz-label">Prizes given</span>
                                                <ul class="pz-list">
                                                    @forelse ($given as $p)
                                                        <li>
                                                            <span class="pz-strong">{{ $p->summary() }}</span>
                                                            <span class="pz-muted">· {{ $p->forLabel() }}</span>
                                                            <span class="pz-pill is-sm {{ $p->status === 'pending' ? 'is-amber' : 'is-green' }}">{{ $p->statusLabel() }}</span>
                                                        </li>
                                                    @empty
                                                        <li class="pz-faint">None yet.</li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </div>
                                    @endif

                                    <span class="pz-label">Contest history</span>
                                    <div class="pz-table-wrap">
                                        <table class="pz-mini">
                                            <thead>
                                                <tr>
                                                    <th>Contest</th>
                                                    <th>Date</th>
                                                    <th style="text-align: center;">Rank</th>
                                                    <th style="text-align: center;">Score</th>
                                                    <th style="text-align: center;">Time</th>
                                                    <th style="text-align: center;">Stars</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($history->get($row->user_id, collect()) as $attempt)
                                                    @php
                                                        $final = $attempt->contest?->status === 'finalized';
                                                        $stars = $final ? $attempt->stars_awarded : $attempt->question_stars;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            @if ($attempt->contest)
                                                                <a href="{{ route('contests.leaderboard', $attempt->contest_id) }}" style="font-weight: 600;" @click.stop>{{ $attempt->contest->title }}</a>
                                                            @endif
                                                            @if (! $final)<span class="pz-pill is-sm is-amber">provisional</span>@endif
                                                            @if ($attempt->status === 'expired')<span class="pz-pill is-sm is-muted">no answers</span>@endif
                                                        </td>
                                                        <td class="pz-muted" style="white-space: nowrap;">{{ $attempt->contest?->starts_at?->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y') }}</td>
                                                        <td class="pz-num pz-strong" style="text-align: center;">{{ $final && $attempt->rank ? '#' . $attempt->rank : '—' }}</td>
                                                        <td class="pz-num" style="text-align: center;">{{ $attempt->score }}<span class="pz-faint">/{{ $attempt->total_questions }}</span></td>
                                                        <td class="pz-num pz-muted" style="text-align: center; white-space: nowrap;">{{ $fmtTime((int) $attempt->time_taken_seconds) }}</td>
                                                        <td style="text-align: center;"><span class="pz-stars">{{ number_format($stars) }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="10">
                                    <div class="pz-empty">
                                        <i class="fa-solid fa-ranking-star"></i>
                                        <p class="pz-title">{{ $search !== '' ? 'No matching students' : 'No participants yet' }}</p>
                                        <p class="pz-sub">{{ $search !== '' ? 'No ranked student matches that name or email.' : 'Students appear here once they have submitted a contest.' }}</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            @if ($rows->hasPages())
                <div class="pz-pager">
                    <span>Showing {{ $rows->firstItem() }}–{{ $rows->lastItem() }} of {{ $rows->total() }} students</span>
                    <div class="pz-pager-links">
                        @if ($rows->onFirstPage())
                            <span class="pz-btn pz-btn-sm pz-btn-quiet" style="opacity: .5; cursor: default;">Previous</span>
                        @else
                            <a href="{{ $rows->previousPageUrl() }}" class="pz-btn pz-btn-sm pz-btn-quiet">Previous</a>
                        @endif
                        @if ($rows->hasMorePages())
                            <a href="{{ $rows->nextPageUrl() }}" class="pz-btn pz-btn-sm pz-btn-quiet">Next</a>
                        @else
                            <span class="pz-btn pz-btn-sm pz-btn-quiet" style="opacity: .5; cursor: default;">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
