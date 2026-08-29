<div class="main-content">
    <livewire:videos.form />

    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <h5 class="mb-0">All Videos</h5>
                        <button style="background-color:#56C596;"
                                @click="$dispatch('videoModal')"
                                class="btn text-white btn-sm mb-0"
                                type="button">+&nbsp; New Video</button>
                    </div>

                    <!-- Filters -->
                    <div class="row mt-3">
                        <div class="col-md-3 mb-2">
                            <input wire:model.live.debounce.400ms="search" type="text"
                                   class="form-control form-control-sm" placeholder="Search title or description...">
                        </div>
                        <div class="col-md-3 mb-2">
                            <select wire:model.live="filterTypeId" class="form-control form-control-sm">
                                <option value="">All Exam Types</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <select wire:model.live="filterSubjectId" class="form-control form-control-sm">
                                <option value="">All Subjects</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 d-flex gap-2">
                            <select wire:model.live="filterChapterId" class="form-control form-control-sm">
                                <option value="">All Chapters</option>
                                @foreach ($chapters as $chapter)
                                    <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                @endforeach
                            </select>
                            <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary mb-0" type="button">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Video</th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Subject</th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Chapter</th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Size</th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($videos as $index => $video)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $videos->firstItem() + $index }}</p>
                                        </td>
                                        <td>
                                            <div class="d-flex px-2 py-1 align-items-center">
                                                @if ($video->thumbnail_url)
                                                    <img src="{{ $video->thumbnail_url }}" class="me-2 rounded"
                                                         style="width:60px;height:40px;object-fit:cover;" alt="thumbnail">
                                                @else
                                                    <div class="me-2 rounded d-flex align-items-center justify-content-center"
                                                         style="width:60px;height:40px;background:#eef2f1;">
                                                        <i class="fas fa-video text-secondary"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $video->title }}</p>
                                                    <p class="text-xs text-secondary mb-0">
                                                        {{ $video->type?->name ?? 'All types' }}
                                                        @if ($video->duration)
                                                            · {{ gmdate($video->duration >= 3600 ? 'H:i:s' : 'i:s', $video->duration) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $video->subject?->name ?? '—' }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $video->chapter?->name ?? '—' }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $video->file_size ? number_format($video->file_size / 1048576, 1) . ' MB' : '—' }}
                                            </p>
                                            @unless ($video->fileExists())
                                                <span class="badge badge-sm bg-gradient-danger">File missing</span>
                                            @endunless
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="toggleActive({{ $video->id }})"
                                                    class="btn btn-link p-0 mb-0"
                                                    title="{{ $video->is_active ? 'Hide from app' : 'Publish to app' }}">
                                                <span class="badge badge-sm {{ $video->is_active ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                    {{ $video->is_active ? 'Visible' : 'Hidden' }}
                                                </span>
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="play({{ $video->id }})" class="btn btn-sm text-success mb-0" title="Preview">
                                                <i class="fa-solid fa-play"></i>
                                            </button>
                                            <button wire:click="editVideo({{ $video->id }})" class="btn btn-sm text-primary mb-0" title="Edit">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button wire:click="confirmDelete({{ $video->id }})" class="btn btn-sm text-danger mb-0" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <p class="text-muted mb-0">No videos found.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center px-4 py-3">
                        <p class="text-sm text-muted mb-0">
                            Showing {{ $videos->firstItem() ?? 0 }} to {{ $videos->lastItem() ?? 0 }} of {{ $videos->total() }} results
                        </p>
                        <div>{{ $videos->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    @if ($showPlayerModal && $videoToPlay)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $videoToPlay->title }}</h5>
                        <button type="button" class="close btn btn-link" wire:click="closePlayer">&times;</button>
                    </div>
                    <div class="modal-body">
                        @if ($videoToPlay->fileExists())
                            <video src="{{ route('videos.preview', $videoToPlay) }}" controls class="w-100 rounded"></video>
                            <p class="text-xs text-secondary mt-2 mb-0">
                                {{ number_format($videoToPlay->file_size / 1048576, 1) }} MB
                                @if ($videoToPlay->checksum) · MD5 {{ $videoToPlay->checksum }} @endif
                            </p>
                        @else
                            <p class="text-muted mb-0">The video file is missing from storage.</p>
                        @endif

                        @if ($videoToPlay->description)
                            <p class="text-sm text-secondary mt-3 mb-0">{{ $videoToPlay->description }}</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePlayer">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Confirm Video Deletion
                        </h5>
                        <button type="button" class="close btn btn-link" wire:click="cancelDelete">&times;</button>
                    </div>
                    <div class="modal-body">
                        @if ($videoToDelete)
                            <div class="alert alert-warning">
                                <strong>Warning!</strong> This also deletes the uploaded file and cannot be undone.
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $videoToDelete->title }}</h6>
                                    <p class="card-text text-sm mb-0">
                                        <strong>Subject:</strong> {{ $videoToDelete->subject?->name ?? 'N/A' }}<br>
                                        <strong>Chapter:</strong> {{ $videoToDelete->chapter?->name ?? 'N/A' }}<br>
                                        <strong>Size:</strong> {{ $videoToDelete->file_size ? number_format($videoToDelete->file_size / 1048576, 1) . ' MB' : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">Cancel</button>
                        <button type="button" class="btn btn-danger" wire:click="deleteVideo">
                            <i class="fas fa-trash"></i> Delete Video
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
