<div
    x-data="{ openModal: @entangle('openModal') }"
    class="flex justify-center px-8">

    <div
        x-cloak
        x-show="openModal" id="default-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto">
        <div
            x-data="{isEdit:@entangle('is_edit')}"
            class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="save">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            <span x-text="isEdit ? 'Edit Vacancy' : 'Create Vacancy'"></span>
                        </h3>
                        <button
                            @click="openModal = false"
                            type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="default-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <div class="flex flex-col gap-2 w-full border-gray-400">
                        <!-- ID for Editing -->
                        <input type="hidden" wire:model="id">
                        <div class="flex justify-between">
                            <!-- Title -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Email</label>
                                <input
                                    placeholder="email"
                                    wire:model="email"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="email">
                                @error('email') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <!-- Experience -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Phone</label>
                                <input
                                    placeholder="phone"

                                    wire:model="phone"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="phone">
                                @error('phone') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="flex justify-between">
                            <!-- Title -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Title</label>
                                <input
                                    wire:model="title"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="text">
                                @error('title') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <!-- Experience -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Experience</label>
                                <input
                                    wire:model="experience"
                                    class="w-full py-2 border border-slate-200 rounded-lg  focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="text">
                                @error('experience') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>


                        <!-- Description -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Description</label>
                            <textarea
                                wire:model="description"
                                class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"></textarea>
                            @error('description') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-between">
                            <!-- Deadline -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Deadline</label>
                                <input
                                    wire:model="deadline"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="date">
                                @error('deadline') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="text-gray-600 dark:text-gray-400">start Date</label>
                                <input
                                    wire:model="startDate"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="date">
                                @error('startDate') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>


                        </div>

                        <div class="flex justify-between">
                            <!-- Title -->
                            <div class="mr-10">
                                <label class="text-gray-600 dark:text-gray-400">Languages</label>
                                <input
                                    wire:model="languages"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="text">
                                @error('languages') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <!-- Location -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Location</label>
                                <input
                                    wire:model="location"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="text">
                                @error('location') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Experience -->
                        <div class="ml-10">
                            <label class="text-gray-600 dark:text-gray-400">Responsibilities</label>
                            <input
                                wire:model="keyResponsibilities"
                                class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            @error('keyResponsibilities') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-between">
                            <!-- Job Type -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Job Type</label>
                                <input
                                    wire:model="jobType"
                                    class="w-full py-2 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="text">
                                @error('jobType') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <!-- Qualifications -->
                            <div>
                                <label class="text-gray-600 dark:text-gray-400">Qualifications</label>
                                <textarea
                                    wire:model="qualifications"
                                    class="w-full  border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"></textarea>
                                @error('qualifications') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>



                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button
                                data-modal-hide="default-modal"
                                type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <span x-text="isEdit ? 'Edit' : 'Create'"></span>
                            </button>

                            <button
                                @click="openModal = false"
                                data-modal-hide="default-modal"
                                type="button"
                                class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Cancel</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>