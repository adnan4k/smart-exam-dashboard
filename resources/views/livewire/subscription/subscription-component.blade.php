<div>
    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Subscriptions & Payments</h5>
                            <p class="text-xs text-slate-400 mb-0">Verify payment receipts, subscription plans, and transaction statuses.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#58706D]">
                                Total Subscriptions: {{ $subscriptions->total() }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Body & Table -->
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 w-full">
                            <thead>
                                <tr>
                                    <th class="text-center w-12">#</th>
                                    <th>Candidate / User</th>
                                    <th class="text-center">Plan Tier</th>
                                    <th class="text-center">Fee Amount</th>
                                    <th class="text-center">Payment Proof</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center w-28">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($subscriptions as $num => $subscription)
                                    <tr>
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $subscriptions->firstItem() + $num }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#58706D] to-[#7C8A6E] text-white flex items-center justify-center font-bold text-xs shadow-xs flex-shrink-0">
                                                    {{ strtoupper(substr(optional($subscription->user)->name ?: 'U', 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">
                                                        {{ optional($subscription->user)->name ?: 'Unknown Candidate' }}
                                                    </p>
                                                    <p class="text-[11px] text-slate-400 mb-0">
                                                        UID: #{{ optional($subscription->user)->id ?: 'N/A' }} &bull; {{ optional($subscription->user)->email ?: '' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($subscription->package)
                                                @php
                                                    $pkgBadge = match($subscription->package->slug) {
                                                        'semester_1' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                        'semester_2' => 'bg-teal-50 text-teal-700 border border-teal-200',
                                                        'coc' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                        'all_access' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                                    };
                                                @endphp
                                                <div class="flex flex-col items-center gap-0.5">
                                                    <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $pkgBadge }}">
                                                        {{ $subscription->package->name }}
                                                    </span>
                                                    @if(optional($subscription->user)->type)
                                                        <span class="text-[10px] text-slate-400 font-medium">
                                                            {{ $subscription->user->type->name }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @elseif(optional($subscription->user)->type)
                                                <span class="badge-subtle-brand">{{ $subscription->user->type->name }}</span>
                                            @else
                                                <span class="text-xs text-slate-400">Standard Tier</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-[#58706D]">
                                                ETB {{ number_format($subscription->amount ?? 0, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($subscription->image)
                                                <button wire:click="showImage({{ $subscription->id }})" class="group relative inline-block focus:outline-none">
                                                    <img src="{{ asset('storage/'.$subscription->image) }}"
                                                         alt="Payment Receipt"
                                                         class="w-11 h-11 object-cover rounded-lg border border-slate-200 shadow-xs group-hover:scale-105 transition-transform duration-200" />
                                                    <div class="absolute inset-0 bg-slate-900/40 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <i class="fas fa-eye text-white text-xs"></i>
                                                    </div>
                                                </button>
                                            @else
                                                <span class="text-[11px] text-slate-400 italic">No receipt</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($subscription->payment_status === 'paid')
                                                <span class="badge-subtle-success">Paid</span>
                                            @elseif($subscription->payment_status === 'pending')
                                                <span class="badge-subtle-amber">Pending Verification</span>
                                            @elseif($subscription->payment_status === 'failed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-600">
                                                    Failed
                                                </span>
                                            @else
                                                <span class="badge-subtle-brand">{{ ucfirst($subscription->payment_status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button
                                                wire:click="edit({{ $subscription->id }})"
                                                class="btn-brand-outline text-xs px-3 py-1.5 inline-flex items-center gap-1.5"
                                                title="Review & Update Status">
                                                <i class="fa-solid fa-file-invoice-dollar text-xs"></i> Review
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-0">
                                            <x-empty-state
                                                title="No Subscriptions Recorded"
                                                description="Candidate enrollment payments and receipts will show up here."
                                                icon="fas fa-receipt"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($subscriptions->hasPages())
                        <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                            <div class="text-xs text-slate-500">
                                Showing {{ $subscriptions->firstItem() }} to {{ $subscriptions->lastItem() }} of {{ $subscriptions->total() }} records
                            </div>
                            <div>
                                {{ $subscriptions->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for updating subscription status or viewing payment proof -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden animate-fade-in">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Verify Payment</h3>
                        <p class="text-xs text-slate-400 mb-0">Confirm candidate payment receipt and activate access.</p>
                    </div>
                    <button wire:click="$set('showModal', false)" type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    @if(isset($selectedSubscription) && $selectedSubscription->image)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Attached Receipt</label>
                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 p-2 text-center">
                                <img src="{{ asset('storage/'.$selectedSubscription->image) }}" alt="Payment Proof" class="max-h-56 mx-auto rounded-lg object-contain shadow-xs" />
                            </div>
                        </div>
                    @endif
                    @if(isset($selectedSubscription))
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 text-xs space-y-1.5">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Candidate:</span>
                                <span class="font-bold text-slate-800">{{ optional($selectedSubscription->user)->name ?: 'Unknown' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Target Package:</span>
                                <span class="font-bold text-[#58706D]">
                                    {{ optional($selectedSubscription->package)->name ?: (optional(optional($selectedSubscription->user)->type)->name ?: 'Standard Exam Access') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-400">Payment Amount:</span>
                                <span class="font-bold text-slate-800">ETB {{ number_format($selectedSubscription->amount ?? 0, 2) }}</span>
                            </div>
                        </div>
                    @endif

                    <form wire:submit.prevent="updateStatus">
                        <div class="space-y-3">
                            <div>
                                <label for="selectedStatus" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Verification Decision</label>
                                <select id="selectedStatus" class="form-select w-full" wire:model="selectedStatus">
                                    <option value="pending">Pending (Awaiting verification)</option>
                                    <option value="paid">Paid (Approve & unlock candidate)</option>
                                    <option value="failed">Failed (Declined / invalid proof)</option>
                                </select>
                                @error('selectedStatus')
                                    <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-2.5 pt-5 mt-5 border-t border-slate-100">
                            <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="$set('showModal', false)">
                                Cancel
                            </button>
                            <button type="submit" class="btn-brand text-xs px-5 py-2 shadow-sm">
                                <span wire:loading.remove wire:target="updateStatus">
                                    <i class="fas fa-check text-xs"></i> Update Verification
                                </span>
                                <span wire:loading wire:target="updateStatus" class="flex items-center gap-1.5">
                                    <i class="fas fa-spinner fa-spin text-xs"></i> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Full-screen Image Modal -->
    @if($showImageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-4 overflow-hidden border border-slate-100">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <h5 class="text-sm font-bold text-slate-800 mb-0">Payment Receipt Preview</h5>
                    <button type="button" wire:click="$set('showImageModal', false)" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <div class="max-h-[70vh] overflow-auto flex items-center justify-center bg-slate-50 rounded-xl p-2">
                    <img src="{{ asset('storage/'.$fullScreenImage) }}" alt="Receipt Enlarged" class="max-h-[65vh] w-auto object-contain rounded-lg shadow-sm">
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" wire:click="$set('showImageModal', false)" class="btn-brand-outline text-xs px-4 py-2">
                        Close Preview
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>