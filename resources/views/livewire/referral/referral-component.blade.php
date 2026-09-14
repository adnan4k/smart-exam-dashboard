<div>
    <livewire:referral.form />
    <livewire:referral.referral-setting.form />

    <!-- Referrals Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="border-b border-slate-100 p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0">
                                <i class="fa-solid fa-gift"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Referrals &amp; Growth</h5>
                                <p class="text-xs text-slate-400 mb-0">Track user invitations, commission rewards, and disbursement status.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('referral-setting') }}" class="btn-brand-outline text-xs px-3.5 py-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-sliders text-xs"></i> Referral Rules
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 w-full">
                            <thead>
                                <tr>
                                    <th class="text-center w-12">#</th>
                                    <th>Referrer (Inviter)</th>
                                    <th>Referred Candidate</th>
                                    <th class="text-center">Bonus Amount</th>
                                    <th class="text-center">Payment Status</th>
                                    <th class="text-center">Date Logged</th>
                                    <th class="text-center w-36">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($referrals as $num => $referral)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $referrals->firstItem() ? $referrals->firstItem() + $num : $num + 1 }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full bg-[#58706D]/10 text-[#58706D] font-bold text-xs flex items-center justify-center flex-shrink-0 uppercase">
                                                    {{ substr($referral->referrer->name ?? 'U', 0, 2) }}
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $referral->referrer->name ?? 'Deleted User' }}</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">{{ $referral->referrer->phone_number ?? $referral->referrer->email ?? 'No contact' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center flex-shrink-0 uppercase">
                                                    {{ substr($referral->referred->name ?? 'U', 0, 2) }}
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $referral->referred->name ?? 'Deleted User' }}</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">{{ $referral->referred->phone_number ?? $referral->referred->email ?? 'No contact' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-[#58706D] border border-emerald-100">
                                                <span>{{ number_format($referral->bonus_amount, 2) }}</span>
                                                <span class="text-[10px] font-medium text-[#7C8A6E]">ETB</span>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($referral->is_paid)
                                                <span class="badge-subtle-success">Paid Out</span>
                                            @else
                                                <span class="badge-subtle-amber">Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-medium text-slate-600 mb-0">{{ $referral->created_at->format('M d, Y') }}</p>
                                            <p class="text-[11px] text-slate-400 mb-0">{{ $referral->created_at->diffForHumans() }}</p>
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="togglePaymentStatus({{ $referral->id }})"
                                                    class="text-xs font-semibold px-3 py-1.5 rounded-lg transition {{ $referral->is_paid ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' : 'bg-emerald-50 text-[#58706D] hover:bg-emerald-100 border border-emerald-200' }}">
                                                {{ $referral->is_paid ? 'Mark Pending' : 'Mark Paid' }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-12">
                                            <x-empty-state 
                                                title="No referrals found" 
                                                subtitle="User referrals and invite activity will show up here as candidates share their links."
                                                icon="fa-solid fa-user-group" 
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($referrals->hasPages())
                        <div class="px-5 py-3 border-t border-slate-100 flex justify-center">
                            {{ $referrals->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
