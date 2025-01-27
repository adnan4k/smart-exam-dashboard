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
                        <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2">Create Section</h2>
                        <div>
                            <button type="button" @click="openModal = false" class="absolute top-0 right-0 p-4">
                                <i class="text-red-500 fas fa-times text-2xl"></i> <!-- Close Icon -->

                            </button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 w-full border-gray-400">

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">
                                Title
                            </label>
                            <input
                                value="{{$title ?? null}}"
                                wire:model="title"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('title') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        <div class="w-full max-w-sm min-w-[200px]">
                            <label class="text-gray-600 dark:text-gray-400">
                                Type
                            </label>
                            <div class="relative">
                                <select
                                    wire:model="type"
                                    x-data
                                    x-on:change="$dispatch('type-changed', { value: $event.target.value })"
                                    class="w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded pl-3 pr-8 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-400 shadow-sm focus:shadow-md appearance-none cursor-pointer">
                                    <option selected>---Select Section---</option>
                                    <option value="about">About</option>
                                    <option value="why-choose-us">Why Choose Us</option>
                                    <option value="destination">Destination</option>
                                        <option value="testimony">Testimony</option>
                                        <option value="package">Packages</option>
                                </select>
                            </div>
                        </div>

                        <!-- Conditional Tour Code Field -->
                        <div x-data="{ show: @entangle('type').defer === 'package' }"
                            x-on:type-changed.window="show = $event.detail.value === 'package'">
                            <template x-if="show">
                                <div class="mt-4">
                                    <label class="text-gray-600 dark:text-gray-400">
                                        Tour Code
                                    </label>
                                    <input
                                        wire:model="code"
                                        class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                        type="text">
                                    <div>
                                        @error('code') <span class="text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </template>
                        </div>

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
                            <label class="text-gray-600 dark:text-gray-400">Content</label>
                            <textarea
                                id="content"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                wire:model="content"></textarea>
                            <div>
                                @error('content') <span class="text-red-500">{{ $message }}</span> @enderror
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
        const simplemde = new SimpleMDE({
            element: document.getElementById("content")
        });
        simplemde.codemirror.on("change", function() {
            $wire.set("content", simplemde.value());
            // console.log(simplemde.value());
        });
    </script>
    @endscript
</div>