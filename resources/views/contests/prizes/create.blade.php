<x-layouts.app>
    @include('contests.partials.pz-styles')

    @php
        $packageDays = $packages->pluck('duration_days', 'id');
        $oldKind     = old('kind', 'subscription');
        $oldPackage  = old('package_id', $packages->first()?->id);
    @endphp

    <div class="pz">
        <a href="{{ route('contest-rankings.index') }}" class="pz-back"><i class="fa-solid fa-arrow-left"></i> Contest Rankings</a>

        @include('contests.partials.pz-flash')

        <div class="pz-layout">
            {{-- Student --}}
            <div class="pz-stack">
                <section class="pz-card">
                    <div class="pz-card-body">
                        <div class="pz-person" style="margin-bottom: 18px;">
                            <div class="pz-avatar is-lg">{{ mb_substr($user->name, 0, 2) }}</div>
                            <div style="min-width: 0;">
                                <p class="pz-title">{{ $user->name }}</p>
                                <p class="pz-sub"><span class="pz-stars"><i class="fa-solid fa-star"></i> {{ number_format($user->total_stars) }}</span> stars in total</p>
                            </div>
                        </div>
                        @include('contests.prizes.partials.contact', ['user' => $user])
                    </div>
                </section>

                <section class="pz-card">
                    <div class="pz-card-body">
                        <span class="pz-label">Active subscriptions</span>
                        <ul class="pz-list" style="margin-bottom: 16px;">
                            @forelse ($user->subscriptions as $sub)
                                <li>
                                    <span class="pz-strong">{{ $sub->package?->name ?? 'Legacy (exam type)' }}</span>
                                    <span class="pz-muted">until {{ \Illuminate\Support\Carbon::parse($sub->end_date)->format('M d, Y') }}</span>
                                </li>
                            @empty
                                <li class="pz-faint">None.</li>
                            @endforelse
                        </ul>

                        <span class="pz-label">Prizes already given</span>
                        <ul class="pz-list">
                            @forelse ($previous as $p)
                                <li>
                                    <span class="pz-strong">{{ $p->summary() }}</span>
                                    <span class="pz-muted">· {{ $p->forLabel() }} · {{ $p->created_at->format('M d, Y') }}</span>
                                    <span class="pz-pill is-sm {{ $p->status === 'pending' ? 'is-amber' : ($p->status === 'cancelled' ? 'is-muted' : 'is-green') }}">{{ $p->statusLabel() }}</span>
                                </li>
                            @empty
                                <li class="pz-faint">None yet.</li>
                            @endforelse
                        </ul>
                    </div>
                </section>
            </div>

            {{-- Form --}}
            <section class="pz-card" style="margin-bottom: 0;">
                <header class="pz-card-head">
                    <h2 class="pz-title">Give a prize</h2>
                    <p class="pz-sub">A subscription unlocks immediately. Money is tracked until you mark it paid.</p>
                </header>

                <form method="POST" action="{{ route('contest-prizes.store') }}" class="pz-card-body pz-fields"
                      x-data="{
                          kind: @js($oldKind),
                          packageId: @js((string) $oldPackage),
                          days: @js(old('subscription_days', $packageDays[$oldPackage] ?? 30)),
                          paidNow: @js((bool) old('paid_now')),
                          defaults: @js($packageDays),
                      }">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $user->id }}">

                    <div class="pz-row is-2-1">
                        <div>
                            <label class="pz-field-label" for="pz-contest">Awarded for</label>
                            <select id="pz-contest" name="contest_id" class="pz-select">
                                <option value="">Overall ranking (all contests)</option>
                                @foreach ($attempts as $a)
                                    <option value="{{ $a->contest_id }}" @selected((int) old('contest_id', $contestId) === $a->contest_id)>
                                        {{ $a->contest->title }}{{ $a->rank && $a->contest->status === 'finalized' ? ' — placed #' . $a->rank : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="pz-field-label" for="pz-rank">Rank</label>
                            <input id="pz-rank" type="number" min="1" name="rank" value="{{ old('rank', $rank) }}" class="pz-input" placeholder="e.g. 1">
                        </div>
                    </div>

                    <div>
                        <span class="pz-field-label">Prize</span>
                        <div class="pz-choice">
                            @foreach ([
                                'subscription' => ['Subscription', 'Free access to a package', 'fa-id-card'],
                                'cash'         => ['Money', 'Paid outside the app', 'fa-money-bill-wave'],
                            ] as $value => [$label, $desc, $icon])
                                <label class="pz-choice-option" :class="kind === '{{ $value }}' && 'is-on'">
                                    <input type="radio" name="kind" value="{{ $value }}" x-model="kind">
                                    <span class="pz-choice-icon"><i class="fa-solid {{ $icon }}"></i></span>
                                    <span style="min-width: 0;">
                                        <span class="pz-choice-title" style="display: block;">{{ $label }}</span>
                                        <span class="pz-choice-desc" style="display: block;">{{ $desc }}</span>
                                    </span>
                                    <i class="fa-solid fa-circle-check pz-choice-check" x-show="kind === '{{ $value }}'"></i>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Subscription --}}
                    <div x-show="kind === 'subscription'">
                        <div class="pz-row is-2-1">
                            <div>
                                <label class="pz-field-label" for="pz-package">Package</label>
                                <select id="pz-package" name="package_id" x-model="packageId" @change="days = defaults[packageId] ?? days"
                                        class="pz-select" :disabled="kind !== 'subscription'">
                                    @foreach ($packages as $package)
                                        <option value="{{ $package->id }}">{{ $package->name }} ({{ number_format($package->price) }} ETB value)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="pz-field-label" for="pz-days">Days</label>
                                <input id="pz-days" type="number" min="1" max="3650" name="subscription_days" x-model="days"
                                       class="pz-input" :disabled="kind !== 'subscription'">
                            </div>
                        </div>
                        <p class="pz-field-help">If the student already has this package active, the days are added to the end of it.</p>
                    </div>

                    {{-- Money --}}
                    <div x-show="kind === 'cash'" x-cloak class="pz-fields">
                        <div>
                            <label class="pz-field-label" for="pz-amount">Amount (ETB)</label>
                            <input id="pz-amount" type="number" min="1" step="0.01" name="amount" value="{{ old('amount') }}"
                                   class="pz-input" :disabled="kind !== 'cash'" placeholder="e.g. 500">
                        </div>

                        <label class="pz-check">
                            <input type="checkbox" name="paid_now" value="1" x-model="paidNow" :disabled="kind !== 'cash'">
                            Already paid
                            <span class="pz-faint" style="font-weight: 400;">— otherwise it is saved as awaiting payment</span>
                        </label>

                        <div class="pz-row is-1-1" x-show="paidNow" x-cloak>
                            <div>
                                <label class="pz-field-label" for="pz-method">Sent via</label>
                                <select id="pz-method" name="payment_method" class="pz-select" :disabled="kind !== 'cash' || ! paidNow">
                                    <option value="">Choose…</option>
                                    @foreach ($methods as $method)
                                        <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="pz-field-label" for="pz-ref">Transaction reference</label>
                                <input id="pz-ref" type="text" name="payment_reference" value="{{ old('payment_reference') }}"
                                       class="pz-input" :disabled="kind !== 'cash' || ! paidNow" placeholder="Optional">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="pz-field-label" for="pz-note">Note</label>
                        <textarea id="pz-note" name="note" rows="2" class="pz-textarea" placeholder="Optional, e.g. agreed on the phone on Nov 3">{{ old('note') }}</textarea>
                    </div>

                    <div class="pz-actions">
                        <a href="{{ route('contest-rankings.index') }}" class="pz-btn pz-btn-quiet">Cancel</a>
                        <button type="submit" class="pz-btn pz-btn-primary"><i class="fa-solid fa-gift"></i> Give prize</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-layouts.app>
