<div class="main-content">
    <livewire:questions.form />

    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Questions</h5>
                    <button
                        style="background-color:#56C596;"
                        @click="$dispatch('questionModal')"
                        class="btn text-white btn-sm px-3"
                        type="button">
                        <i class="fa-solid fa-plus"></i> New Question
                    </button>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">#</th>
                                    <th class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Question</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Subject</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Year Group</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Type</th>
                                    <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($questions as $num => $question)
                                    <tr>
                                        <td class="ps-4 text-xs">{{ $num + 1 }}</td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ Str::limit($question->question_text, 50) }}</p>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary text-white">{{ $question->subject->name }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-white">{{ $question->yearGroup->year }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $question->type == 'exam' ? 'bg-danger' : 'bg-success' }} text-white">
                                                {{ ucfirst($question->type ? $question->type->name : "" ) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                @click="$dispatch('edit-question', { question: {{ $question->id }} })"
                                                class="btn btn-sm text-primary"
                                                data-bs-toggle="tooltip" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button
                                                wire:click="$dispatch('openDeleteModal', { itemId: {{ $question->id }}, model: '{{ addslashes(App\Models\Question::class) }}' })"
                                                class="btn btn-sm text-danger"
                                                data-bs-toggle="tooltip" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                            </button>
                                            {{-- <button
                                                @click="$dispatch('view-question-detail', { questionId: {{ $question->id }} })"
                                                class="btn btn-sm text-info"
                                                data-bs-toggle="tooltip" title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </button> --}}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($questions->isEmpty())
                        <div class="text-center p-3 text-muted">No questions available.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
