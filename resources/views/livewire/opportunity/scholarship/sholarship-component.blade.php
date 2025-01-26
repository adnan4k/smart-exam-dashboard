<div class="main-content">
    <livewire:opportunity.scholarship.form />
    <livewire:components.delete-modal />
    <div class="row">
        <div class="col-12">

            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Scholarshis</h5>
                        </div>
                        <button
                            @click="$dispatch('scholarshipModal')"
                            class="btn bg-green-400 btn-sm mb-0"
                            type="button">+&nbsp; New Scholarship</button>
                    </div>
                </div>
                <div>

                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Author</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>

                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Image</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scholarships as $num => $scholarship)
                                <tr>
                                    <!-- Row Data -->
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">{{ $num + 1 }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $scholarship->title }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $scholarship->first()->categories ?? '' }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $scholarship->author }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $scholarship->date->format('Y-m-d') }}</p>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="flex justify-center items-center">
                                            <img src="{{ asset('storage/' . $scholarship->image) }}" class="w-24 h-24 mb-0" alt="Blog Image" />
                                        </div>
                                    </td>
                                    <td class="text-center">

                                        <button @click="$dispatch('edit-scholarship', { scholarship: {{ $scholarship->id }} })">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click="$dispatch('openDeleteModal', { itemId: {{ $scholarship->id }}, model: '{{ addslashes(App\Models\Scholarship::class) }}' })">
                                            <i class="fa-solid fa-trash text-red-400"></i>
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