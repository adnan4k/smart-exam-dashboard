<div class="main-content">
    <livewire:notifications.form />

    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Notifications</h5>
                            <p class="text-sm text-muted mb-0">Create and manage announcements sent to mobile users.</p>
                        </div>
                        <button
                            style="background-color:#56C596;"
                            @click="$dispatch('notificationModal')"
                            class="btn text-white bg-green-400 btn-sm mb-0"
                            type="button">+&nbsp; New Notification</button>
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Title
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Created
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Likes
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Dislikes
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Comments
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notifications as $index => $notification)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $index + 1 }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $notification->title }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $notification->created_at->diffForHumans() }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $notification->like_count }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $notification->dislike_count }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $notification->comment_count }}</p>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                wire:click="editNotification({{ $notification->id }})"
                                                class="text-blue-500 me-2">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button
                                                wire:click="confirmDelete({{ $notification->id }})"
                                                class="btn btn-sm text-danger"
                                                data-bs-toggle="tooltip" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                            <button
                                                wire:click="showComments({{ $notification->id }})"
                                                class="btn btn-sm text-secondary ms-1"
                                                data-bs-toggle="tooltip" title="View Comments">
                                                <i class="fa-regular fa-comments"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <span class="text-sm text-muted">No notifications yet.</span>
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

    @if($selectedNotification)
        <div class="row mt-4 mx-1">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Comments for: "{{ $selectedNotification->title }}"</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">
                                            User
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Comment
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedComments as $comment)
                                        <tr>
                                            <td class="ps-3">
                                                <p class="text-xs font-weight-bold mb-0">
                                                    {{ optional($comment->user)->name ?? 'Unknown User' }}
                                                </p>
                                                <p class="text-xxs text-muted mb-0">
                                                    {{ optional($comment->user)->phone_number ?? optional($comment->user)->email }}
                                                </p>
                                            </td>
                                            <td>
                                                <p class="text-xs mb-0">{{ $comment->comment }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs text-muted mb-0">{{ $comment->created_at->diffForHumans() }}</p>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-3">
                                                <span class="text-sm text-muted">No comments yet for this notification.</span>
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
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Confirm Notification Deletion
                        </h5>
                        <button type="button" class="close" wire:click="cancelDelete">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($notificationToDelete)
                            <div class="alert alert-warning">
                                <strong>Warning!</strong> This action cannot be undone.
                            </div>
                            <p>Are you sure you want to delete the following notification?</p>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $notificationToDelete->title }}</h6>
                                    <p class="card-text">
                                        <strong>Body:</strong> {{ Str::limit($notificationToDelete->body, 100) }}<br>
                                        <strong>Created:</strong> {{ $notificationToDelete->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteNotification">
                            <i class="fas fa-trash"></i> Delete Notification
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>