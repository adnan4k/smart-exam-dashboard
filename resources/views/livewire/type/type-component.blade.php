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
                                            wire:click="delete({{ $type->id }})" class="text-red-500">
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
</div>