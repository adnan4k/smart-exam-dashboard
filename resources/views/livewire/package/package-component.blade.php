<div>
    <!-- Top Header Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden p-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-lg shadow-sm">
                            <i class="fas fa-cubes-stacked"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-0.5 tracking-tight">Access Packages</h4>
                            <p class="text-xs text-slate-500 mb-0">Set pricing and manage access for 1st Semester, 2nd Semester, COC Exam, and All Access.</p>
                        </div>
                    </div>
                    <div>
                        <button
                            wire:click="openCreateForm"
                            class="btn-brand text-xs px-4 py-2 rounded-xl shadow-sm flex items-center gap-2"
                            type="button">
                            <i class="fa-solid fa-plus text-xs"></i> Add New Package
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- The 4 Core Package Cards -->
    <div class="row g-3 mb-4">
        <!-- 1st Semester Card -->
        @php $sem1 = $corePackages->get('semester_1'); @endphp
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-blue-200/80 rounded-2xl bg-white shadow-sm overflow-hidden hover:shadow-md transition">
                <div class="p-4 flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base font-bold shadow-xs">
                                <i class="fas fa-book-open"></i>
                            </div>
                            @if($sem1)
                                <button
                                    wire:click="toggleStatus({{ $sem1->id }})"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold transition {{ $sem1->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sem1->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $sem1->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            @endif
                        </div>
                        <h5 class="font-extrabold text-slate-800 text-base mb-1">1st Semester</h5>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-3">
                            {{ $sem1->description ?? 'Unlocks all subjects and questions for 1st Semester.' }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Access Fee</span>
                            <h4 class="text-base font-black text-blue-700 mb-0">
                                ETB {{ number_format($sem1->price ?? 300, 2) }}
                            </h4>
                        </div>
                        @if($sem1)
                            <button
                                wire:click="edit({{ $sem1->id }})"
                                class="action-icon-btn hover:bg-blue-50 hover:text-blue-700"
                                title="Edit 1st Semester Price & Details">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- 2nd Semester Card -->
        @php $sem2 = $corePackages->get('semester_2'); @endphp
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-teal-200/80 rounded-2xl bg-white shadow-sm overflow-hidden hover:shadow-md transition">
                <div class="p-4 flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base font-bold shadow-xs">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            @if($sem2)
                                <button
                                    wire:click="toggleStatus({{ $sem2->id }})"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold transition {{ $sem2->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $sem2->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $sem2->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            @endif
                        </div>
                        <h5 class="font-extrabold text-slate-800 text-base mb-1">2nd Semester</h5>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-3">
                            {{ $sem2->description ?? 'Unlocks all subjects and questions for 2nd Semester.' }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Access Fee</span>
                            <h4 class="text-base font-black text-teal-700 mb-0">
                                ETB {{ number_format($sem2->price ?? 300, 2) }}
                            </h4>
                        </div>
                        @if($sem2)
                            <button
                                wire:click="edit({{ $sem2->id }})"
                                class="action-icon-btn hover:bg-teal-50 hover:text-teal-700"
                                title="Edit 2nd Semester Price & Details">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- COC Exam Card -->
        @php $coc = $corePackages->get('coc'); @endphp
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-amber-200/80 rounded-2xl bg-white shadow-sm overflow-hidden hover:shadow-md transition">
                <div class="p-4 flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base font-bold shadow-xs">
                                <i class="fas fa-certificate"></i>
                            </div>
                            @if($coc)
                                <button
                                    wire:click="toggleStatus({{ $coc->id }})"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold transition {{ $coc->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $coc->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $coc->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            @endif
                        </div>
                        <h5 class="font-extrabold text-slate-800 text-base mb-1">COC Exam</h5>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-3">
                            {{ $coc->description ?? 'Comprehensive COC & Exit Exam preparation materials.' }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Access Fee</span>
                            <h4 class="text-base font-black text-amber-700 mb-0">
                                ETB {{ number_format($coc->price ?? 400, 2) }}
                            </h4>
                        </div>
                        @if($coc)
                            <button
                                wire:click="edit({{ $coc->id }})"
                                class="action-icon-btn hover:bg-amber-50 hover:text-amber-700"
                                title="Edit COC Exam Price & Details">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- All Access Card -->
        @php $allAccess = $corePackages->get('all_access'); @endphp
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card h-100 border border-purple-200/80 rounded-2xl bg-white shadow-sm overflow-hidden hover:shadow-md transition">
                <div class="p-4 flex flex-col justify-between h-full">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base font-bold shadow-xs">
                                <i class="fas fa-infinity"></i>
                            </div>
                            @if($allAccess)
                                <button
                                    wire:click="toggleStatus({{ $allAccess->id }})"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold transition {{ $allAccess->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $allAccess->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    {{ $allAccess->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            @endif
                        </div>
                        <h5 class="font-extrabold text-slate-800 text-base mb-1">All Access</h5>
                        <p class="text-xs text-slate-500 line-clamp-2 mb-3">
                            {{ $allAccess->description ?? 'Full unlimited bundle: 1st Sem + 2nd Sem + COC combined.' }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Access Fee</span>
                            <h4 class="text-base font-black text-purple-700 mb-0">
                                ETB {{ number_format($allAccess->price ?? 700, 2) }}
                            </h4>
                        </div>
                        @if($allAccess)
                            <button
                                wire:click="edit({{ $allAccess->id }})"
                                class="action-icon-btn hover:bg-purple-50 hover:text-purple-700"
                                title="Edit All Access Price & Details">
                                <i class="fa-regular fa-pen-to-square"></i>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INLINE EDIT / CREATE FORM (No Modal) -->
    @if($showForm)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border border-emerald-200/90 rounded-2xl bg-white shadow-md overflow-hidden animate-fade-in">
                    <!-- Form Header -->
                    <div class="px-5 py-3.5 border-b border-slate-100 bg-emerald-50/40 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-[#58706D] text-white flex items-center justify-center text-xs">
                                <i class="fas {{ $isEdit ? 'fa-pen' : 'fa-plus' }}"></i>
                            </span>
                            <div>
                                <h5 class="font-bold text-slate-800 text-sm mb-0">
                                    {{ $isEdit ? 'Edit Package: ' . $name : 'Configure New Package' }}
                                </h5>
                                <p class="text-[11px] text-slate-500 mb-0">Adjust price, package category, and status below.</p>
                            </div>
                        </div>
                        <button
                            wire:click="cancelEdit"
                            type="button"
                            class="text-xs text-slate-500 hover:text-slate-800 font-semibold px-3 py-1 rounded-lg hover:bg-slate-100 transition">
                            <i class="fas fa-times mr-1"></i> Close
                        </button>
                    </div>

                    <!-- Form Body -->
                    <form wire:submit.prevent="savePackage" class="p-5">
                        <div class="row g-3">
                            <!-- Preset Buttons -->
                            <div class="col-12">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Package Category
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        wire:click="selectPreset('semester_1')"
                                        class="text-xs py-2 px-3 rounded-xl border font-semibold transition flex items-center gap-1.5 {{ $selectedPreset === 'semester_1' ? 'border-blue-500 bg-blue-50 text-blue-700 ring-2 ring-blue-500/20 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600' }}">
                                        <i class="fas fa-book-open text-[11px]"></i> 1st Semester
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="selectPreset('semester_2')"
                                        class="text-xs py-2 px-3 rounded-xl border font-semibold transition flex items-center gap-1.5 {{ $selectedPreset === 'semester_2' ? 'border-teal-500 bg-teal-50 text-teal-700 ring-2 ring-teal-500/20 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600' }}">
                                        <i class="fas fa-graduation-cap text-[11px]"></i> 2nd Semester
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="selectPreset('coc')"
                                        class="text-xs py-2 px-3 rounded-xl border font-semibold transition flex items-center gap-1.5 {{ $selectedPreset === 'coc' ? 'border-amber-500 bg-amber-50 text-amber-700 ring-2 ring-amber-500/20 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600' }}">
                                        <i class="fas fa-certificate text-[11px]"></i> COC Exam
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="selectPreset('all_access')"
                                        class="text-xs py-2 px-3 rounded-xl border font-semibold transition flex items-center gap-1.5 {{ $selectedPreset === 'all_access' ? 'border-purple-500 bg-purple-50 text-purple-700 ring-2 ring-purple-500/20 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600' }}">
                                        <i class="fas fa-infinity text-[11px]"></i> All Access
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="selectPreset('custom')"
                                        class="text-xs py-2 px-3 rounded-xl border font-semibold transition flex items-center gap-1.5 {{ $selectedPreset === 'custom' ? 'border-slate-500 bg-slate-100 text-slate-800 ring-2 ring-slate-400/20 shadow-xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-600' }}">
                                        <i class="fas fa-pen text-[11px]"></i> Custom
                                    </button>
                                </div>
                            </div>

                            <!-- Package Name -->
                            <div class="col-12 col-md-4">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Package Name <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    wire:model="name"
                                    type="text"
                                    class="form-control w-full text-xs"
                                    placeholder="e.g. 1st Semester Access">
                                @error('name') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Price (ETB) -->
                            <div class="col-12 col-md-3">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Access Price (ETB) <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        wire:model="price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="form-control w-full text-xs font-bold pl-3 pr-12"
                                        placeholder="300.00">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-bold">ETB</span>
                                </div>
                                @error('price') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12 col-md-5">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Description <span class="text-slate-400 font-normal">(Optional)</span>
                                </label>
                                <input
                                    wire:model="description"
                                    type="text"
                                    class="form-control w-full text-xs"
                                    placeholder="Brief description of materials unlocked...">
                                @error('description') <span class="text-rose-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                            </div>

                            <!-- Status & Submit Bar -->
                            <div class="col-12 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100">
                                <div class="flex items-center gap-2">
                                    <input
                                        wire:model="isActive"
                                        type="checkbox"
                                        id="is_active_toggle"
                                        class="w-4 h-4 text-[#58706D] rounded border-slate-300 focus:ring-[#58706D] cursor-pointer">
                                    <label for="is_active_toggle" class="text-xs font-bold text-slate-700 cursor-pointer mb-0">
                                        Active (Visible for subscription purchase in mobile app)
                                    </label>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        wire:click="cancelEdit"
                                        type="button"
                                        class="btn-brand-outline text-xs px-4 py-2">
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="btn-brand text-xs px-5 py-2 shadow-sm flex items-center gap-1.5">
                                        <i class="fas fa-check text-xs"></i>
                                        {{ $isEdit ? 'Save Changes' : 'Create Package' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- All Packages Full Width Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Table Header & Search -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">All Packages Overview</h5>
                            <p class="text-xs text-slate-400 mb-0">List of all configured subscription tiers, prices, and linked subjects.</p>
                        </div>
                        <div class="relative w-full sm:w-72">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                wire:model.live.debounce.300ms="search"
                                type="text"
                                placeholder="Search by package name or slug..."
                                class="form-control pl-8 w-full text-xs py-2 rounded-xl border-slate-200 focus:border-[#58706D]">
                        </div>
                    </div>
                </div>

                <!-- Table Body -->
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 w-full">
                            <thead>
                                <tr>
                                    <th class="text-center w-12">#</th>
                                    <th>Package Name</th>
                                    <th>Identifier Slug</th>
                                    <th class="text-center">Access Fee</th>
                                    <th class="text-center">Subjects Linked</th>
                                    <th class="text-center">Total Sales</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center w-28">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($packages as $num => $pkg)
                                    <tr class="hover:bg-slate-50/70 transition {{ $isEdit && $packageId === $pkg->id ? 'bg-emerald-50/50' : '' }}">
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $packages->firstItem() + $num }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-3 py-1">
                                                @php
                                                    $badgeClass = match($pkg->slug) {
                                                        'semester_1' => 'bg-blue-100 text-blue-700 border border-blue-200',
                                                        'semester_2' => 'bg-teal-100 text-teal-700 border border-teal-200',
                                                        'coc' => 'bg-amber-100 text-amber-700 border border-amber-200',
                                                        'all_access' => 'bg-purple-100 text-purple-700 border border-purple-200',
                                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                                    };
                                                    $icon = match($pkg->slug) {
                                                        'semester_1' => 'fa-book-open',
                                                        'semester_2' => 'fa-graduation-cap',
                                                        'coc' => 'fa-certificate',
                                                        'all_access' => 'fa-infinity',
                                                        default => 'fa-cube',
                                                    };
                                                @endphp
                                                <div class="w-8 h-8 rounded-xl {{ $badgeClass }} flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                                    <i class="fas {{ $icon }}"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $pkg->name }}</p>
                                                    <p class="text-[11px] text-slate-400 mb-0 line-clamp-1 max-w-sm">
                                                        {{ $pkg->description ?: 'No description provided' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-xs font-mono font-medium px-2 py-0.5 rounded-md {{ $badgeClass }}">
                                                {{ $pkg->slug }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-xs font-black text-slate-800">
                                                ETB {{ number_format($pkg->price, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                                {{ $pkg->subjects_count }} subjects
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                                                {{ $pkg->subscriptions_count }} sales
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                wire:click="toggleStatus({{ $pkg->id }})"
                                                type="button"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold transition cursor-pointer {{ $pkg->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}"
                                                title="Click to toggle status">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $pkg->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                                {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button
                                                    wire:click="edit({{ $pkg->id }})"
                                                    class="action-icon-btn {{ $isEdit && $packageId === $pkg->id ? 'bg-[#58706D] text-white' : '' }}"
                                                    title="Edit Package">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </button>
                                                <button
                                                    wire:click="confirmDelete({{ $pkg->id }})"
                                                    class="action-icon-btn btn-danger-icon"
                                                    title="Delete Package">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-0">
                                            <x-empty-state
                                                title="No Packages Found"
                                                description="Get started by configuring your first access package above."
                                                icon="fas fa-cubes-stacked"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($packages->hasPages())
                        <div class="px-4 py-3 border-t border-slate-100">
                            {{ $packages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (Safety prompt only) -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center animate-fade-in">
                <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h5 class="text-base font-bold text-slate-800 mb-1">Delete Package</h5>
                <p class="text-xs text-slate-500 mb-4">
                    Are you sure you want to delete <span class="font-bold text-slate-700">"{{ $packageToDelete->name ?? '' }}"</span>?
                </p>

                @if($packageToDelete && ($packageToDelete->subscriptions_count ?? 0) > 0)
                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-3 mb-4 text-left text-xs text-rose-700">
                        <i class="fas fa-circle-exclamation mr-1"></i>
                        <strong>Protected:</strong> This package has active subscriptions and cannot be deleted. Deactivate it instead.
                    </div>
                @endif

                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" class="btn-brand-outline text-xs px-4 py-2" wire:click="cancelDelete">
                        Cancel
                    </button>
                    @if(($packageToDelete->subscriptions_count ?? 0) == 0)
                        <button type="button" class="btn bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg px-4 py-2 flex items-center gap-1.5 transition" wire:click="delete">
                            <i class="fas fa-trash text-[11px]"></i> Delete Permanently
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
