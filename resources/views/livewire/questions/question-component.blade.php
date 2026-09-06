<div>
    <livewire:questions.form />
    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Question Bank</h5>
                            <p class="text-xs text-slate-400 mb-0">Browse and manage questions categorized by subject, year group, and exam type.</p>
                        </div>
                        <button
                            @click="$dispatch('questionModal')"
                            class="btn-brand self-start lg:self-auto shadow-sm"
                            type="button">
                            <i class="fa-solid fa-plus text-xs"></i> New Question
                        </button>
                    </div>

                    <!-- Filter Toolbar -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mt-3 pt-3 border-t border-slate-100">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input type="text" wire:model.live="searchTerm" class="form-control" style="padding-left: 2rem !important;" placeholder="Search questions...">
                        </div>
                        <div>
                            <select wire:model.live="selectedSubject" class="form-select">
                                <option value="">All Subjects</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->name }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select wire:model.live="selectedYear" class="form-select">
                                <option value="">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year->year }}">{{ $year->year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select wire:model.live="selectedType" class="form-select">
                                <option value="">All Types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Card Body & Table -->
                <div class="card-body px-0 pt-0 pb-3">
                    @php
                        $groupedQuestions = $questions->groupBy(function($question) {
                            return $question->subject->name ?? 'No Subject';
                        });
                    @endphp

                    @forelse($groupedQuestions as $subject => $subjectQuestions)
                        <div class="mb-4">
                            <div class="px-4 py-2.5 bg-slate-50/80 border-y border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                    <i class="fas fa-book text-[#58706D] text-xs"></i>
                                    {{ $subject }}
                                </span>
                                <span class="badge-subtle-brand text-xs">
                                    {{ count($subjectQuestions) }} questions
                                </span>
                            </div>
                            <div class="table-responsive p-0">
                                <table class="table align-items-center mb-0 w-full">
                                    <thead>
                                        <tr>
                                            <th class="text-center w-12">#</th>
                                            <th>Question Preview</th>
                                            <th class="text-center">Subject</th>
                                            <th class="text-center">Year</th>
                                            <th class="text-center">Type</th>
                                            <th class="text-center w-24">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($subjectQuestions as $num => $question)
                                            <tr>
                                                <td class="text-center text-xs font-semibold text-slate-400">{{ $num + 1 }}</td>
                                                <td>
                                                    <div class="text-xs font-medium text-slate-800">
                                                        {!! Str::limit(strip_tags($question->question_text), 75) !!}
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge-subtle-brand">{{ $question->subject->name ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge-subtle-amber">{{ $question->subject->year ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge-subtle-success">
                                                        {{ ucfirst($question->type ? $question->type->name : 'Standard') }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button
                                                            type="button"
                                                            @click="$dispatch('edit-question', { questionId: {{ $question->id }} })"
                                                            class="action-icon-btn"
                                                            title="Edit question">
                                                            <i class="fa-regular fa-pen-to-square text-xs"></i>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            wire:click="confirmDelete({{ $question->id }})"
                                                            class="action-icon-btn btn-danger-icon"
                                                            title="Delete question">
                                                            <i class="fa-solid fa-trash text-xs"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <x-empty-state 
                            icon="fas fa-circle-question"
                            title="No questions found"
                            message="There are no questions matching your current filters. Start building your question bank by creating your first entry."
                        >
                            <button
                                type="button"
                                @click="$dispatch('questionModal')"
                                class="btn-brand">
                                <i class="fa-solid fa-plus text-xs"></i> Add New Question
                            </button>
                        </x-empty-state>
                    @endforelse

                    <div class="d-flex justify-content-center mt-3 px-4">
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