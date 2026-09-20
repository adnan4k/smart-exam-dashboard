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
                            <p class="text-xs text-slate-400 mb-0">Manage examination subjects, regions, year levels, test durations, and question counts.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                wire:click="openMergeModal"
                                class="btn-brand-outline text-xs px-3 py-2 flex items-center gap-1.5"
                                type="button"
                                title="Merge similar or duplicate subjects">
                                <i class="fa-solid fa-code-merge text-xs"></i> Merge Subjects
                            </button>
                            <button
                                @click="$dispatch('subjectModal')"
                                class="btn-brand self-start sm:self-auto shadow-sm text-xs px-3.5 py-2 flex items-center gap-1.5"
                                type="button">
                                <i class="fa-solid fa-plus text-xs"></i> New Subject
                            </button>
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
                                    <th>Subject Name</th>
                                    <th class="text-center">Exam Type</th>
                                    <th class="text-center">Access Package</th>
                                    <th class="text-center">Questions</th>
                                    <th class="text-center">Region</th>
                                    <th class="text-center">Duration</th>
                                    <th class="text-center">Year</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center w-28">Action</th>
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
                                            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1 rounded-full {{ $subject->questions_count > 0 ? 'bg-emerald-50 text-[#58706D] border border-emerald-200' : 'bg-slate-100 text-slate-400' }}">
                                                <i class="fa-solid fa-circle-question text-[11px]"></i>
                                                {{ number_format($subject->questions_count) }}
                                            </span>
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
                                                    wire:click="openMergeModal({{ $subject->id }})"
                                                    class="action-icon-btn text-purple-600 hover:bg-purple-50"
                                                    title="Merge similar subjects into this one">
                                                    <i class="fa-solid fa-code-merge"></i>
                                                </button>
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
                                        <td colspan="10" class="p-0">
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

    <!-- Merge Subjects Modal -->
    @if($showMergeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-100 animate-fade-in my-8">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-xs">
                            <i class="fa-solid fa-code-merge"></i>
                        </span>
                        <div>
                            <h5 class="text-base font-bold text-slate-800 mb-0">Merge Similar Subjects</h5>
                            <p class="text-xs text-slate-400 mb-0">Combine duplicate or similar subjects and consolidate all questions into one.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="closeMergeModal" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="py-4 space-y-4">
                    <!-- Target Subject Dropdown -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Destination Subject (Master to Keep) <span class="text-rose-500">*</span>
                        </label>
                        <select wire:model.live="targetSubjectId" class="form-select w-full text-xs font-semibold text-slate-800">
                            <option value="">-- Choose Master Subject --</option>
                            @foreach($allSubjects as $sub)
                                <option value="{{ $sub->id }}">
                                    {{ $sub->name }} (ID #{{ $sub->id }}{{ $sub->year ? ', ' . $sub->year : '' }}) — {{ $sub->questions_count }} questions
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1 mb-0">
                            This subject will be preserved. All questions, notes, videos, and student enrollments will be moved here.
                        </p>
                    </div>

                    <!-- Source Subjects Selection -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-0">
                                Source Subjects to Merge & Remove <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-xs font-semibold text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-full border border-purple-200">
                                {{ count($sourceSubjectIds) }} selected to merge
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mb-2">
                            Select the duplicate or similar subjects that will be absorbed into the destination subject:
                        </p>
                        <div class="max-h-56 overflow-y-auto p-3 bg-slate-50 rounded-xl border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @forelse($allSubjects as $sub)
                                @if((int)$sub->id !== (int)$targetSubjectId)
                                    <label class="flex items-center justify-between p-2.5 bg-white rounded-lg border border-slate-200/80 hover:border-purple-400 cursor-pointer text-xs transition">
                                        <div class="flex items-center gap-2 truncate">
                                            <input
                                                type="checkbox"
                                                wire:model.live="sourceSubjectIds"
                                                value="{{ $sub->id }}"
                                                class="rounded border-slate-300 text-purple-600 focus:ring-purple-500 w-4 h-4">
                                            <div class="truncate">
                                                <span class="font-bold text-slate-800">{{ $sub->name }}</span>
                                                <span class="text-[10px] text-slate-400 block">ID: #{{ $sub->id }}{{ $sub->year ? ' &bull; ' . $sub->year : '' }}</span>
                                            </div>
                                        </div>
                                        <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sub->questions_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400' }}">
                                            {{ $sub->questions_count }} Qs
                                        </span>
                                    </label>
                                @endif
                            @empty
                                <div class="col-span-full py-3 text-center text-xs text-slate-400">
                                    No other subjects found.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Impact Preview Box -->
                    @if($previewData && count($sourceSubjectIds) > 0)
                        <div class="bg-purple-50/70 border border-purple-200 rounded-xl p-3.5 text-xs text-purple-900 space-y-2">
                            <div class="font-bold flex items-center gap-1.5 text-purple-800">
                                <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                                Merge Impact Preview:
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-center py-1">
                                <div class="bg-white/90 p-2 rounded-lg border border-purple-100 shadow-xs">
                                    <span class="block text-base font-black text-purple-800">{{ $previewData['questions_count'] }}</span>
                                    <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Questions to Move</span>
                                </div>
                                <div class="bg-white/90 p-2 rounded-lg border border-purple-100 shadow-xs">
                                    <span class="block text-base font-black text-purple-800">{{ $previewData['notes_count'] }}</span>
                                    <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Notes to Move</span>
                                </div>
                                <div class="bg-white/90 p-2 rounded-lg border border-purple-100 shadow-xs">
                                    <span class="block text-base font-black text-purple-800">{{ $previewData['videos_count'] }}</span>
                                    <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Videos to Move</span>
                                </div>
                            </div>
                            <p class="text-[11px] text-purple-800 font-medium mb-0">
                                <i class="fas fa-info-circle mr-1"></i>
                                All <strong>{{ $previewData['questions_count'] }}</strong> questions from the {{ count($sourceSubjectIds) }} selected source subject(s) will be merged into <strong>"{{ optional($previewData['target'])->name }}"</strong>, and the old source subject entries will be safely deleted.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100">
                    <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="closeMergeModal">
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="btn bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg px-4 py-2 flex items-center gap-1.5 shadow-sm transition disabled:opacity-50"
                        wire:click="executeMerge"
                        {{ empty($sourceSubjectIds) || !$targetSubjectId ? 'disabled' : '' }}>
                        <i class="fa-solid fa-code-merge text-xs"></i> Confirm & Merge Subjects
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>