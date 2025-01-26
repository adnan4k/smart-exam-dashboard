<div
    x-data="{ openModal: @entangle('openModal') }"
    class="flex justify-center px-8">

    <div
        x-cloak
        x-show="openModal" id="default-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto">
        <div
            x-data="{ isEdit: @entangle('is_edit') }"
            class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="save">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <!-- Header -->
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            <span x-text="isEdit ? 'Edit Scholarship' : 'Create Scholarship'"></span>
                        </h3>

                    </div>
                    <!-- Form Content -->
                    <div class="flex flex-col gap-2 w-full border-gray-400">
                        <input type="hidden" wire:model="id">

                        <div class="flex justify-between px-4">
                            <!-- Title -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Title</label>
                                <input
                                    placeholder="Enter scholarship title"
                                    wire:model="title"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="text">
                                @error('title') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <!-- Author -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Author</label>
                                <input
                                    placeholder="Enter author's name"
                                    wire:model="author"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="text">
                                @error('author') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                            <!-- Date -->
                            <div class="px-4">
                                <label class="text-gray-600 dark:text-gray-400">Date</label>
                                <input
                                    placeholder="Select date"
                                    wire:model="date"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="date">
                                @error('date') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>



                        <!-- Link -->
                        <div  class="px-4">
                            <label class="text-gray-600 dark:text-gray-400">Link</label>
                            <input
                                placeholder="Enter scholarship link"
                                wire:model="link"
                                class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="url">
                            @error('link') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <!-- Category -->
                        @if ($categories)
                        <div class="max-w-48 px-4">
                            <label for="category_id" class="block mb-2 text-sm font-medium text-gray-600 w-full">Category</label>
                            <select
                                wire:model="category_id"
                                id="category_id"
                                class="h-12 border border-gray-300 text-gray-600 text-base rounded-lg block w-full py-2.5 px-4 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="" selected>Select a Category</option>
                                @forelse ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->title }}</option>
                                @empty
                                <option value="" disabled>No Categories Available</option>
                                @endforelse
                            </select>

                            <!-- Validation Error Message -->
                            <div>
                                @error('category_id')
                                <span class="text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="px-4 w-full">
                        <label class="text-gray-600 dark:text-gray-400">
                            Image
                        </label>
                        <input
                            value="{{$image ?? null}}"
                            wire:model="image"
                            class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                            type="file">
                        <div>
                            @error('image') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                        <button
                            data-modal-hide="default-modal"
                            type="submit"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <span x-text="isEdit ? 'Edit Scholarship' : 'Create Scholarship'"></span>
                        </button>
                        <button
                            @click="openModal = false"
                            type="button"
                            class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 bg-white rounded-lg border border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                            Cancel
                        </button>
                    </div>
                </div>
        </div>
        </form>
    </div>
</div>