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
                        <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2">Create Schedule</h2>
                        <div>
                            <button type="button" @click="openModal = false" class="absolute top-0 right-0 p-4">
                                <i class="text-red-500 fas fa-times text-2xl"></i> <!-- Close Icon -->

                            </button>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 w-full border-gray-400">

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">
                                Day Number
                            </label>
                            <input
                                wire:model="day_number"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text">
                            <div>
                                @error('day_number') <span class="text-red-500">{{ $message }}</span> @enderror
                            </div>

                        </div>
                        @if ($tours)

                        <div class="w-full max-w-sm min-w-[200px]">
                            <label class="text-gray-600 dark:text-gray-400">
                                Seletct Tour
                            </label>
                            <div class="relative">
                                <select
                                    wire:model="tour_id"

                                    class="w-full bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded pl-3 pr-8 py-2 transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-400 shadow-sm focus:shadow-md appearance-none cursor-pointer">
                                    <option selected>---Select Tour---</option>
                                    @foreach ($tours as $tour )
                                    <option value="{{$tour->id}}"> {{$tour->name}}</option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                        @endif



                        <div>
                            @error('type') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>




                        <div wire:ignore>
                            <label class="text-gray-600 dark:text-gray-400">description</label>
                            <textarea
                                id="description"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                wire:model="description"></textarea>
                            <div>
                                @error('description') <span class="text-red-500">{{ $message }}</span> @enderror
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
            element: document.getElementById("description")
        });
        simplemde.codemirror.on("change", function() {
            $wire.set("description", simplemde.value());
            // console.log(simplemde.value());
        });
    </script>
    @endscript
</div>