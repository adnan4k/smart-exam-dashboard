<div x-data="{ openModal: @entangle('openModal') }" class="flex justify-center px-8">

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

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Exam Duration (in minutes)</label>
                            <input type="number" wire:model="examDuration"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100" placeholder="Enter duration in minutes">
                            @error('examDuration')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Year Group -->
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

                        <!-- Question Text -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Question Text</label>
                            <textarea wire:model="questionText" class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"></textarea>
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

                        <!-- Sample Question -->
                        <div class="flex items-center p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                            <div class="flex items-center h-5">
                                <input type="checkbox" wire:model="isSample" id="isSample"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="ml-2 text-sm">
                                <label for="isSample" class="font-medium text-gray-900 dark:text-gray-300">Mark as Sample Question</label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">This question will be available in the sample questions section</p>
                            </div>
                            @error('isSample')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Answer Text -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Answer</label>
                            <textarea wire:model="answerText" class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"></textarea>
                            @error('answerText')
                                <span class="text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Explanation -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Explanation</label>
                            <textarea wire:model="explanation" class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"></textarea>
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
                        <div
                            class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
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
