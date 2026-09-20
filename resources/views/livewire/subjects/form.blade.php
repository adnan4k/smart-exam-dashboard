<div x-data="{ openModal: @entangle('openModal') }">
    <div @click.away="openModal = false" x-cloak x-show="openModal" id="subject-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100">
        <div x-data="{isEdit:@entangle('is_edit')}" class="relative w-full max-w-lg my-6">
            <form class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden" wire:submit.prevent="saveSubject">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <h3 class="text-base font-bold text-slate-800" x-text="isEdit ? 'Edit Subject' : 'Create New Subject'"></h3>
                        <p class="text-xs text-slate-400 mb-0">Specify curriculum details, exam category, and testing parameters.</p>
                    </div>
                    <button @click="openModal = false" type="button" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6 space-y-4">
                    <!-- Subject Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Subject Name <span class="text-rose-500">*</span></label>
                        <input wire:model="name"
                            class="form-control w-full"
                            type="text"
                            placeholder="e.g. Mathematics, Biology, Chemistry">
                        @error('name') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Grid: Exam Type & Year -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Exam Type -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Exam Type <span class="text-rose-500">*</span></label>
                            <select wire:model="typeId" 
                                    wire:change="handleTypeChange($event.target.value)"
                                    class="form-select w-full">
                                <option value="">Select Exam Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('typeId') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>

                        <!-- Year -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Year Group <span class="text-rose-500">*</span></label>
                            <input wire:model="year"
                                type="text"
                                class="form-control w-full"
                                placeholder="e.g. 2024">
                            @error('year') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Access Package / Semester -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Access Package / Semester
                        </label>
                        <select wire:model="packageId" class="form-select w-full">
                            <option value="">No Package / Open (Free)</option>
                            @foreach ($packages as $pkg)
                                <option value="{{ $pkg->id }}">
                                    {{ $pkg->name }} ({{ $pkg->slug }}) — ETB {{ number_format($pkg->price, 2) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-400 mt-1 mb-0">
                            Students will need a subscription for this package (or All Access) to unlock all questions in this subject.
                        </p>
                        @error('packageId') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Region Dropdown - Only shown when type is regional -->
                    <div x-show="$wire.isRegional" x-cloak class="transition-all duration-200">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Administrative Region <span class="text-rose-500">*</span></label>
                        <select wire:model="region" class="form-select w-full">
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
                        @error('region') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Default Duration Field -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Default Duration (Minutes) <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input wire:model="defaultDuration"
                                type="number"
                                min="1"
                                class="form-control w-full"
                                placeholder="e.g. 60">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-medium">mins</span>
                        </div>
                        @error('defaultDuration') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <!-- Sample Subject Toggle -->
                    <div class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-100 rounded-xl">
                        <input type="checkbox" wire:model="isSample" id="isSample"
                            class="mt-1 w-4 h-4 rounded text-[#58706D] focus:ring-[#58706D] border-slate-300 cursor-pointer">
                        <div class="flex-1">
                            <label for="isSample" class="block text-xs font-bold text-slate-800 cursor-pointer">
                                Mark as Sample Subject
                            </label>
                            <p class="text-[11px] text-slate-400 mb-0">
                                Questions under this subject will be visible in the open preview/sample examination pool.
                            </p>
                            @error('isSample')
                                <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2.5 px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                    <button @click="openModal = false" type="button" class="btn-brand-outline text-xs px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit" class="btn-brand text-xs px-5 py-2 shadow-sm">
                        <span wire:loading.remove wire:target="saveSubject">
                            <i class="fas fa-check text-xs"></i> <span x-text="isEdit ? 'Save Changes' : 'Create Subject'"></span>
                        </span>
                        <span wire:loading wire:target="saveSubject" class="flex items-center gap-1.5">
                            <i class="fas fa-spinner fa-spin text-xs"></i> Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>