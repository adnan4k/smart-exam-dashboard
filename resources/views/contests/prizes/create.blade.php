<x-layouts.app>
    @include('contests.partials.theme')

    @php
        $packageDays = $packages->pluck('duration_days', 'id');
        $oldKind     = old('kind', 'subscription');
        $oldPackage  = old('package_id', $packages->first()?->id);
    @endphp

    <div class="ct">
        <div class="row">
            <div class="col-12 col-xl-10">
                @include('contests.partials.flash')

                <div class="mb-3">
                    <a href="{{ route('contest-rankings.index') }}" class="text-xs font-semibold text-[#58706D] hover:underline flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Contest Rankings
                    </a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                    <!-- Student -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm p-4">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full bg-[#58706D]/10 text-[#58706D] font-bold text-sm flex items-center justify-center uppercase">
                                    {{ mb_substr($user->name, 0, 2) }}
                                </div>
                                <div>
                                    <h5 class="font-bold text-slate-800 text-base mb-0 tracking-tight">{{ $user->name }}</h5>
                                    <p class="text-[11px] text-slate-400 mb-0">
                                        <i class="fa-solid fa-star text-amber-500"></i> {{ number_format($user->total_stars) }} stars in total
                                    </p>
                                </div>
                            </div>
                            @include('contests.prizes.partials.contact', ['user' => $user])
                        </div>

                        <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm p-4">
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-2">Active subscriptions</p>
                            @forelse ($user->subscriptions as $sub)
                                <p class="text-xs text-slate-700 mb-1">
                                    <span class="font-semibold">{{ $sub->package?->name ?? 'Legacy (exam type)' }}</span>
                                    <span class="text-slate-400">until {{ \Illuminate\Support\Carbon::parse($sub->end_date)->format('M d, Y') }}</span>
                                </p>
                            @empty
                                <p class="text-xs text-slate-400 mb-0">None.</p>
                            @endforelse

                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mt-3 mb-2">Prizes already given</p>
                            @forelse ($previous as $p)
                                <p class="text-xs text-slate-700 mb-1">
                                    <span class="font-semibold">{{ $p->summary() }}</span>
                                    <span class="text-slate-400">· {{ $p->forLabel() }} · {{ $p->statusLabel() }} · {{ $p->created_at->format('M d, Y') }}</span>
                                </p>
                            @empty
                                <p class="text-xs text-slate-400 mb-0">None yet.</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="lg:col-span-3">
                        <form method="POST" action="{{ route('contest-prizes.store') }}"
                              class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm p-4 space-y-4"
                              x-data="{
                                  kind: @js($oldKind),
                                  packageId: @js((string) $oldPackage),
                                  days: @js(old('subscription_days', $packageDays[$oldPackage] ?? 30)),
                                  paidNow: @js((bool) old('paid_now')),
                                  defaults: @js($packageDays),
                              }">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">

                            <div>
                                <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Give a prize</h5>
                                <p class="text-xs text-slate-400 mb-0">A subscription unlocks immediately. Cash is tracked until you mark it paid.</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="block sm:col-span-2">
                                    <span class="text-xs font-semibold text-slate-600">Awarded for</span>
                                    <select name="contest_id" class="form-select text-xs w-full mt-1">
                                        <option value="">Overall ranking (all contests)</option>
                                        @foreach ($attempts as $a)
                                            <option value="{{ $a->contest_id }}" @selected((int) old('contest_id', $contestId) === $a->contest_id)>
                                                {{ $a->contest->title }}{{ $a->rank && $a->contest->status === 'finalized' ? ' — placed #' . $a->rank : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">Rank</span>
                                    <input type="number" min="1" name="rank" value="{{ old('rank', $rank) }}" class="form-control text-xs w-full mt-1" placeholder="e.g. 1">
                                </label>
                            </div>

                            <!-- Kind -->
                            <div>
                                <span class="text-xs font-semibold text-slate-600">Prize</span>
                                <div class="grid grid-cols-2 gap-2 mt-1">
                                    @foreach (['subscription' => ['Subscription', 'fa-id-card'], 'cash' => ['Money', 'fa-money-bill-wave']] as $value => [$label, $icon])
                                        <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition"
                                               :class="kind === '{{ $value }}' ? 'border-[#58706D] bg-emerald-50/60' : 'border-slate-200 hover:bg-slate-50'">
                                            <input type="radio" name="kind" value="{{ $value }}" x-model="kind" class="sr-only">
                                            <i class="fa-solid {{ $icon }} text-[#58706D]"></i>
                                            <span class="text-xs font-bold text-slate-800">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Subscription -->
                            <div x-show="kind === 'subscription'" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="block sm:col-span-2">
                                    <span class="text-xs font-semibold text-slate-600">Package</span>
                                    <select name="package_id" x-model="packageId" @change="days = defaults[packageId] ?? days"
                                            class="form-select text-xs w-full mt-1" :disabled="kind !== 'subscription'">
                                        @foreach ($packages as $package)
                                            <option value="{{ $package->id }}">{{ $package->name }} ({{ number_format($package->price) }} ETB value)</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">Days</span>
                                    <input type="number" min="1" max="3650" name="subscription_days" x-model="days"
                                           class="form-control text-xs w-full mt-1" :disabled="kind !== 'subscription'">
                                </label>
                                <p class="sm:col-span-3 text-[11px] text-slate-400 mb-0">
                                    If the student already has this package active, the days are added to the end of it.
                                </p>
                            </div>

                            <!-- Cash -->
                            <div x-show="kind === 'cash'" x-cloak class="space-y-3">
                                <label class="block">
                                    <span class="text-xs font-semibold text-slate-600">Amount (ETB)</span>
                                    <input type="number" min="1" step="0.01" name="amount" value="{{ old('amount') }}"
                                           class="form-control text-xs w-full mt-1" :disabled="kind !== 'cash'" placeholder="e.g. 500">
                                </label>

                                <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                                    <input type="checkbox" name="paid_now" value="1" x-model="paidNow" class="form-check-input" :disabled="kind !== 'cash'">
                                    Already paid
                                </label>

                                <div x-show="paidNow" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <label class="block">
                                        <span class="text-xs font-semibold text-slate-600">Sent via</span>
                                        <select name="payment_method" class="form-select text-xs w-full mt-1" :disabled="kind !== 'cash' || ! paidNow">
                                            <option value="">Choose...</option>
                                            @foreach ($methods as $method)
                                                <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="block">
                                        <span class="text-xs font-semibold text-slate-600">Transaction reference</span>
                                        <input type="text" name="payment_reference" value="{{ old('payment_reference') }}"
                                               class="form-control text-xs w-full mt-1" :disabled="kind !== 'cash' || ! paidNow" placeholder="Optional">
                                    </label>
                                </div>
                            </div>

                            <label class="block">
                                <span class="text-xs font-semibold text-slate-600">Note</span>
                                <textarea name="note" rows="2" class="form-control text-xs w-full mt-1" placeholder="Optional, e.g. agreed on the phone on Nov 3">{{ old('note') }}</textarea>
                            </label>

                            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                                <a href="{{ route('contest-rankings.index') }}" class="btn-brand-outline text-xs px-4 py-2">Cancel</a>
                                <button type="submit" class="btn-brand text-xs px-4 py-2 shadow-sm">
                                    <i class="fa-solid fa-gift text-xs"></i> Give prize
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
