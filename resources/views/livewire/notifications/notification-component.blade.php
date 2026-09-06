<div>
    <livewire:notifications.form />

    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="border-b border-slate-100 p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-base shadow-xs flex-shrink-0">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Announcements &amp; Broadcasts</h5>
                                <p class="text-xs text-slate-400 mb-0">Push announcements and urgent notices to student mobile applications.</p>
                            </div>
                        </div>

                        <button @click="$dispatch('notificationModal')"
                                class="btn-brand text-xs px-4 py-2 shadow-sm flex items-center gap-1.5"
                                type="button">
                            <i class="fa-solid fa-plus text-xs"></i> New Notification
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 w-full">
                            <thead>
                                <tr>
                                    <th class="text-center w-12">#</th>
                                    <th>Announcement</th>
                                    <th class="text-center">Audience</th>
                                    <th class="text-center">Engagement</th>
                                    <th class="text-center">Published</th>
                                    <th class="text-center w-32">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notifications as $index => $notification)
                                    <tr class="hover:bg-slate-50/70 transition">
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $index + 1 }}
                                        </td>
                                        <td>
                                            <div class="flex items-start gap-3 py-1">
                                                @if ($notification->image_url)
                                                    @php
                                                        $img = $notification->image_url;
                                                        if (!filter_var($img, FILTER_VALIDATE_URL) && !str_starts_with($img, 'storage/') && !str_starts_with($img, '/storage/')) {
                                                            $img = asset('storage/' . $img);
                                                        } elseif (!filter_var($img, FILTER_VALIDATE_URL)) {
                                                            $img = asset($img);
                                                        }
                                                    @endphp
                                                    <img src="{{ $img }}" alt="" class="w-10 h-10 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                                                @else
                                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center text-sm flex-shrink-0">
                                                        <i class="fa-regular fa-message"></i>
                                                    </div>
                                                @endif
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-xs font-bold text-slate-800 mb-0.5 leading-snug">{{ $notification->title }}</p>
                                                    <p class="text-[11px] text-slate-400 line-clamp-1 mb-0">{{ Str::limit($notification->body, 90) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge-subtle-brand">{{ $notification->type?->name ?? 'All Users' }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="inline-flex items-center gap-2.5 px-3 py-1 rounded-lg bg-slate-50 border border-slate-200/70 text-xs">
                                                <span class="flex items-center gap-1 text-emerald-700 font-semibold" title="Likes">
                                                    <i class="fa-regular fa-thumbs-up text-[11px]"></i> {{ $notification->like_count }}
                                                </span>
                                                <span class="text-slate-300">&bull;</span>
                                                <span class="flex items-center gap-1 text-slate-500 font-semibold" title="Dislikes">
                                                    <i class="fa-regular fa-thumbs-down text-[11px]"></i> {{ $notification->dislike_count }}
                                                </span>
                                                <span class="text-slate-300">&bull;</span>
                                                <span class="flex items-center gap-1 text-[#58706D] font-semibold" title="Comments">
                                                    <i class="fa-regular fa-comments text-[11px]"></i> {{ $notification->comment_count }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-medium text-slate-600 mb-0">{{ $notification->created_at->format('M d, Y') }}</p>
                                            <p class="text-[11px] text-slate-400 mb-0">{{ $notification->created_at->diffForHumans() }}</p>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button wire:click="showComments({{ $notification->id }})"
                                                        class="action-icon-btn {{ $selectedNotification && $selectedNotification->id === $notification->id ? 'bg-[#58706D]/10 text-[#58706D]' : '' }}"
                                                        title="View Comments">
                                                    <i class="fa-regular fa-comments text-xs"></i>
                                                </button>
                                                <button wire:click="editNotification({{ $notification->id }})"
                                                        class="action-icon-btn" title="Edit Announcement">
                                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                                </button>
                                                <button wire:click="confirmDelete({{ $notification->id }})"
                                                        class="action-icon-btn text-rose-500 hover:bg-rose-50 hover:text-rose-700" title="Delete Announcement">
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-12">
                                            <x-empty-state 
                                                title="No announcements sent yet" 
                                                subtitle="Broadcast notifications to notify mobile students of upcoming contests, schedule updates, or system tips."
                                                icon="fa-solid fa-bullhorn" 
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

    {{-- Comments Section (if selected) --}}
    @if($selectedNotification)
        <div class="row mt-2">
            <div class="col-12">
                <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6">
                    <div class="border-b border-slate-100 p-4 bg-slate-50/50 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-[#58706D]/10 text-[#58706D] flex items-center justify-center text-xs font-bold">
                                <i class="fa-regular fa-comments"></i>
                            </div>
                            <h6 class="font-bold text-slate-800 text-sm mb-0">
                                Student Feedback on: <span class="text-[#58706D]">"{{ $selectedNotification->title }}"</span>
                            </h6>
                        </div>
                        <button wire:click="$set('selectedNotification', null)" class="action-icon-btn text-slate-400 hover:text-slate-700" title="Close comments">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>

                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 w-full">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Student</th>
                                        <th>Comment Text</th>
                                        <th class="text-center w-36">Posted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedComments as $comment)
                                        <tr class="hover:bg-slate-50/70 transition">
                                            <td class="ps-4">
                                                <div class="flex items-center gap-2.5 py-1">
                                                    <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center flex-shrink-0 uppercase">
                                                        {{ substr(optional($comment->user)->name ?? 'U', 0, 2) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-bold text-slate-800 mb-0">{{ optional($comment->user)->name ?? 'Unknown Student' }}</p>
                                                        <p class="text-[11px] text-slate-400 mb-0">{{ optional($comment->user)->phone_number ?? optional($comment->user)->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs text-slate-700 mb-0 leading-relaxed">{{ $comment->comment }}</p>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-medium text-slate-600 mb-0">{{ $comment->created_at->format('M d, Y') }}</p>
                                                <p class="text-[11px] text-slate-400 mb-0">{{ $comment->created_at->diffForHumans() }}</p>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-8">
                                                <p class="text-xs text-slate-400 mb-0">No comments posted by students for this announcement yet.</p>
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
        <div class="fixed inset-0 z-50 flex justify-center items-center bg-slate-900/40 backdrop-blur-xs p-4">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden">
                <div class="p-6 text-center">
                    <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-xl mx-auto mb-4">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h5 class="font-bold text-slate-800 text-base mb-1">Delete Notification?</h5>
                    <p class="text-xs text-slate-400 mb-4">
                        This action is irreversible. The notification and all student responses and comments will be permanently erased.
                    </p>

                    @if($notificationToDelete)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 text-left mb-5">
                            <p class="text-xs font-bold text-slate-800 mb-1">{{ $notificationToDelete->title }}</p>
                            <p class="text-[11px] text-slate-500 mb-0 line-clamp-2">{{ Str::limit($notificationToDelete->body, 80) }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-center gap-3">
                        <button type="button" wire:click="cancelDelete" class="btn-brand-outline text-xs px-4 py-2">
                            Cancel
                        </button>
                        <button type="button" wire:click="deleteNotification" class="text-xs font-bold px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition">
                            <i class="fa-solid fa-trash-can mr-1.5"></i> Delete Permanently
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>