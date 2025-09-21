<div class="main-content">
    <div class="container">
        <div class="card mb-4 mx-4">
            <div class="card-header pb-0">
                <div class="d-flex flex-row justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">All Users</h5>
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
                                    Name
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Email
                                </th>
                             
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Phone Number
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Institution
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Type
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Referral
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Role
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Status
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Last Login
                                </th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">{{ $user->id }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $user->name }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $user->email }}
                                        </p>
                                    </td>
                                 
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $user->phone_number }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $user->institution_name }}
                                            @if($user->institution_type)
                                                <br>
                                                <small class="text-muted">({{ $user->institution_type }})</small>
                                            @endif
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $user->type->name ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            Code: {{ $user->referral_code }}
                                            @if($user->referred_by)
                                                <br>
                                                <small class="text-muted">Referred by: {{ $user->referredBy->name ?? 'N/A' }}</small>
                                            @endif
                                        </p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ ucfirst($user->role) }}
                                        </p>
                                    </td>
                                    <td>
                                        @if($user->status === 'active')
                                            <span class="inline-block rounded bg-green-500 text-white px-2 py-1 text-xs font-bold">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        @elseif($user->status === 'inactive')
                                            <span style="background-color:yellow" class="inline-block rounded bg-yellow-500 text-white px-2 py-1 text-xs font-bold">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        @elseif($user->status === 'suspended')
                                            <span class="inline-block rounded bg-red-500 text-white px-2 py-1 text-xs font-bold">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        @else
                                            <span style="background-color:green" class="inline-block rounded bg-green-500 text-white px-2 py-1 text-xs font-bold">
                                                {{ ucfirst($user->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                        </p>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button
                                                wire:click="edit({{ $user->id }})"
                                                class="btn btn-primary btn-sm">
                                                Update Status
                                            </button>
                                            <button
                                                wire:click="confirmDelete({{ $user->id }})"
                                                class="btn btn-danger btn-sm">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($users->isEmpty())
                    <div class="text-center p-3 text-muted">No users found.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal for updating user status -->
    @if($showModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Update User Status</h5>
                        <button type="button" class="close" wire:click="$set('showModal', false)">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="updateStatus">
                            <div class="form-group">
                                <label for="selectedStatus">User Status</label>
                                <select id="selectedStatus" class="form-control" wire:model="selectedStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
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

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Confirm User Deletion
                        </h5>
                        <button type="button" class="close" wire:click="cancelDelete">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($userToDelete)
                            <div class="alert alert-warning">
                                <strong>Warning!</strong> This action cannot be undone.
                            </div>
                            <p>Are you sure you want to delete the following user?</p>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $userToDelete->name }}</h6>
                                    <p class="card-text">
                                        <strong>Email:</strong> {{ $userToDelete->email }}<br>
                                        <strong>Institution:</strong> {{ $userToDelete->institution_name }}<br>
                                        <strong>Role:</strong> {{ ucfirst($userToDelete->role) }}<br>
                                        <strong>Status:</strong> {{ ucfirst($userToDelete->status) }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteUser">
                            <i class="fas fa-trash"></i> Delete User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>