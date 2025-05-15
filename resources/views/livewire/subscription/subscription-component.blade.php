<div class="main-content">
    <div class="container">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between">
                    <div>
                        <h5 class="mb-0">All Subscriptions</h5>
                    </div>
                </div>
            </div>

            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    ID
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    User
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                    User id
                                </th>

                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Subscription Type
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Amount
                                </th>
                               
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Screenshot
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Payment Status
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subscriptions as $subscription)
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">{{ $subscription->id }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $subscription->user->name ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $subscription->user->id ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $subscription->user->type->name ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $subscription->amount ?? 'N/A' }}
                                        </p>
                                    </td>
                                  
                                    <td>
                                        @if($subscription->image)
                                            <button wire:click="showImage({{ $subscription->id }})" class="relative w-fit">
                                                <img src="{{ asset('storage/'.$subscription->image) }}"
                                                     alt="Payment Proof"
                                                     class="w-14 h-14 object-cover cursor-pointer rounded-lg border border-gray-200 shadow hover:scale-105 transition-transform duration-300"
                                                      />
                                                <!-- Eye icon overlay on hover -->
                                                <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                                    <i class="fas fa-eye text-white text-2xl"></i>
                                                </div>
                                            </button>
                                        @else
                                            <span class="text-xs">No Image</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($subscription->payment_status === 'paid')
                                            <span style="background-color:green " class="inline-block px-2 rounded bg-green-500 text-white px-2 py-1 text-xs font-bold">
                                                {{ ucfirst($subscription->payment_status) }}
                                            </span>
                                        @elseif($subscription->payment_status === 'pending')
                                            <span style="background-color:yellow " class="inline-block px-2 rounded bg-yellow-500 text-white font-bold px-2 py-1 text-xs font-bold">
                                                {{ ucfirst($subscription->payment_status) }}
                                            </span>
                                        @elseif($subscription->payment_status === 'failed')
                                            <span class="inline-block px-2 rounded bg-red-500 text-white px-2 py-1 text-xs font-bold">
                                                {{ ucfirst($subscription->payment_status) }}
                                            </span>
                                        @else
                                            <span style="background-color:green " class="inline-block px-2 rounded bg-gray-500 text-white px-2 py-1 text-xs font-bold">
                                                {{ ucfirst($subscription->payment_status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <button
                                            wire:click="edit({{ $subscription->id }})"
                                            class="btn btn-primary btn-sm">
                                            Update Status
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for updating subscription status or viewing payment proof -->
    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Update Payment Status</h5>
                        <button type="button" class="close" wire:click="$set('showModal', false)">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        @if(isset($selectedSubscription) && $selectedSubscription->image)
                            <div class="mb-3 text-center">
                                <img src="{{ asset($selectedSubscription->image) }}" alt="Payment Proof" class="w-32 h-32 object-cover mx-auto" />
                            </div>
                        @endif
                        <form wire:submit.prevent="updateStatus">
                            <div class="form-group">
                                <label for="selectedStatus">Payment Status</label>
                                <select id="selectedStatus" class="form-control" wire:model="selectedStatus">
                                    <option value="pending">Pending</option>
                                    <option value="paid">Paid</option>
                                    <option value="failed">Failed</option>
                                </select>
                                @error('selectedStatus')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">
                                Update Status
                            </button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" wire:click="$set('showModal', false)">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Full-screen Image Modal -->
    @if($showImageModal)
        <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-75">
            <div class="bg-white rounded shadow-lg p-4 w-54 h-54 flex flex-col">
                <div class="flex-grow flex items-center justify-center">
                    <img src="{{ asset('storage/'.$fullScreenImage) }}" alt="Half Size Image" class="max-h-full max-w-full object-contain">
                </div>
                <div class="mt-4 text-center">
                    <button type="button" wire:click="$set('showImageModal', false)" class="px-4 py-2 bg-gray-800 text-white rounded">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>