<div class="main-content">
    <livewire:notes.form />

    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Notes</h5>
                        </div>
                        <button
                            style="background-color:#56C596;"
                            @click="$dispatch('noteModal')"
                            class="btn text-white bg-green-400 btn-sm mb-0"
                            type="button">+&nbsp; New Note</button>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        #
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Title
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Subject
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Chapter
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Grade
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notes as $index => $note)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $notes->firstItem() + $index }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $note->title }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ optional($note->subject)->name }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ optional($note->chapter)->name }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $note->grade ?? '-' }}</p>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                wire:click="editNote({{ $note->id }})"
                                                class="text-blue-500">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button
                                                wire:click="confirmDelete({{ $note->id }})"
                                                class="btn btn-sm text-danger"
                                                data-bs-toggle="tooltip" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <p class="text-muted mb-0">No notes found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center px-4 py-3">
                        <div>
                            <p class="text-sm text-muted mb-0">
                                Showing {{ $notes->firstItem() ?? 0 }} to {{ $notes->lastItem() ?? 0 }} of {{ $notes->total() }} results
                            </p>
                        </div>
                        <div>
                            {{ $notes->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Confirm Note Deletion
                        </h5>
                        <button type="button" class="close" wire:click="cancelDelete">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($noteToDelete)
                            <div class="alert alert-warning">
                                <strong>Warning!</strong> This action cannot be undone.
                            </div>
                            <p>Are you sure you want to delete the following note?</p>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $noteToDelete->title }}</h6>
                                    <p class="card-text">
                                        <strong>Subject:</strong> {{ optional($noteToDelete->subject)->name ?? 'N/A' }}<br>
                                        <strong>Chapter:</strong> {{ optional($noteToDelete->chapter)->name ?? 'N/A' }}<br>
                                        <strong>Grade:</strong> {{ $noteToDelete->grade ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteNote">
                            <i class="fas fa-trash"></i> Delete Note
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div> 