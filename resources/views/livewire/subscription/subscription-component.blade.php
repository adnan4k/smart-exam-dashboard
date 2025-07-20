<div class="main-content">
    <div class="container">
        <div class="card mb-4 mx-4 shadow-sm border-0">
            <div class="card-header pb-0 bg-white border-bottom">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 text-dark font-weight-bold">All Subscriptions</h5>
                        <p class="text-sm text-muted mb-0">Manage payment statuses and view payment proofs</p>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success text-white px-3 py-2">
                            <i class="fas fa-check-circle me-1"></i>
                            {{ $subscriptions->where('payment_status', 'paid')->count() }} Paid
                        </span>
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="fas fa-clock me-1"></i>
                            {{ $subscriptions->where('payment_status', 'pending')->count() }} Pending
                        </span>
                    </div>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 ps-4">
                                    ID
                                </th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                    User
                                </th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                    User ID
                                </th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                    Subscription Type
                                </th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                    Amount
                                </th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                    Payment Proof
                                </th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">
                                    Status
                                </th>
                                <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 text-center">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subscriptions as $subscription)
                                <tr class="border-bottom">
                                    <td class="ps-4">
                                        <span class="text-xs font-weight-bold text-dark">#{{ $subscription->id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3 bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center">
                                                <span class="text-white text-xs font-weight-bold">
                                                    {{ strtoupper(substr($subscription->user->name ?? 'N/A', 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="text-xs font-weight-bold text-dark">
                                                    {{ $subscription->user->name ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-xs text-muted">{{ $subscription->user->id ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold text-dark">
                                            {{ $subscription->user->type->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold text-success">
                                            ETB {{ number_format($subscription->amount ?? 0) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($subscription->image)
                                            <button wire:click="showImage({{ $subscription->id }})" class="relative w-fit border-0 bg-transparent p-0">
                                                <img src="{{ asset('storage/'.$subscription->image) }}"
                                                     alt="Payment Proof"
                                                     class="w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 object-cover cursor-pointer rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:scale-105 transition-all duration-200"
                                                      />
                                                <!-- Eye icon overlay on hover -->
                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity bg-black bg-opacity-30 rounded-lg">
                                                    <i class="fas fa-eye text-white text-xs"></i>
                                                </div>
                                            </button>
                                        @else
                                            <span class="text-xs text-muted">
                                                <i class="fas fa-image me-1"></i>
                                                No Image
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subscription->payment_status === 'paid')
                                            <span class="badge bg-success text-white px-3 py-2 text-xs font-weight-bold">
                                                <i class="fas fa-check-circle me-1"></i>
                                                Paid
                                            </span>
                                        @elseif($subscription->payment_status === 'pending')
                                            <span class="badge bg-warning text-dark px-3 py-2 text-xs font-weight-bold">
                                                <i class="fas fa-clock me-1"></i>
                                                Pending
                                            </span>
                                        @elseif($subscription->payment_status === 'failed')
                                            <span class="badge bg-danger text-white px-3 py-2 text-xs font-weight-bold">
                                                <i class="fas fa-times-circle me-1"></i>
                                                Failed
                                            </span>
                                        @else
                                            <span class="badge bg-secondary text-white px-3 py-2 text-xs font-weight-bold">
                                                <i class="fas fa-question-circle me-1"></i>
                                                {{ ucfirst($subscription->payment_status ?? 'Unknown') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button
                                            wire:click="edit({{ $subscription->id }})"
                                            class="btn btn-sm btn-outline-primary px-3 py-1 text-xs font-weight-bold hover:bg-primary hover:text-white transition-colors">
                                            <i class="fas fa-edit me-1"></i>
                                            Update
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($subscriptions->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-inbox text-muted text-4xl mb-3"></i>
                        <p class="text-muted">No subscriptions found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal for updating subscription status or viewing payment proof -->
    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Update Payment Status
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="$set('showModal', false)">
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        @if(isset($selectedSubscription) && $selectedSubscription->image)
                            <div class="mb-4 text-center">
                                <img src="{{ asset('storage/'.$selectedSubscription->image) }}" 
                                     alt="Payment Proof" 
                                     class="w-48 h-48 object-cover mx-auto rounded-lg shadow-md border" />
                                <p class="text-sm text-muted mt-2">Payment Proof</p>
                            </div>
                        @endif
                        <form wire:submit.prevent="updateStatus">
                            <div class="form-group">
                                <label for="selectedStatus" class="form-label fw-bold">Payment Status</label>
                                <select id="selectedStatus" class="form-select" wire:model="selectedStatus">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="failed">Failed</option>
                                </select>
                                @error('selectedStatus')
                                    <span class="text-danger text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="d-flex gap-2 mt-4">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-save me-1"></i>
                                    Update Status
                                </button>
                                <button type="button" class="btn btn-secondary" wire:click="$set('showModal', false)">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Half-screen Image Modal -->
    @if($showImageModal)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-50">
            <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-2xl max-h-[80vh] flex flex-col">
                <div class="flex justify-between items-center p-4 border-b bg-primary text-white rounded-t-lg">
                    <h5 class="text-lg font-semibold mb-0">
                        <i class="fas fa-image me-2"></i>
                        Payment Proof
                    </h5>
                    <button type="button" wire:click="$set('showImageModal', false)" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div class="flex-1 p-4 overflow-auto">
                    <div class="flex justify-center">
                        <img src="{{ asset('storage/'.$fullScreenImage) }}" 
                             alt="Payment Proof" 
                             class="max-w-full max-h-full object-contain rounded-lg shadow-md">
                    </div>
                </div>
                <div class="p-4 border-t bg-gray-50 rounded-b-lg">
                    <button type="button" 
                            wire:click="$set('showImageModal', false)" 
                            class="w-full px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark transition-colors">
                        <i class="fas fa-times me-1"></i>
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>