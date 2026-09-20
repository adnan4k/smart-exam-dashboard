<div>
    <livewire:subjects.form />

    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Subject Management</h5>
                            <p class="text-xs text-slate-400 mb-0">Manage examination subjects, regions, year levels, and test durations.</p>
                        </div>
                        <button
                            @click="$dispatch('subjectModal')"
                            class="btn-brand self-start sm:self-auto shadow-sm"
                            type="button">
                            <i class="fa-solid fa-plus text-xs"></i> New Subject
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
                                    <th>Subject Name</th>
                                    <th class="text-center">Exam Type</th>
                                    <th class="text-center">Access Package</th>
                                    <th class="text-center">Region</th>
                                    <th class="text-center">Duration</th>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center w-24">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($subjects as $num => $subject)
                                    <tr>
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $num + 1 }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-xs">
                                                    <i class="fas fa-book"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $subject->name }}</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">ID: #{{ $subject->id }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($subject->type)
                                                <span class="badge-subtle-brand">{{ $subject->type->name }}</span>
                                            @else
                                                <span class="text-xs text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($subject->package)
                                                @php
                                                    $pkgBadge = match($subject->package->slug) {
                                                        'semester_1' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                                        'semester_2' => 'bg-teal-50 text-teal-700 border border-teal-200',
                                                        'coc' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                        'all_access' => 'bg-purple-50 text-purple-700 border border-purple-200',
                                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center text-[11px] font-bold px-2.5 py-0.5 rounded-full {{ $pkgBadge }}">
                                                    {{ $subject->package->name }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center text-[11px] font-medium px-2 py-0.5 rounded-full bg-slate-100 text-slate-400">
                                                    Open / Free
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($subject->region)
                                                <span class="badge-subtle-amber capitalize">{{ str_replace('_', ' ', $subject->region) }}</span>
                                            @else
                                                <span class="text-xs text-slate-400">National</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-600 font-medium">
                                                <i class="far fa-clock text-slate-400 text-[11px]"></i>
                                                {{ $subject->default_duration }} min
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                                {{ $subject->year ?: '—' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($subject->is_sample)
                                                <span class="badge-subtle-success">Sample</span>
                                            @else
                                                <span class="text-xs text-slate-400">Regular</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button
                                                    @click="$dispatch('edit-subject', { subject: {{ $subject->id }} })"
                                                    class="action-icon-btn"
                                                    title="Edit Subject">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $subject->id }})"
                                                    class="action-icon-btn btn-danger-icon"
                                                    title="Delete Subject">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-0">
                                            <x-empty-state
                                                title="No Subjects Found"
                                                description="Get started by clicking '+ New Subject' to create your first exam subject."
                                                icon="fas fa-book-open"
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
                <h5 class="text-base font-bold text-slate-800 mb-1">Delete Subject</h5>
                <p class="text-xs text-slate-500 mb-4">
                    Are you sure you want to delete <span class="font-bold text-slate-700">"{{ $subjectToDelete->name ?? '' }}"</span>? This will permanently remove it along with all associated curriculum mappings.
                </p>

                @if($subjectToDelete)
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-5 text-left text-xs text-slate-600 flex flex-col gap-1.5">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Exam Type:</span>
                            <span class="font-semibold text-slate-700">{{ optional($subjectToDelete->type)->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Year Group:</span>
                            <span class="font-semibold text-slate-700">{{ $subjectToDelete->year ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="cancelDelete">
                        Cancel
                    </button>
                    <button type="button" class="btn bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg px-4 py-2 flex items-center gap-1.5 transition" wire:click="deleteSubject">
                        <i class="fas fa-trash text-[11px]"></i> Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>