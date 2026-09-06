<x-layouts.app>
    @include('contests.partials.theme')

    <div class="main-content ct">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Contests</h5>
                                <p class="text-sm mb-0 ct-muted">Scheduled competitions students compete in for stars and coins.</p>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('contest-questions.index') }}" class="btn ct-btn-quiet btn-sm mb-0">Contest bank</a>
                                <a href="{{ route('contests.create') }}"
                                  
                                   class="btn ct-btn btn-sm mb-0">+&nbsp; New contest</a>
                            </div>
                        </div>

                        <div class="mt-3">
                            @php $current = request('status'); @endphp
                            @foreach (['' => 'All', 'draft' => 'Draft', 'scheduled' => 'Scheduled', 'finalized' => 'Finalized'] as $value => $label)
                                <a href="{{ route('contests.index', array_filter(['status' => $value])) }}"
                                   class="btn btn-sm mb-0 me-1 {{ $current === ($value ?: null) ? 'btn-dark' : 'btn-outline-secondary' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="card-body px-0 pt-3 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Contest</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Exam type</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Starts</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Questions</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Entries</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($contests as $contest)
                                        <tr>
                                            <td class="ps-4">
                                                <p class="text-xs font-weight-bold mb-0">{{ $contest->title }}</p>
                                                <p class="text-xxs text-secondary mb-0">
                                                    {{ $contest->duration_minutes }} min &middot;
                                                    {{ $contest->join_window_minutes }} min join window
                                                </p>
                                            </td>
                                            <td>
                                                <span class="text-xs">{{ $contest->type?->name ?? 'All types' }}</span>
                                            </td>
                                            <td>
                                                <p class="text-xs mb-0">{{ $contest->starts_at->format('D, d M Y') }}</p>
                                                <p class="text-xxs text-secondary mb-0">{{ $contest->starts_at->format('H:i') }} &ndash; {{ $contest->ends_at->format('H:i') }}</p>
                                            </td>
                                            <td class="text-center"><span class="text-xs font-weight-bold">{{ $contest->question_count }}</span></td>
                                            <td class="text-center"><span class="text-xs font-weight-bold">{{ $contest->attempts_count }}</span></td>
                                            <td class="text-center">
                                                @include('contests.partials.status-badge', ['contest' => $contest])
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('contests.builder', $contest) }}" class="text-secondary me-2" title="Build paper">
                                                    <i class="fa-solid fa-list-check"></i>
                                                </a>
                                                <a href="{{ route('contests.leaderboard', $contest) }}" class="ct-icon-action me-2" title="Leaderboard">
                                                    <i class="fa-solid fa-ranking-star"></i>
                                                </a>
                                                <a href="{{ route('contests.edit', $contest) }}" class="ct-icon-action me-2" title="Edit">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </a>
                                                @if ($contest->attempts_count === 0)
                                                    <form action="{{ route('contests.destroy', $contest) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Delete this contest?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-link p-0 m-0 ct-icon-danger align-baseline" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <p class="text-sm text-secondary mb-2">No contests yet.</p>
                                                <a href="{{ route('contests.create') }}" class="btn ct-btn btn-sm mb-0">Create the first one</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="px-4 pt-3">{{ $contests->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
