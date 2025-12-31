<div class="main-content">
    <livewire:type.form />
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Types</h5>
                        </div>
                        <button
                            style="background-color:#56C596;"
                            @click="$dispatch('typeModal')"
                            class="btn text-white bg-green-400 btn-sm mb-0"
                            type="button">+&nbsp; New Type</button>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Description</th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Price</th>
                                    {{-- <th class=" text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Image</th> --}}
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($types as $type)
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">{{ $type->name }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $type->description }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $type->price }}</p>
                                    </td>
                                    {{-- <td class="text-center">
                                                @if($type->image)
                                                    <img src="{{ asset('storage/'.$type->image) }}"
                                    alt="{{ $type->name }}"
                                    class="w-8 h-8 object-cover rounded-lg border border-gray-200 shadow cursor-pointer hover:scale-105 transition-transform duration-300"
                                    @click="$dispatch('showImage', { image: '{{ $type->image }}' })">
                                    @else
                                    <span class="text-xs">No Image</span>
                                    @endif
                                    </td> --}}
                                    <td class="text-center">
                                        <button
                                            @click="$dispatch('edit-type', { type: {{ $type->id }} })"
                                            class="text-blue-500">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>

                                        <button
                                            wire:click="confirmDelete({{ $type->id }})" class="text-red-500">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>


                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-exclamation-triangle"></i> Confirm Type Deletion
                        </h5>
                        <button type="button" class="close" wire:click="cancelDelete">
                            &times;
                        </button>
                    </div>
                    <div class="modal-body">
                        @if($typeToDelete)
                            <div class="alert alert-warning">
                                <strong>Warning!</strong> This action cannot be undone.
                            </div>
                            <p>Are you sure you want to delete the following type?</p>
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $typeToDelete->name }}</h6>
                                    <p class="card-text">
                                        <strong>Description:</strong> {{ $typeToDelete->description ?? 'N/A' }}<br>
                                        <strong>Price:</strong> {{ $typeToDelete->price ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelDelete">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            <i class="fas fa-trash"></i> Delete Type
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>