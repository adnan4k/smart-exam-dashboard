<div class="main-content">
    <livewire:testimony.form />
    <livewire:components.delete-modal />
    <div class="row">
        <div class="col-12">

            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Testimonies</h5>
                        </div>
                        <a
                            @click="$dispatch('testimonyModal')"
                            class="btn bg-[#56C596] text-white btn-sm mb-0"
                            type="button">+&nbsp; New Testimony</a>
                    </div>
                </div>
                <div>

                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        ID
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Image
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Name
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Position
                                    </th>
                                    <!-- <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Content
                                    </th> -->



                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ( $testimonies as $num => $testimony )
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">{{$num +1}}</p>
                                    </td>
                                    <td>
                                        <div>
                                        <img src="{{ asset('storage/' . $testimony->image) }}" class="h-12 w-24 me-3" alt="testimony Image">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{$testimony->name}}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{$testimony->position}}</p>
                                    </td>
                                    <!-- <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{$testimony->content}}</p>
                                    </td> -->


                                    <td class="text-center">
                                       
                                        <button
                                            @click="$dispatch('edit-testimony',{testimony:{{$testimony->id}}})"

                                            class="">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button
                                            wire:click="$dispatch('openDeleteModal', { itemId: {{ $testimony->id }}, model: '{{ addslashes(App\Models\Testimony::class) }}' })">
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