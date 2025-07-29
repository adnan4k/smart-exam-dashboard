<div x-data="{
        openModal: @entangle('openModal'),
        initQuill() {
            // First, completely destroy any existing Quill instance
            if (window.explanationEditor) {
                try {
                    window.explanationEditor.destroy();
                } catch (e) {
                    console.log('Error destroying existing editor:', e);
                }
                window.explanationEditor = null;
            }

            // Remove any existing Quill elements from the DOM
            const existingQuillElements = document.querySelectorAll('.ql-editor, .ql-toolbar');
            existingQuillElements.forEach(el => {
                if (el.closest('#explanationEditor')) {
                    el.remove();
                }
            });

            // Recreate the editor container
            const container = document.getElementById('explanationEditor');
            if (!container) {
                console.error('explanationEditor container not found!');
                return false;
            }

            // Clear the container and recreate the editor div
            container.innerHTML = '';

            try {
                window.explanationEditor = new Quill('#explanationEditor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline'],
                            ['link'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'color': [] }, { 'background': [] }], // Color and background color options
                            ['formula'], // Add formula support
                            ['clean']
                        ]
                    },
                    placeholder: 'Enter detailed explanation...'
                });

                // Set content if editing (explanation exists)
                const explanation = window.Livewire.find('{{ $this->getId() }}').get('explanation');
                if (explanation && explanation.trim() !== '') {
                    // Use setTimeout to ensure the editor is fully ready
                    setTimeout(() => {
                        if (window.explanationEditor && window.explanationEditor.root) {
                            window.explanationEditor.root.innerHTML = explanation;
                        }
                    }, 50);
                }

                window.explanationEditor.on('text-change', function() {
                    window.Livewire.find('{{ $this->getId() }}').set('explanation', window.explanationEditor.root.innerHTML);
                });

                return true;
            } catch (error) {
                console.error('Error initializing explanationEditor:', error);
                return false;
            }
        },
        
        // Retry initialization with multiple attempts
        initQuillWithRetry() {
            let attempts = 0;
            const maxAttempts = 5;
            
            const tryInit = () => {
                attempts++;
                if (this.initQuill()) {
                    return;
                }
                if (attempts < maxAttempts) {
                    setTimeout(tryInit, attempts * 100);
                }
            };
            tryInit();
        },

        // Clear Quill editor content
        clearQuill() {
            if (window.explanationEditor) {
                window.explanationEditor.setText('');
            }
        },

        // Clean up Quill editor completely
        cleanupQuill() {
            if (window.explanationEditor) {
                try {
                    window.explanationEditor.destroy();
                } catch (e) {
                    console.log('Error destroying editor during cleanup:', e);
                }
                window.explanationEditor = null;
            }
        }
    }"
    x-init="$watch('openModal', value => { 
        if(value) { 
            // Clear the editor content first if it's a new question (not editing)
            if (!window.Livewire.find('{{ $this->getId() }}').get('is_edit')) {
                setTimeout(() => {
                    this.clearQuill();
                }, 50);
            }
            setTimeout(() => initQuillWithRetry(), 100); 
        } else {
            // Clean up when modal is closed
            setTimeout(() => {
                this.cleanupQuill();
            }, 100);
        }
    })"
    class="flex justify-center px-8"
>
    @assets
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    @endassets

    <div @click.away="openModal = false" x-cloak x-show="openModal" id="default-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto"
        wire:ignore.self>
        <div x-data="{ isEdit: @entangle('is_edit') }" class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="saveQuestion">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2"
                        x-text="isEdit ? 'Edit Question' : 'Create Question'"></h2>

                    <div class="flex flex-col gap-2 w-full border-gray-400">
                        <!-- Exam Type -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Exam Type</label>
                            <select wire:model="type" wire:change="loadSubjects()"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Exam Type</option>
                                @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Subject</label>
                            <select wire:model="subjectId" wire:change="loadChapters()"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Subject</option>
                                @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }} - {{ $subject->year }} - {{ $subject->region }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Chapters -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Chapters</label>
                            <select wire:model="chapterId"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Chapters</option>
                                @foreach ($chapters as $chapter)
                                <option value="{{ $chapter->id }}">{{ $chapter->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Question Text -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Question Text</label>
                            <textarea wire:model="questionText" placeholder="Enter question text..."
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100" rows="4"></textarea>
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

                        <!-- Choices (Simplified - No Images) -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Choices</label>
                            @foreach ($choices as $index => $choice)
                            <div class="mb-4">
                                <div class="flex gap-2 mb-1">
                                    <textarea wire:model="choices.{{ $index }}.text" placeholder="Choice Text"
                                        class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100" rows="2"></textarea>
                                    <button type="button" wire:click="removeChoice({{ $index }})"
                                        class="text-red-500">
                                        Remove
                                    </button>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" wire:model="correctChoiceId" value="{{ $index }}"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                                        Mark as Correct Answer
                                    </label>
                                </div>
                                @error('choices.'.$index.'.text')
                                <span class="text-red-500">Choice text is required</span>
                                @enderror
                            </div>
                            @endforeach
                            <button type="button" wire:click="addChoice"
                                class="py-2.5 px-2 bg-[#56C596] text-sm font-medium text-white focus:outline-none rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                Add Choice
                            </button>
                        </div>

                        <!-- Explanation -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Explanation</label>
                            <div wire:ignore>
                                <div id="explanationEditor" class="w-full border border-slate-200 rounded-lg focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100" style="height: 200px;"></div>
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

                        <!-- Show region field only if exam type is regional -->
                        <div x-show="$wire.type === 'regional'">
                            <label class="text-gray-600 dark:text-gray-400">Region</label>
                            <select wire:model="region"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Region</option>
                                <option value="addis_ababa">Addis Ababa</option>
                                <option value="afar">Afar</option>
                                <option value="amhara">Amhara</option>
                                <option value="benishangul_gumuz">Benishangul-Gumuz</option>
                                <option value="central_ethiopia">Central Ethiopia</option>
                                <option value="dire_dawa">Dire Dawa</option>
                                <option value="gambela">Gambela</option>
                                <option value="harari">Harari</option>
                                <option value="oromia">Oromia</option>
                                <option value="sidama">Sidama</option>
                                <option value="south_ethiopia">South Ethiopia</option>
                                <option value="tigray">Tigray</option>
                            </select>
                            @error('region')
                            <span class="text-red-500">{{ $message }}</span>
                            @enderror
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