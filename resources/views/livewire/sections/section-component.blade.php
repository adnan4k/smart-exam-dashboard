<div class="main-content">
    <livewire:sections.form />
    <livewire:components.delete-modal />
    <div class="row">
        <div class="col-12">

            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Sections</h5>
                        </div>
                        <button
                            class="bg-[#56C596]  hover:bg-green-600 text-white p-2 "
                            style="background-color: #56C596 !important; color: white !important;"
                            @click="$dispatch('sectionModal')"
                            type="button">
                            +&nbsp; New section
                        </button>


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
                                        Title
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Section Type
                                    </th>
                                    <!-- <th class=" text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Content
                                    </th> -->



                                    <th class=" text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ( $sections as $num => $section )
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">{{$num +1}}</p>
                                    </td>
                                    <td>
                                        <div>
                                            <img src="{{ asset('storage/' . $section->image) }}" class="h-12 w-24 me-3" alt="section Image">
                                        </div>
                                    </td>
                                    <td class="">
                                        <p class="text-xs font-weight-bold mb-0">{{$section->title}}</p>
                                    </td>
                                    <td class="">
                                        <p class="text-xs font-weight-bold mb-0">{{$section->type}}</p>
                                    </td>
                                    <!-- <td class="">
                                        <p class="text-xs wrap font-weight-bold mb-0">{!!$section->content!!}</p>
                                    </td> -->


                                    <td class="">

                                        <button
                                            @click="$dispatch('edit-section',{section:{{$section->id}}})"

                                            class="">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button
                                            wire:click="$dispatch('openDeleteModal', { itemId: {{ $section->id }}, model: '{{ addslashes(App\Models\Section::class) }}' })">
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