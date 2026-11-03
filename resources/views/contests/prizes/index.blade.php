<x-layouts.app>
    @include('contests.partials.theme')

    <div class="ct">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                    <div class="card-header border-b border-slate-100 p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Contest Prizes</h5>
                                <p class="text-xs text-slate-400 mb-0">Subscriptions and money given to top students. Give a new prize from the rankings page.</p>
                            </div>
                            <a href="{{ route('contest-rankings.index') }}" class="btn-brand text-xs px-4 py-2 shadow-sm">
                                <i class="fa-solid fa-ranking-star text-xs"></i> Contest Rankings
                            </a>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
                            @foreach ([
                                ['Cash Paid', number_format($summary['cash_paid'], 2) . ' ETB', 'fa-circle-check', 'bg-emerald-50 text-[#58706D]'],
                                ['Cash Owed', number_format($summary['cash_pending'], 2) . ' ETB', 'fa-hourglass-half', 'bg-amber-50 text-amber-600'],
                                ['Awaiting Payment', $summary['pending_count'], 'fa-money-bill-wave', 'bg-amber-50 text-amber-600'],
                                ['Subscriptions Given', $summary['subscriptions'], 'fa-id-card', 'bg-blue-50 text-blue-600'],
                            ] as [$label, $value, $icon, $tone])
                                <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/70 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $tone }} flex items-center justify-center text-sm flex-shrink-0">
                                        <i class="fa-solid {{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">{{ $label }}</p>
                                        <h5 class="text-base font-extrabold text-slate-800 mb-0 font-mono">{{ $value }}</h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <form method="GET" class="flex flex-col sm:flex-row items-center gap-2.5 mt-4 pt-3 border-t border-slate-100 max-w-3xl">
                            <div class="relative flex-1 w-full">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="search" name="q" value="{{ request('q') }}" class="form-control text-xs w-full"
                                       style="padding-left: 2rem !important;" placeholder="Search name, phone or email...">
                            </div>
                            <select name="kind" class="form-select text-xs w-full sm:w-40">
                                <option value="">All prizes</option>
                                <option value="subscription" @selected(request('kind') === 'subscription')>Subscriptions</option>
                                <option value="cash" @selected(request('kind') === 'cash')>Money</option>
                            </select>
                            <select name="status" class="form-select text-xs w-full sm:w-44">
                                <option value="">Any status</option>
                                <option value="pending" @selected(request('status') === 'pending')>Awaiting payment</option>
                                <option value="delivered" @selected(request('status') === 'delivered')>Paid / granted</option>
                                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                            </select>
                            <button type="submit" class="btn-brand text-xs px-4 py-2 w-full sm:w-auto flex-shrink-0">Filter</button>
                        </form>
                    </div>

                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 w-full">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Awarded for</th>
                                        <th class="text-center">Prize</th>
                                        <th class="text-center">Status</th>
                                        <th>Payment</th>
                                        <th class="text-center">Given</th>
                                        <th class="text-center w-40">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($prizes as $prize)
                                        @php
                                            $phone = $prize->user?->phone ?: $prize->user?->phone_number ?: $prize->recipient_phone;
                                            $tone = match ($prize->status) {
                                                'pending'   => 'bg-amber-50 text-amber-800 border-amber-200',
                                                'delivered' => 'bg-emerald-50 text-[#3f5653] border-emerald-200',
                                                default     => 'bg-slate-100 text-slate-500 border-slate-200',
                                            };
                                        @endphp
                                        <tr x-data="{ paying: false }" class="align-top">
                                            <td>
                                                <p class="text-xs font-bold text-slate-800 mb-0">{{ $prize->user?->name ?? $prize->recipient_name }}</p>
                                                <p class="text-[11px] text-slate-500 mb-0">
                                                    @if ($phone)<a href="tel:{{ $phone }}" class="text-[#58706D] hover:underline">{{ $phone }}</a>@else<span class="text-slate-400">No phone</span>@endif
                                                    @if ($prize->user?->email) &middot; <a href="mailto:{{ $prize->user->email }}" class="hover:underline">{{ $prize->user->email }}</a>@endif
                                                </p>
                                                @if (! $prize->user)
                                                    <p class="text-[10px] text-slate-400 mb-0">Account deleted</p>
                                                @endif
                                            </td>
                                            <td class="text-xs text-slate-700">
                                                {{ $prize->forLabel() }}
                                                @if ($prize->note)
                                                    <p class="text-[11px] text-slate-400 mb-0 max-w-xs whitespace-normal">{{ $prize->note }}</p>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-800">
                                                    <i class="fa-solid {{ $prize->isCash() ? 'fa-money-bill-wave' : 'fa-id-card' }} text-[#58706D] text-[11px]"></i>
                                                    {{ $prize->summary() }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-bold border {{ $tone }}">{{ $prize->statusLabel() }}</span>
                                            </td>
                                            <td class="text-[11px] text-slate-500">
                                                @if ($prize->isCash() && $prize->status === 'delivered')
                                                    <p class="mb-0 text-slate-700 font-semibold">{{ $prize->payment_method }}</p>
                                                    @if ($prize->payment_reference)<p class="mb-0 font-mono">{{ $prize->payment_reference }}</p>@endif
                                                    <p class="mb-0">{{ $prize->delivered_at?->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y H:i') }} @if ($prize->deliveredBy) by {{ $prize->deliveredBy->name }}@endif</p>
                                                @elseif (! $prize->isCash() && $prize->subscription_id)
                                                    <p class="mb-0">Subscription #{{ $prize->subscription_id }}</p>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-center text-[11px] text-slate-500">
                                                {{ $prize->created_at->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y') }}
                                                @if ($prize->grantedBy)<p class="mb-0">by {{ $prize->grantedBy->name }}</p>@endif
                                            </td>
                                            <td class="text-center">
                                                @if ($prize->isCash() && $prize->status === 'pending')
                                                    <div class="flex items-center justify-center gap-1" x-show="! paying">
                                                        <button type="button" class="btn-brand text-[11px] px-2.5 py-1.5" @click="paying = true">
                                                            <i class="fa-solid fa-check text-[10px]"></i> Mark paid
                                                        </button>
                                                        <form method="POST" action="{{ route('contest-prizes.cancel', $prize) }}"
                                                              onsubmit="return confirm('Cancel this prize? It will no longer count as owed.');">
                                                            @csrf
                                                            <button type="submit" class="action-icon-btn btn-danger-icon" title="Cancel prize">
                                                                <i class="fa-solid fa-xmark text-xs"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <form method="POST" action="{{ route('contest-prizes.paid', $prize) }}" x-show="paying" x-cloak class="space-y-1.5 text-left">
                                                        @csrf
                                                        <select name="payment_method" required class="form-select text-[11px] w-full">
                                                            <option value="">Sent via...</option>
                                                            @foreach ($methods as $method)
                                                                <option value="{{ $method }}">{{ $method }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="text" name="payment_reference" class="form-control text-[11px] w-full" placeholder="Reference (optional)">
                                                        <div class="flex gap-1">
                                                            <button type="submit" class="btn-brand text-[11px] px-2.5 py-1">Save</button>
                                                            <button type="button" class="btn-brand-outline text-[11px] px-2.5 py-1" @click="paying = false">Back</button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <span class="text-slate-300 text-xs">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="p-0">
                                                <x-empty-state
                                                    title="No Prizes Yet"
                                                    description="Open Contest Rankings and press Reward next to a student."
                                                    icon="fa-solid fa-gift"
                                                />
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($prizes->hasPages())
                            <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                                <div class="text-xs text-slate-500">Showing {{ $prizes->firstItem() }} to {{ $prizes->lastItem() }} of {{ $prizes->total() }} prizes</div>
                                <div>{{ $prizes->links() }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
