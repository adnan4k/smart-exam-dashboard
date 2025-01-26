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
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2">Create Biography</h2>

                    <div class="flex flex-col gap-2 w-full border-gray-400">

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">
                                Name
                            </label>
                            <input
                                value="{{$name ?? null}}"
                                wire:model="name"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>


                        <div class="flex flex-row">
                            <div class="mx-2">
                                <label class="text-gray-600 dark:text-gray-400">Position</label>
                                <input
                                    value="{{$position ?? null}}"
                                    wire:model="position"
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                    type="text">
                                <div>
                                    @error('position') <span class="text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mx-2">
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
                        </div>
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Detail</label>
                            <textarea
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                wire:model="detail"></textarea>
                            <div>
                                @error('detail') <span class="text-red-500">{{ $message }}</span> @enderror
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