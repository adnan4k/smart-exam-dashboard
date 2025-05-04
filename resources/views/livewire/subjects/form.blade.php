<div x-data="{ openModal: @entangle('openModal') }" class="flex justify-center px-8">
    <div @click.away="openModal = false" x-cloak x-show="openModal" id="default-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto">
        <div x-data="{isEdit:@entangle('is_edit')}" class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="saveSubject">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2" x-text="isEdit ? 'Edit Subject' : 'Create Subject'"></h2>

                    <div class="flex flex-col gap-2 w-full border-gray-400">
                        <!-- Name Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Name</label>
                            <input wire:model="name"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                type="text"
                                placeholder="Enter subject name">
                            @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Year</label>
                            <input wire:model="year"
                                type="text"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                placeholder="e.g., 2023">
                            @error('year') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Exam Type Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Exam Type</label>
                            <select wire:model="typeId" 
                                    wire:change="handleTypeChange($event.target.value)"
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Exam Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('typeId') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Region Dropdown - Only shown when type is regional -->
                        <div x-show="$wire.isRegional">
                            <label class="text-gray-600 dark:text-gray-400">Region</label>
                            <select wire:model="region"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                                <option value="">Select Region</option>
                                <option value="addis_ababa">Addis Ababa (City)</option>
                                <option value="afar">Afar</option>
                                <option value="amhara">Amhara</option>
                                <option value="benishangul_gumuz">Benishangul-Gumuz</option>
                                <option value="dire_dawa">Dire Dawa (City)</option>
                                <option value="gambela">Gambela</option>
                                <option value="harari">Harari</option>
                                <option value="oromia">Oromia</option>
                                <option value="sidama">Sidama</option>
                                <option value="snnpr">Southern Nations, Nationalities, and Peoples' Region</option>
                                <option value="somali">Somali</option>
                                <option value="tigray">Tigray</option>
                            </select>
                            @error('region') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Default Duration Field -->
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Default Duration (minutes)</label>
                            <input wire:model="defaultDuration"
                                type="number"
                                min="1"
                                class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                placeholder="Enter duration in minutes">
                            @error('defaultDuration') <span class="text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button style="background-color:#56C596;" type="submit"
                                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <span x-text="isEdit ? 'Edit' : 'Create'"></span>
                            </button>
                            <button @click="openModal = false" type="button"
                                class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>