<div x-data="{ openModal: @entangle('openModal') }" class="flex justify-center px-8">
    <!-- PlateJS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/platejs@latest/dist/plate.min.css" rel="stylesheet">
    
    <div @click.away="openModal = false" x-cloak x-show="openModal" id="default-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto">
        <div x-data="{ isEdit: @entangle('is_edit') }" class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="saveQuestion">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2"
                        x-text="isEdit ? 'Edit Question' : 'Create Question'"></h2>

                    <div class="flex flex-col gap-2 w-full border-gray-400">

                        <!-- Subject -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Subject</label>
                            <select wire:model="subjectId"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subjectId')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Chapters</label>
                            <select wire:model="chapterId"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Chapters</option>
                                @foreach ($chapters as $chapter)
                                    <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                @endforeach
                            </select>
                            @error('subjectId')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Exam Type</label>
                            <select wire:model="type"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Exam Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Exam Duration -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Exam Duration (in minutes)</label>
                            <input type="number" wire:model="duration"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                            @error('duration')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Year Group</label>
                            <select wire:model="yearGroupId"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Year Group</option>
                                @foreach ($yearGroups as $yearGroup)
                                    <option value="{{ $yearGroup->id }}">{{ $yearGroup->year }}</option>
                                @endforeach
                            </select>
                            @error('yearGroupId')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Question Text with PlateJS -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Question Text</label>
                            <div class="plate-editor" wire:ignore>
                                <textarea wire:model="questionText" 
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    placeholder="Enter question text..."></textarea>
                            </div>
                            @error('questionText')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Question Image -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Question Image</label>
                            <input type="file" wire:model="questionImage"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                            @error('questionImage')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Formula -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Formula (LaTeX)</label>
                            <input type="text" wire:model="formula"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                            @error('formula')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Answer Text -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Answer</label>
                            <textarea wire:model="answerText" 
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                placeholder="Enter correct answer..."></textarea>
                            @error('answerText')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Explanation with PlateJS -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Explanation</label>
                            <div class="plate-editor" wire:ignore>
                                <textarea wire:model="explanation" 
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    placeholder="Enter detailed explanation..."></textarea>
                            </div>
                            @error('explanation')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Explanation Image -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Explanation Image</label>
                            <input type="file" wire:model="explanationImage"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                            @error('explanationImage')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Choices -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Choices</label>
                            @foreach ($choices as $index => $choice)
                                <div class="flex gap-2 mb-2">
                                    <textarea wire:model="choices.{{ $index }}.text" placeholder="Choice Text"
                                        class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"></textarea>
                                    <button type="button" wire:click="removeChoice({{ $index }})"
                                        class="text-red-500">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                            <button type="button" wire:click="addChoice"
                                class="py-2.5 px-2 bg-[#56C596] text-sm font-medium text-white focus:outline-none rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                Add Choice
                            </button>
                        </div>
                   
                        <!-- Submit Button -->
                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button style="background-color:#56C596;" type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <span x-text="isEdit ? 'Edit' : 'Create'"></span>
                            </button>
                            <button @click="openModal = false" type="button"
                                class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/platejs@latest/dist/plate.min.js"></script>
<script>
    document.addEventListener('livewire:load', function() {
        // Initialize PlateJS editors
        Plate.init({
            selector: '.plate-editor',
            plugins: ['basic'],
            toolbar: ['bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList']
        });
        
        // Reinitialize after Livewire updates
        Livewire.hook('message.processed', () => {
            Plate.init({
                selector: '.plate-editor',
                plugins: ['basic'],
                toolbar: ['bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList']
            });
        });
    });
</script>
@endpush