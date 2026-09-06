<div>
    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">User Management</h5>
                            <p class="text-xs text-slate-400 mb-0">Monitor registered candidates, institution affiliations, access roles, and account status.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#58706D]">
                                Total Users: {{ $users->total() }}
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
                                    <th>User Details</th>
                                    <th>Phone</th>
                                    <th>Institution</th>
                                    <th class="text-center">Exam Type</th>
                                    <th class="text-center">Role</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Last Active</th>
                                    <th class="text-center w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $num => $user)
                                    <tr>
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $users->firstItem() + $num }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#58706D] to-[#7C8A6E] text-white flex items-center justify-center font-bold text-xs shadow-xs flex-shrink-0">
                                                    {{ strtoupper(substr($user->name ?: 'U', 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $user->name }}</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">{{ $user->email }}</p>
                                                    @if($user->referral_code)
                                                        <span class="text-[10px] text-slate-400">Code: <span class="font-mono text-slate-600">{{ $user->referral_code }}</span></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-xs text-slate-600 font-medium">
                                                {{ $user->phone_number ?: '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <p class="text-xs font-medium text-slate-700 mb-0">{{ $user->institution_name ?: 'Self-study' }}</p>
                                                @if($user->institution_type)
                                                    <span class="text-[11px] text-slate-400">({{ $user->institution_type }})</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($user->type)
                                                <span class="badge-subtle-brand">{{ $user->type->name }}</span>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($user->role === 'admin')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-800 text-white">
                                                    Admin
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-600">
                                                    Student
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($user->status === 'active')
                                                <span class="badge-subtle-success">Active</span>
                                            @elseif($user->status === 'inactive')
                                                <span class="badge-subtle-amber">Inactive</span>
                                            @elseif($user->status === 'suspended')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 text-rose-600">
                                                    Suspended
                                                </span>
                                            @else
                                                <span class="badge-subtle-brand">{{ ucfirst($user->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs text-slate-500">
                                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button
                                                    wire:click="edit({{ $user->id }})"
                                                    class="action-icon-btn"
                                                    title="Update Status">
                                                    <i class="fa-solid fa-user-pen text-xs"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $user->id }})"
                                                    class="action-icon-btn btn-danger-icon"
                                                    title="Delete User">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="p-0">
                                            <x-empty-state
                                                title="No Users Registered"
                                                description="No user accounts match the current filters."
                                                icon="fas fa-users"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                        <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                            <div class="text-xs text-slate-500">
                                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                            </div>
                            <div>
                                {{ $users->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for updating user status -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden animate-fade-in">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Update User Status</h3>
                        <p class="text-xs text-slate-400 mb-0">Modify access permissions and account state.</p>
                    </div>
                    <button wire:click="$set('showModal', false)" type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit.prevent="updateStatus">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Account Status</label>
                            <select id="selectedStatus" class="form-select w-full" wire:model="selectedStatus">
                                <option value="active">Active (Full access permitted)</option>
                                <option value="inactive">Inactive (Pending email / activation)</option>
                                <option value="suspended">Suspended (Access blocked)</option>
                            </select>
                            @error('selectedStatus')
                                <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                        <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="$set('showModal', false)">
                            Cancel
                        </button>
                        <button type="submit" class="btn-brand text-xs px-5 py-2 shadow-sm">
                            <span wire:loading.remove wire:target="updateStatus">
                                <i class="fas fa-check text-xs"></i> Save Status
                            </span>
                            <span wire:loading wire:target="updateStatus" class="flex items-center gap-1.5">
                                <i class="fas fa-spinner fa-spin text-xs"></i> Updating...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center animate-fade-in">
                <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fas fa-user-xmark"></i>
                </div>
                <h5 class="text-base font-bold text-slate-800 mb-1">Delete User Account</h5>
                <p class="text-xs text-slate-500 mb-4">
                    Are you sure you want to delete user <span class="font-bold text-slate-700">"{{ $userToDelete->name ?? '' }}"</span>? This will remove all their exam progress and contest participation history.
                </p>

                @if($userToDelete)
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-5 text-left text-xs text-slate-600 flex flex-col gap-1.5">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Email:</span>
                            <span class="font-medium text-slate-700">{{ $userToDelete->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Role:</span>
                            <span class="font-semibold text-slate-700">{{ ucfirst($userToDelete->role) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Institution:</span>
                            <span class="font-medium text-slate-700">{{ $userToDelete->institution_name ?: 'N/A' }}</span>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="cancelDelete">
                        Cancel
                    </button>
                    <button type="button" class="btn bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg px-4 py-2 flex items-center gap-1.5 transition" wire:click="deleteUser">
                        <i class="fas fa-trash text-[11px]"></i> Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>