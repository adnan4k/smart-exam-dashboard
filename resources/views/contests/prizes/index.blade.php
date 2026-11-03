<x-layouts.app>
    @include('contests.partials.pz-styles')

    <div class="pz">
        @include('contests.partials.pz-flash')

        <section class="pz-card">
            <header class="pz-card-head">
                <div class="pz-head-row">
                    <div>
                        <h2 class="pz-title">Contest Prizes</h2>
                        <p class="pz-sub">Subscriptions and money given to top students. Give a new prize from the rankings page.</p>
                    </div>
                    <a href="{{ route('contest-rankings.index') }}" class="pz-btn pz-btn-primary"><i class="fa-solid fa-ranking-star"></i> Contest Rankings</a>
                </div>

                <div class="pz-stats">
                    @foreach ([
                        ['Cash paid', number_format($summary['cash_paid'], 2) . ' ETB', 'fa-circle-check', 'is-brand'],
                        ['Cash owed', number_format($summary['cash_pending'], 2) . ' ETB', 'fa-hourglass-half', 'is-amber'],
                        ['Awaiting payment', $summary['pending_count'], 'fa-money-bill-wave', 'is-amber'],
                        ['Subscriptions given', $summary['subscriptions'], 'fa-id-card', 'is-blue'],
                    ] as [$label, $value, $icon, $tone])
                        <div class="pz-stat">
                            <div class="pz-stat-icon {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div>
                            <div style="min-width: 0;">
                                <p class="pz-stat-label">{{ $label }}</p>
                                <p class="pz-stat-value">{{ $value }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form method="GET" class="pz-filters">
                    <div class="pz-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="search" name="q" value="{{ request('q') }}" class="pz-input" placeholder="Search name, phone or email">
                    </div>
                    <select name="kind" class="pz-select">
                        <option value="">All prizes</option>
                        <option value="subscription" @selected(request('kind') === 'subscription')>Subscriptions</option>
                        <option value="cash" @selected(request('kind') === 'cash')>Money</option>
                    </select>
                    <select name="status" class="pz-select">
                        <option value="">Any status</option>
                        <option value="pending" @selected(request('status') === 'pending')>Awaiting payment</option>
                        <option value="delivered" @selected(request('status') === 'delivered')>Paid / granted</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                    </select>
                    <button type="submit" class="pz-btn pz-btn-primary">Filter</button>
                    @if (request()->hasAny(['q', 'kind', 'status']) && (request('q') || request('kind') || request('status')))
                        <a href="{{ route('contest-prizes.index') }}" class="pz-btn pz-btn-quiet">Clear</a>
                    @endif
                </form>
            </header>

            <div class="pz-table-wrap">
                <table class="pz-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Awarded for</th>
                            <th>Prize</th>
                            <th class="pz-c">Status</th>
                            <th>Payment</th>
                            <th class="pz-hide-lg">Given</th>
                            <th class="pz-r">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($prizes as $prize)
                            @php
                                $phone = $prize->user?->phone ?: $prize->user?->phone_number ?: $prize->recipient_phone;
                                $pill  = match ($prize->status) {
                                    'pending'   => 'is-amber',
                                    'delivered' => 'is-green',
                                    default     => 'is-muted',
                                };
                            @endphp
                            <tr x-data="{ paying: false }">
                                <td>
                                    <p class="pz-name">{{ $prize->user?->name ?? $prize->recipient_name }}</p>
                                    <p class="pz-meta">
                                        @if ($phone)<a href="tel:{{ $phone }}">{{ $phone }}</a>@else No phone @endif
                                        @if ($prize->user?->email) · <a href="mailto:{{ $prize->user->email }}">{{ $prize->user->email }}</a>@endif
                                    </p>
                                    @if (! $prize->user)<p class="pz-meta">Account deleted</p>@endif
                                </td>
                                <td style="min-width: 170px; max-width: 260px;">
                                    <p>{{ $prize->forLabel() }}</p>
                                    @if ($prize->note)<p class="pz-meta" style="white-space: normal;">{{ $prize->note }}</p>@endif
                                </td>
                                <td class="pz-nowrap">
                                    <span class="pz-strong">
                                        <i class="fa-solid {{ $prize->isCash() ? 'fa-money-bill-wave' : 'fa-id-card' }}" style="color: var(--pz-brand); font-size: 11px;"></i>
                                        {{ $prize->summary() }}
                                    </span>
                                </td>
                                <td class="pz-c"><span class="pz-pill {{ $pill }}">{{ $prize->statusLabel() }}</span></td>
                                <td class="pz-nowrap" style="font-size: 12px;">
                                    @if ($prize->isCash() && $prize->status === 'delivered')
                                        <p class="pz-strong">{{ $prize->payment_method }}</p>
                                        @if ($prize->payment_reference)<p class="pz-muted" style="font-family: ui-monospace, monospace;">{{ $prize->payment_reference }}</p>@endif
                                        <p class="pz-faint">{{ $prize->delivered_at?->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y H:i') }}@if ($prize->deliveredBy) · {{ $prize->deliveredBy->name }}@endif</p>
                                    @elseif (! $prize->isCash() && $prize->subscription_id)
                                        <p class="pz-muted">Subscription #{{ $prize->subscription_id }}</p>
                                    @else
                                        <span class="pz-faint">—</span>
                                    @endif
                                </td>
                                <td class="pz-nowrap pz-hide-lg" style="font-size: 12px;">
                                    <p>{{ $prize->created_at->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y') }}</p>
                                    @if ($prize->grantedBy)<p class="pz-faint">by {{ $prize->grantedBy->name }}</p>@endif
                                </td>
                                <td class="pz-r pz-nowrap">
                                    @if ($prize->isCash() && $prize->status === 'pending')
                                        <div x-show="! paying"><div class="pz-btn-row">
                                            <button type="button" class="pz-btn pz-btn-sm pz-btn-primary" @click="paying = true"><i class="fa-solid fa-check"></i> Mark paid</button>
                                            <form method="POST" action="{{ route('contest-prizes.cancel', $prize) }}" style="margin: 0;"
                                                  onsubmit="return confirm('Cancel this prize? It will no longer count as owed.');">
                                                @csrf
                                                <button type="submit" class="pz-btn pz-btn-sm pz-btn-quiet pz-btn-icon pz-btn-danger" title="Cancel prize"><i class="fa-solid fa-xmark"></i></button>
                                            </form>
                                        </div></div>
                                        <form method="POST" action="{{ route('contest-prizes.paid', $prize) }}" x-show="paying" x-cloak class="pz-inline-form" style="text-align: left;">
                                            @csrf
                                            <select name="payment_method" required class="pz-select">
                                                <option value="">Sent via…</option>
                                                @foreach ($methods as $method)
                                                    <option value="{{ $method }}">{{ $method }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="payment_reference" class="pz-input" placeholder="Reference (optional)">
                                            <div style="display: flex; gap: 6px;">
                                                <button type="submit" class="pz-btn pz-btn-sm pz-btn-primary">Save</button>
                                                <button type="button" class="pz-btn pz-btn-sm pz-btn-quiet" @click="paying = false">Back</button>
                                            </div>
                                        </form>
                                    @else
                                        <span class="pz-faint">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="pz-empty">
                                        <i class="fa-solid fa-gift"></i>
                                        <p class="pz-title">No prizes yet</p>
                                        <p class="pz-sub">Open Contest Rankings and press Reward next to a student.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($prizes->hasPages())
                <div class="pz-pager">
                    <span>Showing {{ $prizes->firstItem() }}–{{ $prizes->lastItem() }} of {{ $prizes->total() }} prizes</span>
                    <div class="pz-pager-links">
                        @if ($prizes->onFirstPage())
                            <span class="pz-btn pz-btn-sm pz-btn-quiet" style="opacity: .5; cursor: default;">Previous</span>
                        @else
                            <a href="{{ $prizes->previousPageUrl() }}" class="pz-btn pz-btn-sm pz-btn-quiet">Previous</a>
                        @endif
                        @if ($prizes->hasMorePages())
                            <a href="{{ $prizes->nextPageUrl() }}" class="pz-btn pz-btn-sm pz-btn-quiet">Next</a>
                        @else
                            <span class="pz-btn pz-btn-sm pz-btn-quiet" style="opacity: .5; cursor: default;">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
