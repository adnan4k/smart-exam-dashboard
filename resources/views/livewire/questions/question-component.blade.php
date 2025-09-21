<div class="main-content">
    <livewire:questions.form />
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <h5 class="mb-0">All Questions</h5>
                        <div class="d-flex gap-2">
                            <div class="input-group">
                                <input type="text" wire:model.live="searchTerm" class="form-control" placeholder="Search questions...">
                            </div>
                            <select wire:model.live="selectedSubject" class="form-select">
                                <option value="">All Subjects</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->name }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="selectedYear" class="form-select">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year->year }}">{{ $year->year }}</option>
                                @endforeach
                            </select>
                            <select wire:model.live="selectedType" class="form-select">
                                <option value="">All Types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <button
                                style="background-color:#56C596;"
                                @click="$dispatch('questionModal')"
                                class="btn text-white btn-sm px-3"
                                type="button">
                                <i class="fa-solid fa-plus"></i> New Question
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    @php
                        $groupedQuestions = $questions->groupBy(function($question) {
                            return $question->subject->name ?? 'No Subject';
                        });
                    @endphp

                    @foreach($groupedQuestions as $subject => $subjectQuestions)
                        <div class="mb-4">
                            <h6 class="text-uppercase text-secondary text-xs font-weight-bolder opacity-7 px-4">
                                {{ $subject }}
                            </h6>
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
                                        @foreach ($subjectQuestions as $num => $question)
                                            <tr>
                                                <td class="ps-4 text-xs">{{ $num + 1 }}</td>
                                                <td>
                                                    <div class="text-xs font-weight-bold mb-0">
                                                        {!! Str::limit(strip_tags($question->question_text), 50) !!}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary text-white">{{ $question->subject->name }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info text-white">{{ $question->subject->year }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $question->type == 'exam' ? 'bg-danger' : 'bg-success' }} text-white">
                                                        {{ ucfirst($question->type ? $question->type->name : "" ) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-2 justify-content-center">
                                                        <button
                                                            @click="$dispatch('edit-question', { questionId: {{ $question->id }} })"
                                                            class="btn btn-sm text-primary"
                                                            data-bs-toggle="tooltip" title="Edit">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </button>
                                                        <button
                                                            wire:click="confirmDelete({{ $question->id }})"
                                                            class="btn btn-sm text-danger"
                                                            data-bs-toggle="tooltip" title="Delete">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    @if ($questions->isEmpty())
                        <div class="text-center p-3 text-muted">No questions found.</div>
                    @endif

                    <div class="d-flex justify-content-center mt-4">
                        {{ $questions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Confirm Question Deletion
                        </h5>
                        <button type="button" class="close" wire:click="cancelDelete">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($questionToDelete)
                            <div class="alert alert-warning">
                                <strong>Warning!</strong> This action cannot be undone. All related choices will also be deleted.
                            </div>
                            <p>Are you sure you want to delete the following question?</p>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Question Details:</h6>
                                    <div class="mb-2">
                                        <strong>Question:</strong>
                                        <div class="border p-2 rounded bg-light">
                                            {!! Str::limit(strip_tags($questionToDelete->question_text), 200) !!}
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Subject:</strong> {{ $questionToDelete->subject->name ?? 'N/A' }}<br>
                                            <strong>Year:</strong> {{ $questionToDelete->subject->year ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Type:</strong> {{ $questionToDelete->type->name ?? 'N/A' }}<br>
                                            <strong>Choices:</strong> {{ $questionToDelete->choices->count() }} choices
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteQuestion">
                            <i class="fas fa-trash"></i> Delete Question
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>