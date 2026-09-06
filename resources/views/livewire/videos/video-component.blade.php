<div>
    <livewire:videos.form />

    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Video Lessons & Tutorials</h5>
                            <p class="text-xs text-slate-400 mb-0">Upload and curate chapter-based video explanations, lecture durations, and student access.</p>
                        </div>
                        <button
                            @click="$dispatch('videoModal')"
                            class="btn-brand self-start sm:self-auto shadow-sm"
                            type="button">
                            <i class="fa-solid fa-plus text-xs"></i> New Video
                        </button>
                    </div>

                    <!-- Filters Toolbar -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mt-4 pt-3 border-t border-slate-100">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input wire:model.live.debounce.400ms="search" type="text"
                                   class="form-control text-xs w-full" style="padding-left: 2rem !important;" placeholder="Search title or description...">
                        </div>
                        <div>
                            <select wire:model.live="filterTypeId" class="form-select text-xs w-full">
                                <option value="">All Exam Types</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select wire:model.live="filterSubjectId" class="form-select text-xs w-full">
                                <option value="">All Subjects</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <select wire:model.live="filterChapterId" class="form-select text-xs flex-1">
                                <option value="">All Chapters</option>
                                @foreach ($chapters as $chapter)
                                    <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                @endforeach
                            </select>
                            <button wire:click="clearFilters" class="btn-brand-outline text-xs px-3 py-2 flex-shrink-0" type="button">
                                Reset
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
                                    <th>Video Details</th>
                                    <th class="text-center">Subject</th>
                                    <th class="text-center">Chapter</th>
                                    <th class="text-center">Size</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center w-28">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($videos as $index => $video)
                                    <tr>
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $videos->firstItem() + $index }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3">
                                                @if ($video->thumbnail_url)
                                                    <img src="{{ $video->thumbnail_url }}" class="w-14 h-10 object-cover rounded-lg border border-slate-200 shadow-xs flex-shrink-0" alt="thumbnail">
                                                @else
                                                    <div class="w-14 h-10 rounded-lg bg-emerald-50 text-[#58706D] flex items-center justify-center flex-shrink-0">
                                                        <i class="fas fa-video text-xs"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $video->title }}</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">
                                                        {{ $video->type?->name ?? 'All Types' }}
                                                        @if ($video->duration)
                                                            &bull; {{ gmdate($video->duration >= 3600 ? 'H:i:s' : 'i:s', $video->duration) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($video->subject)
                                                <span class="badge-subtle-brand">{{ $video->subject->name }}</span>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs text-slate-600 font-medium">
                                                {{ $video->chapter?->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-medium text-slate-700 mb-0">
                                                {{ $video->file_size ? number_format($video->file_size / 1048576, 1) . ' MB' : '—' }}
                                            </p>
                                            @unless ($video->fileExists())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 mt-0.5">Missing</span>
                                            @endunless
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="toggleActive({{ $video->id }})"
                                                    class="focus:outline-none transition"
                                                    title="{{ $video->is_active ? 'Hide from app' : 'Publish to app' }}">
                                                @if($video->is_active)
                                                    <span class="badge-subtle-success cursor-pointer">Visible</span>
                                                @else
                                                    <span class="badge-subtle-amber cursor-pointer">Hidden</span>
                                                @endif
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="play({{ $video->id }})" class="action-icon-btn text-[#58706D]" title="Preview Video">
                                                    <i class="fa-solid fa-play text-xs"></i>
                                                </button>
                                                <button wire:click="editVideo({{ $video->id }})" class="action-icon-btn" title="Edit Video">
                                                    <i class="fa-regular fa-pen-to-square text-xs"></i>
                                                </button>
                                                <button wire:click="confirmDelete({{ $video->id }})" class="action-icon-btn btn-danger-icon" title="Delete Video">
                                                    <i class="fa-solid fa-trash text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-0">
                                            <x-empty-state
                                                title="No Video Lessons Found"
                                                description="Upload instructional video lectures to guide students through chapters."
                                                icon="fas fa-video"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($videos->hasPages())
                        <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                            <div class="text-xs text-slate-500">
                                Showing {{ $videos->firstItem() ?? 0 }} to {{ $videos->lastItem() ?? 0 }} of {{ $videos->total() }} results
                            </div>
                            <div>
                                {{ $videos->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Player Modal -->
    @if ($showPlayerModal && $videoToPlay)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-5 overflow-hidden border border-slate-100 animate-fade-in">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <h5 class="text-sm font-bold text-slate-800 mb-0">{{ $videoToPlay->title }}</h5>
                        <p class="text-[11px] text-slate-400 mb-0">{{ $videoToPlay->subject?->name }} &bull; {{ $videoToPlay->chapter?->name }}</p>
                    </div>
                    <button type="button" wire:click="closePlayer" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <div class="bg-black rounded-xl overflow-hidden shadow-inner aspect-video flex items-center justify-center">
                    @if ($videoToPlay->fileExists())
                        <video src="{{ route('videos.preview', $videoToPlay) }}" controls class="w-full h-full object-contain"></video>
                    @else
                        <div class="text-center p-6 text-slate-400">
                            <i class="fas fa-exclamation-circle text-2xl mb-2 text-rose-400"></i>
                            <p class="text-xs mb-0">Video stream file missing from server storage.</p>
                        </div>
                    @endif
                </div>

                @if ($videoToPlay->description)
                    <p class="text-xs text-slate-600 mt-3 mb-0">{{ $videoToPlay->description }}</p>
                @endif

                <div class="mt-4 flex justify-between items-center text-xs text-slate-400 pt-3 border-t border-slate-100">
                    <span>File size: {{ number_format($videoToPlay->file_size / 1048576, 1) }} MB</span>
                    <button type="button" wire:click="closePlayer" class="btn-brand-outline text-xs px-4 py-1.5">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center animate-fade-in">
                <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="text-base font-bold text-slate-800 mb-1">Delete Video Lesson</h5>
                <p class="text-xs text-slate-500 mb-4">
                    Are you sure you want to delete <span class="font-bold text-slate-700">"{{ $videoToDelete->title ?? '' }}"</span>? This will permanently delete the uploaded video asset.
                </p>

                @if ($videoToDelete)
                    <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-5 text-left text-xs text-slate-600 flex flex-col gap-1">
                        <div class="flex justify-between">
                            <span class="text-slate-400">Subject:</span>
                            <span class="font-medium text-slate-700">{{ $videoToDelete->subject?->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">Chapter:</span>
                            <span class="font-medium text-slate-700">{{ $videoToDelete->chapter?->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400">File Size:</span>
                            <span class="font-semibold text-slate-700">{{ $videoToDelete->file_size ? number_format($videoToDelete->file_size / 1048576, 1) . ' MB' : 'N/A' }}</span>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="cancelDelete">
                        Cancel
                    </button>
                    <button type="button" class="btn bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg px-4 py-2 flex items-center gap-1.5 transition" wire:click="deleteVideo">
                        <i class="fas fa-trash text-[11px]"></i> Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
