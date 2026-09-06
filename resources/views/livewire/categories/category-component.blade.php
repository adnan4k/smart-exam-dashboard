<div x-data="{ confirmDeleteId: null, confirmDeleteTitle: '' }">
    <livewire:categories.form />
    <livewire:components.delete-modal />

    <div class="row">
        <div class="col-12">
            <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-4">
                <!-- Card Header -->
                <div class="card-header border-b border-slate-100 p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h5 class="font-bold text-slate-800 text-base mb-0.5 tracking-tight">Question & Subject Categories</h5>
                            <p class="text-xs text-slate-400 mb-0">Organize syllabus topics, question groupings, and curriculum categories.</p>
                        </div>
                        <button
                            @click="$dispatch('categoryModal')"
                            class="btn-brand self-start sm:self-auto shadow-sm"
                            type="button">
                            <i class="fa-solid fa-plus text-xs"></i> New Category
                        </button>
                    </div>
                </div>

                <!-- Card Body & Table -->
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0 w-full">
                            <thead>
                                <tr>
                                    <th class="text-center w-12">#</th>
                                    <th>Category Title</th>
                                    <th>Description</th>
                                    <th class="text-center">Created At</th>
                                    <th class="text-center w-24">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $num => $category)
                                    <tr>
                                        <td class="text-center text-xs font-semibold text-slate-400">
                                            {{ $num + 1 }}
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#58706D] flex items-center justify-center font-bold text-xs">
                                                    <i class="fas fa-folder"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-bold text-slate-800 mb-0">{{ $category->title }}</p>
                                                    <p class="text-[11px] text-slate-400 mb-0">ID: #{{ $category->id }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs text-slate-600 mb-0 max-w-md line-clamp-1">
                                                {{ $category->description ?: 'No description provided' }}
                                            </p>
                                        </td>
                                        <td class="text-center">
                                            <span class="inline-flex items-center gap-1 text-xs text-slate-600 font-medium">
                                                <i class="far fa-clock text-slate-400 text-[11px]"></i>
                                                {{ $category->created_at ? $category->created_at->format('M d, Y') : '—' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <button
                                                    @click="$dispatch('edit-category', { category: {{ $category->id }} })"
                                                    class="action-icon-btn"
                                                    title="Edit Category">
                                                    <i class="fa-regular fa-pen-to-square"></i>
                                                </button>
                                                <button
                                                    @click="confirmDeleteId = {{ $category->id }}; confirmDeleteTitle = '{{ addslashes($category->title) }}'"
                                                    class="action-icon-btn btn-danger-icon"
                                                    title="Delete Category">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-0">
                                            <x-empty-state
                                                title="No Categories Found"
                                                description="Get started by creating your first category."
                                                icon="fas fa-folder-open"
                                            />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal (Alpine) -->
    <div x-cloak x-show="confirmDeleteId"
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center animate-fade-in"
             @click.away="confirmDeleteId = null">
            <div class="w-14 h-14 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h5 class="text-base font-bold text-slate-800 mb-1">Delete Category</h5>
            <p class="text-xs text-slate-500 mb-4">
                Are you sure you want to delete <span class="font-bold text-slate-700" x-text="'&quot;' + confirmDeleteTitle + '&quot;'"></span>? This action cannot be undone.
            </p>

            <div class="flex items-center justify-end gap-2.5">
                <button type="button" class="btn-brand-outline text-xs px-4 py-2" @click="confirmDeleteId = null">
                    Cancel
                </button>
                <button type="button" class="btn bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg px-4 py-2 flex items-center gap-1.5 transition"
                        @click="$dispatch('deleteItem', { itemId: confirmDeleteId, model: '{{ addslashes(App\Models\Category::class) }}' }); confirmDeleteId = null;">
                    <i class="fas fa-trash text-[11px]"></i> Delete Permanently
                </button>
            </div>
        </div>
    </div>
</div>