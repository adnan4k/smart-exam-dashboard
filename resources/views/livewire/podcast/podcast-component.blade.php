<div class="main-content">
    <livewire:podcast.form />
    <livewire:components.delete-modal />
    <livewire:podcast.detial/>

    <div class="row">
        <div class="col-12">

            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Podcasts</h5>
                        </div>
                        <button
                            @click="$dispatch('podcastModal')"
                            class="btn bg-green-400 btn-sm mb-0"
                            type="button">
                            +&nbsp; New Podcast
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
                                        Video
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Title
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Episode
                                    </th>
                                    <!-- <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Description
                                    </th> -->

                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Creation Date
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($podcasts as $num => $podcast )
                                <tr>
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">{{$num +1}}</p>
                                    </td>
                                    <td>
                                        <div>
                                            <iframe
                                                className="w-full h-10 mb-4"
                                                src="https://www.youtube.com/embed/{{ $podcast->video_id }}"
                                                frameBorder="0"
                                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                allowFullScreen
                                                title={title}></iframe>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{$podcast->title}}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{$podcast->episode}}</p>
                                    </td>
                                    <!-- <td class="text-center">
                                        <p class="text-xs font-bold mb-0 max-w-[10rem] truncate" title="{{$podcast->description}}">
                                            {{$podcast->description}}
                                        </p>
                                    </td> -->

                                    <td class="text-center">
                                        <span class="text-secondary text-xs font-weight-bold">{{$podcast->created_at->format('Y-m-d')}}</span>
                                    </td>
                                    <td class="text-center">
                                        <button
                                        wire:click="$dispatch('openDetailModal', { podcast: {{ $podcast->id }} })" 
                                        >
                                            <i class="fa-solid fa-eye text-green-300"></i>

                                        </button>
                                        <button
                                        @click="$dispatch('edit-podcast',{podcast:{{$podcast->id}}})"

                                        class="">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button
                                            wire:click="$dispatch('openDeleteModal', { itemId: {{ $podcast->id }}, model: '{{ addslashes(App\Models\Podcast::class) }}' })">
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
    {{ $podcasts->links() }}

</div>