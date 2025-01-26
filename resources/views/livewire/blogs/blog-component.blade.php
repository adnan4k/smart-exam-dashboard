<div class="main-content">
    <livewire:blogs.form />
    <livewire:components.delete-modal />
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 mx-4">
                <div class="card-header pb-0">
                    <div class="d-flex flex-row justify-content-between">
                        <div>
                            <h5 class="mb-0">All Blogs</h5>
                        </div>
                        <button
                            @click="$dispatch('blogModal')"
                            class="btn bg-[#56C596] text-white btn-sm mb-0"
                            type="button">+&nbsp; New Blog</button>
                    </div>
                </div>

                <div x-data="{ openRow: null }" class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Category</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Author</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Image</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($blogs as $num => $blog)
                                <tr>
                                    <!-- Row Data -->
                                    <td class="ps-4">
                                        <p class="text-xs font-weight-bold mb-0">{{ $num + 1 }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $blog->title }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $blog->first()->category->title ?? '' }}</p>
                                    </td>
                                    <td class="text-center">
                                        <p class="text-xs font-weight-bold mb-0">{{ $blog->author }}</p>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="flex justify-center items-center">
                                            <img src="{{ asset('storage/' . $blog->image) }}" class="w-24 h-24 mb-0" alt="Blog Image" />
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button
                                            @click="openRow === {{ $blog->id }} ? openRow = null : openRow = {{ $blog->id }}">
                                            <i :class="openRow === {{ $blog->id }} ? 'fa-solid fa-minus text-red-400' : 'fa-solid fa-plus text-green-300'"></i>
                                        </button>
                                        <button @click="$dispatch('edit-blog', { blog: {{ $blog->id }} })">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button wire:click="$dispatch('openDeleteModal', { itemId: {{ $blog->id }}, model: '{{ addslashes(App\Models\Blog::class) }}' })">
                                            <i class="fa-solid fa-trash text-red-400"></i>
                                        </button>
                                    </td>
                                </tr>
                                  <div style="width: 100px;">

                                      <!-- Collapsible Description -->
                                      <tr style="width: 200px;" x-cloak x-show="openRow === {{ $blog->id }}" >
                                          <td class="text-center" >
                                              <p class="text-gray-500 break-words whitespace-normal">
                                                  {{ $blog->content ?? 'No description available for this blog.' }}
                                              </p>
                                          </td>
                                      </tr>
                                  </div>


                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>