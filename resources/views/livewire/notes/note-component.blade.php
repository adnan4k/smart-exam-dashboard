<div>
    <livewire:notes.form />

    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Study Notes & Reference Materials</h5>
                            <p class="text-xs text-slate-400 mb-0">Create and curate comprehensive curriculum summaries with rich mathematical notation.</p>
                        </div>
                        <button
                            @click="$dispatch('noteModal')"
                            class="btn-brand self-start sm:self-auto shadow-sm"
                            type="button">
                            <i class="fa-solid fa-plus text-xs"></i> New Note
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
                                    <th>Note Title</th>
                                    <th class="text-center">Subject</th>
                                    <th class="text-center">Chapter</th>
                                    <th class="text-center">Grade</th>
                                    <th class="text-center w-24">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notes as $index => $note)
                                    <tr>
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $notes->firstItem() + $index }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-xs">
                                                    <i class="fa-solid fa-book-open"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $note->title }}</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">Lang: {{ ucfirst($note->language ?? 'english') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($note->subject)
                                                <span class="badge-subtle-brand">{{ $note->subject->name }}</span>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs text-slate-600 font-medium">
                                                {{ optional($note->chapter)->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($note->grade)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                                                    Grade {{ $note->grade }}
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button
                                                    wire:click="editNote({{ $note->id }})"
                                                    class="action-icon-btn"
                                                    title="Edit Note">
                                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $note->id }})"
                                                    class="action-icon-btn btn-danger-icon"
                                                    title="Delete Note">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-0">
                                            <x-empty-state
                                                title="No Study Notes Found"
                                                description="Create formatted study notes with formulas and definitions for students."
                                                icon="fa-solid fa-book-open"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($notes->hasPages())
                        <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                            <div class="text-xs text-slate-500">
                                Showing {{ $notes->firstItem() ?? 0 }} to {{ $notes->lastItem() ?? 0 }} of {{ $notes->total() }} results
                            </div>
                            <div>
                                {{ $notes->links() }}
                            </div>
                        </div>
                    @endif
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
                <h5 class="text-base font-bold text-slate-800 mb-1">Delete Note</h5>
                <p class="text-xs text-slate-500 mb-4">
                    Are you sure you want to delete <span class="font-bold text-slate-700">"{{ $noteToDelete->title ?? '' }}"</span>? This will remove the note from candidate view.
                </p>

                @if($noteToDelete)
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-5 text-left text-xs text-slate-600 flex flex-col gap-1">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Subject:</span>
                            <span class="font-medium text-slate-700">{{ optional($noteToDelete->subject)->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Chapter:</span>
                            <span class="font-medium text-slate-700">{{ optional($noteToDelete->chapter)->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="cancelDelete">
                        Cancel
                    </button>
                    <button type="button" class="btn bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg px-4 py-2 flex items-center gap-1.5 transition" wire:click="deleteNote">
                        <i class="fas fa-trash text-[11px]"></i> Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div> 