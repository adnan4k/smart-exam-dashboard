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
                                <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Contest Question Bank</h5>
                                <p class="text-xs text-slate-400 mb-0">
                                    Exclusive competition questions withheld from practice pools until contest completion.
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('contests.index') }}" class="btn-brand-outline text-xs px-3.5 py-2">
                                    <i class="fa-solid fa-trophy text-xs"></i> View Contests
                                </a>
                                <a href="{{ route('contest-questions.create') }}" class="btn-brand text-xs px-4 py-2 shadow-sm">
                                    <i class="fa-solid fa-plus text-xs"></i> New Question
                                </a>
                            </div>
                        </div>

                        <!-- Filter Form -->
                        <form method="GET" class="flex flex-col sm:flex-row items-center gap-2.5 mt-4 pt-3 border-t border-slate-100 max-w-xl">
                            <div class="relative flex-1 w-full">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <input type="search" name="search" value="{{ request('search') }}"
                                       class="form-control text-xs w-full" style="padding-left: 2rem !important;" placeholder="Search question text...">
                            </div>
                            <select name="subject_id" class="form-select text-xs w-full sm:w-48">
                                <option value="">All subjects</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-brand text-xs px-4 py-2 w-full sm:w-auto flex-shrink-0">
                                Filter
                            </button>
                        </form>
                    </div>

                    <!-- Card Body & Table -->
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0 w-full">
                                <thead>
                                    <tr>
                                        <th class="text-center w-12">#</th>
                                        <th>Question Preview</th>
                                        <th class="text-center">Subject</th>
                                        <th class="text-center">Difficulty</th>
                                        <th class="text-center">Assignment</th>
                                        <th class="text-center w-24">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($questions as $num => $question)
                                        <tr>
                                            <td class="text-center text-xs font-semibold text-slate-400">
                                                {{ $questions->firstItem() + $num }}
                                            </td>
                                            <td style="max-width: 34rem;">
                                                <p class="text-xs font-medium text-slate-800 mb-0">
                                                    {{ Str::limit(strip_tags($question->question_text), 130) }}
                                                </p>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-subtle-brand">{{ $question->subject?->name ?? '—' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 capitalize">
                                                    {{ $question->difficulty ?? 'Standard' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if ($usedIds->has($question->id))
                                                    <span class="badge-subtle-amber font-semibold">Assigned</span>
                                                @else
                                                    <span class="badge-subtle-success font-semibold">Available</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="flex items-center justify-center gap-1">
                                                    <a href="{{ route('contest-questions.edit', $question->id) }}" class="action-icon-btn" title="Edit Question">
                                                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                                                    </a>
                                                    @unless ($usedIds->has($question->id))
                                                        <form action="{{ route('contest-questions.destroy', $question->id) }}" method="POST"
                                                              class="inline-block" onsubmit="return confirm('Delete this contest question?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="action-icon-btn btn-danger-icon" title="Delete Question">
                                                                <i class="fa-solid fa-trash text-xs"></i>
                                                            </button>
                                                        </form>
                                                    @endunless
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="p-0">
                                                <x-empty-state
                                                    title="Contest Bank Is Empty"
                                                    description="Draft special challenge questions that are quarantined from regular practice sets."
                                                    icon="fas fa-shield-alt"
                                                >
                                                    <div class="mt-3">
                                                        <a href="{{ route('contest-questions.create') }}" class="btn-brand text-xs px-4 py-2">
                                                            <i class="fa-solid fa-plus text-xs"></i> Write First Contest Question
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
                        @if($questions->hasPages())
                            <div class="px-4 py-3 border-t border-slate-100 flex items-center justify-between">
                                <div class="text-xs text-slate-500">
                                    Showing {{ $questions->firstItem() }} to {{ $questions->lastItem() }} of {{ $questions->total() }} questions
                                </div>
                                <div>
                                    {{ $questions->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
