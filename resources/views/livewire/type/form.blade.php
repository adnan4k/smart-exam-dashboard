<div x-data="{ openModal: @entangle('openModal') }" class="flex justify-center px-8">
    <div @click.away="openModal = false" x-cloak x-show="openModal"
        class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto">
        <div x-data="{ isEdit: @entangle('isEdit') }" class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="saveType">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2"
                        x-text="isEdit ? 'Edit Type' : 'Add New Type'"></h2>

                    <div class="flex flex-col gap-2 w-full border-gray-400">
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Name</label>
                            <input wire:model="name"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none 
                                       focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Description</label>
                            <textarea wire:model="description"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none 
                                       focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                            </textarea>
                            @error('description') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Image</label>
                            <input type="file" wire:model="image" class="w-full py-2 border border-slate-200 rounded-lg px-3">
                            @if ($image)
                                <img src="{{ $image->temporaryUrl() }}" class="w-20 h-20 mt-2 rounded-lg">
                            @endif
                            @error('image') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center p-4 justify-between md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button style="background-color:#56C596;" type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none 
                                       focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center 
                                       dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <span x-text="isEdit ? 'Update' : 'Save'"></span>
                            </button>
                            <button @click="openModal = false" type="button"
                                class="py-2.5 px-5 ml-3 text-sm font-medium text-gray-900 focus:outline-none 
                                       bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 
                                       focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 
                                       dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white 
                                       dark:hover:bg-gray-700">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>