<div class="main-content">
    <livewire:year-groups.form />

    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Year Groups</h5>
                        </div>
                        <button
                            style="background-color:#56C596;"
                            @click="$dispatch('yearGroupModal')"
                            class="btn text-white bg-green-400 btn-sm mb-0"
                            type="button">+&nbsp; New Year Group</button>
                    </div>
                </div>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        ID
                                    </th>
                                    <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                        Year
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Created At
                                    </th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($yearGroups as $num => $yearGroup)
                                    <tr>
                                        <td class="ps-4">
                                            <p class="text-xs font-weight-bold mb-0">{{ $num + 1 }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs text-center font-weight-bold mb-0">{{ $yearGroup->year }}</p>
                                        </td>
                                        <td class="text-center">
                                            <p class="text-xs font-weight-bold mb-0">{{ $yearGroup->created_at->format('Y-m-d') }}</p>
                                        </td>
                                        <td class="text-center">
                                            <button
                                                @click="$dispatch('edit-yearGroup', { yearGroup: {{ $yearGroup->id }} })"
                                                class="text-blue-500">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button
                                            wire:click="delete({{ $yearGroup->id }})" class="text-red-500">
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