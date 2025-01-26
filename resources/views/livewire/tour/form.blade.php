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
                <div>
                        <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2">Create Tour</h2>
                        <div>
                            <button type="button" @click="openModal = false" class="absolute top-0 right-0 p-4">
                                <i class="text-red-500 fas fa-times text-2xl"></i> <!-- Close Icon -->

                            </button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 w-full border-gray-400">

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">
                                Name
                            </label>
                            <input
                                value="{{$title ?? null}}"
                                wire:model="name"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        <div class="flex flex-row  sm:flex-row justify-content-between   ">
                        <div class="mr-4">
                            <label class="text-gray-600 dark:text-gray-400">
                                Duration
                            </label>
                            <input
                                wire:model="duration"
                                
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('duration') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">
                                Tour Code
                            </label>
                            <input
                                value="{{$title ?? null}}"
                                wire:model="tour_code"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('tour_code') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        </div>

                        <div class="flex flex-row lg:flex-row justify-between">
                        <div class="mr-4">
                            <label class="text-gray-600 dark:text-gray-400">
                                Tour Type
                            </label>
                            <input
                                wire:model="tour_type"
                                
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('tour_type') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">
                                Group Size
                            </label>
                            <input
                                wire:model="group_size"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('group_size') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        </div>
                        <div class="flex flex-row  sm:flex-row justify-content-between   ">
                        <div class="mr-4">
                            <label class="text-gray-600 dark:text-gray-400">
                                Transport
                            </label>
                            <input
                                wire:model="transport"
                                
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('transport') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">
                                Destination
                            </label>
                            <input
                                value="{{$title ?? null}}"
                                wire:model="destination"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('destination') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        </div>
                    

                        <!-- Conditional Tour Code Field -->
                   

                        <div>
                            @error('type') <span class="text-red-500">{{ $message }}</span> @enderror
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
                        <div wire:ignore>
                            <label class="text-gray-600 dark:text-gray-400">Package Inclusions</label>
                            <textarea
                                id="inclusions"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                wire:model="inclusions"></textarea>
                            <div>
                                @error('inclusions') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div wire:ignore>
                            <label class="text-gray-600 dark:text-gray-400">Package Exclusions</label>
                            <textarea
                                id="exclusions"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                wire:model="exclusions"></textarea>
                            <div>
                                @error('exclusions') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button
                                data-modal-hide="default-modal"
                                type="submit"
                                class="text-white bg-[#56C596] hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
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
    @script
    <script>
        const inclusions = new SimpleMDE({
            element: document.getElementById("inclusions")
        });
        inclusions.codemirror.on("change", function() {
            $wire.set("inclusions", inclusions.value());
            // console.log(simplemde.value());
        });
        const exclusions = new SimpleMDE({
            element: document.getElementById("exclusions")
        });
        exclusions.codemirror.on("change", function() {
            $wire.set("exclusions", exclusions.value());
            // console.log(simplemde.value());
        });
    </script>
    @endscript
</div>