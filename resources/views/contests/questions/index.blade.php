<x-layouts.app>
    <div class="main-content">
        <div class="row">
            <div class="col-12">
                @include('contests.partials.flash')

                <div class="card mb-4 mx-4">
                    <div class="card-header pb-0">
                        <div class="d-flex flex-row justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-0">Contest bank</h5>
                                <p class="text-sm text-secondary mb-0">
                                    Questions written for contests. Students cannot see these anywhere in the app
                                    until a contest using them has ended.
                                </p>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('contest-questions.create') }}"
                                   style="background-color:#56C596;" class="btn text-white btn-sm mb-0">+&nbsp; New question</a>
                                <a href="{{ route('contests.index') }}" class="btn btn-outline-dark btn-sm mb-0">Contests</a>
                            </div>
                        </div>

                        <form method="GET" class="d-flex gap-2 mt-3" style="max-width: 32rem;">
                            <input type="search" name="search" value="{{ request('search') }}"
                                   class="form-control form-control-sm" placeholder="Search question text">
                            <select name="subject_id" class="form-control form-control-sm">
                                <option value="">All subjects</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-dark mb-0">Filter</button>
                        </form>
                    </div>

                    <div class="card-body px-0 pt-3 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Question</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subject</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Level</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">On a paper</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($questions as $question)
                                        <tr>
                                            <td class="ps-4" style="max-width: 34rem;">
                                                <p class="text-xs mb-0">{{ Str::limit(strip_tags($question->question_text), 140) }}</p>
                                            </td>
                                            <td><span class="text-xs">{{ $question->subject?->name ?? '—' }}</span></td>
                                            <td class="text-center">
                                                <span class="badge badge-sm bg-gradient-info">{{ $question->difficulty ?? 'unset' }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($usedIds->has($question->id))
                                                    <span class="badge badge-sm bg-gradient-dark">Assigned</span>
                                                @else
                                                    <span class="text-xxs text-secondary">Available</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('contest-questions.edit', $question->id) }}" class="text-blue-500 me-2">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </a>
                                                @unless ($usedIds->has($question->id))
                                                    <form action="{{ route('contest-questions.destroy', $question->id) }}" method="POST"
                                                          class="d-inline" onsubmit="return confirm('Delete this question?');">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-link p-0 m-0 text-red-500 align-baseline">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endunless
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <p class="text-sm text-secondary mb-1">The contest bank is empty.</p>
                                                <p class="text-xxs text-secondary mb-3" style="max-width: 30rem; margin: 0 auto;">
                                                    Contest papers are built from questions written here, so students who
                                                    grind the study bank have not already seen the paper.
                                                </p>
                                                <a href="{{ route('contest-questions.create') }}" class="btn btn-sm btn-dark mb-0">Write the first one</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="px-4 pt-3">{{ $questions->links() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
