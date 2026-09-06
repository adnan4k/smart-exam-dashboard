<div>
    <livewire:year-groups.form />

    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Academic Year Groups</h5>
                            <p class="text-xs text-slate-400 mb-0">Manage curriculum cohorts, matriculation batches, and question years.</p>
                        </div>
                        <button
                            @click="$dispatch('yearGroupModal')"
                            class="btn-brand self-start sm:self-auto shadow-sm"
                            type="button">
                            <i class="fa-solid fa-plus text-xs"></i> New Year Group
                        </button>
                    </div>
                </div>

                <!-- Card Body & Table -->
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 w-full">
                            <thead>
                                <tr>
                                    <th class="text-center w-12">#</th>
                                    <th>Year Group</th>
                                    <th class="text-center">Registered Date</th>
                                    <th class="text-center w-24">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($yearGroups as $num => $yearGroup)
                                    <tr>
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $num + 1 }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-xs">
                                                    <i class="far fa-calendar-alt"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $yearGroup->year }} Academic Year</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">Cohort ID: #{{ $yearGroup->id }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-600 font-medium">
                                                <i class="far fa-clock text-slate-400 text-[11px]"></i>
                                                {{ $yearGroup->created_at ? $yearGroup->created_at->format('M d, Y') : '—' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button
                                                    @click="$dispatch('edit-yearGroup', { yearGroup: {{ $yearGroup->id }} })"
                                                    class="action-icon-btn"
                                                    title="Edit Year Group">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $yearGroup->id }})"
                                                    class="action-icon-btn btn-danger-icon"
                                                    title="Delete Year Group">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-0">
                                            <x-empty-state
                                                title="No Year Groups Found"
                                                description="Get started by configuring your first academic year group."
                                                icon="far fa-calendar-alt"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center animate-fade-in">
                <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="text-base font-bold text-slate-800 mb-1">Delete Year Group</h5>
                <p class="text-xs text-slate-500 mb-4">
                    Are you sure you want to delete year group <span class="font-bold text-slate-700">"{{ $yearGroupToDelete->year ?? '' }}"</span>? This may affect examination classification filters.
                </p>

                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="cancelDelete">
                        Cancel
                    </button>
                    <button type="button" class="btn bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg px-4 py-2 flex items-center gap-1.5 transition" wire:click="delete">
                        <i class="fas fa-trash text-[11px]"></i> Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>