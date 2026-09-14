<x-layouts.app>
    @include('contests.partials.theme')

    <div class="ct">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                    <!-- Card Header -->
                    <div class="card-header border-b border-slate-100 p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Competitions & Contests</h5>
                                <p class="text-xs text-slate-400 mb-0">Scheduled competitive exams where candidates race for rankings, badges, and rewards.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('contest-questions.index') }}" class="btn-brand-outline text-xs px-3.5 py-2">
                                    <i class="fa-solid fa-layer-group text-xs"></i> Contest Bank
                                </a>
                                <a href="{{ route('contests.create') }}" class="btn-brand text-xs px-4 py-2 shadow-sm">
                                    <i class="fa-solid fa-plus text-xs"></i> New Contest
                                </a>
                            </div>
                        </div>

                        <!-- Status Filter Tabs -->
                        <div class="flex items-center gap-1.5 mt-4 pt-3 border-t border-slate-100">
                            @php $current = request('status'); @endphp
                            @foreach (['' => 'All Contests', 'draft' => 'Drafts', 'scheduled' => 'Scheduled', 'finalized' => 'Finalized'] as $value => $label)
                                @php $isActive = $current === ($value ?: null); @endphp
                                <a href="{{ route('contests.index', array_filter(['status' => $value])) }}"
                                   class="text-xs font-semibold px-3 py-1.5 rounded-lg transition {{ $isActive ? 'bg-[#58706D] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Card Body & Table -->
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 w-full">
                                <thead>
                                    <tr>
                                        <th class="text-center w-12">#</th>
                                        <th>Contest Title</th>
                                        <th class="text-center">Exam Type</th>
                                        <th class="text-center">Schedule</th>
                                        <th class="text-center">Questions</th>
                                        <th class="text-center">Entries</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center w-36">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($contests as $num => $contest)
                                        <tr>
                                            <td class="text-center text-xs font-semibold text-slate-400">
                                                {{ $contests->firstItem() + $num }}
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-xs shadow-xs flex-shrink-0">
                                                        <i class="fa-solid fa-trophy"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-bold text-slate-800 mb-0">{{ $contest->title }}</p>
                                                        <p class="text-[11px] text-slate-400 mb-0">
                                                            {{ $contest->duration_minutes }} mins &bull; {{ $contest->join_window_minutes }} mins window
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-subtle-brand">{{ $contest->type?->name ?? 'All Types' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <p class="text-xs font-medium text-slate-700 mb-0">{{ $contest->starts_at->format('M d, Y') }}</p>
                                                <p class="text-[11px] text-slate-400 mb-0">{{ $contest->starts_at->format('H:i') }} &ndash; {{ $contest->ends_at->format('H:i') }}</p>
                                            </td>
                                            <td class="text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-100 text-slate-700">
                                                    {{ $contest->question_count }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-[#58706D]">
                                                    {{ $contest->attempts_count }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @include('contests.partials.status-badge', ['contest' => $contest])
                                            </td>
                                            <td class="text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <a href="{{ route('contests.builder', $contest) }}" class="action-icon-btn" title="Build paper">
                                                        <i class="fa-solid fa-list-check text-xs"></i>
                                                    </a>
                                                    <a href="{{ route('contests.leaderboard', $contest) }}" class="action-icon-btn" title="Leaderboard">
                                                        <i class="fa-solid fa-ranking-star text-xs"></i>
                                                    </a>
                                                    <a href="{{ route('contests.edit', $contest) }}" class="action-icon-btn" title="Edit details">
                                                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                                                    </a>
                                                    @if ($contest->attempts_count === 0)
                                                        <form action="{{ route('contests.destroy', $contest) }}" method="POST" class="inline-block"
                                                              onsubmit="return confirm('Are you sure you want to delete this contest?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="action-icon-btn btn-danger-icon" title="Delete Contest">
                                                                <i class="fa-solid fa-trash text-xs"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="p-0">
                                                <x-empty-state
                                                    title="No Competitions Found"
                                                    description="Create a timed exam contest for candidates to challenge each other."
                                                    icon="fa-solid fa-trophy"
                                                >
                                                    <div class="mt-3">
                                                        <a href="{{ route('contests.create') }}" class="btn-brand text-xs px-4 py-2">
                                                            <i class="fa-solid fa-plus text-xs"></i> Create First Contest
                                                        </a>
                                                    </div>
                                                </x-empty-state>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($contests->hasPages())
                            <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                                <div class="text-xs text-slate-500">
                                    Showing {{ $contests->firstItem() }} to {{ $contests->lastItem() }} of {{ $contests->total() }} contests
                                </div>
                                <div>
                                    {{ $contests->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
