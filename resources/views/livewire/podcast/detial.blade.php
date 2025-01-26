<div
    x-data="{ openModal: @entangle('openModal') }"
    class="flex justify-center px-8">

    <div
        x-cloak
        x-show="openModal" id="default-modal" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700" wire:submit.prevent="save">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <div class="flex items-center justify-between p-1 md:p-5 border-b rounded-t dark:border-gray-600">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Podcast Detail
                        </h3>
                        <button
                            @click="openModal = false"
                            type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                            data-modal-hide="default-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <div class="flex justify-center flex-col gap-2 w-full border-gray-400">

                        <div class="overflow-auto max-h-96">
                            <div class="w-full mx-auto p-4 sm:p-6">
                                <div class="grid ">
                                    <div class="rounded overflow-hidden shadow-lg">
                                        <div class="relative">
                                            <div>
                                                <iframe
                                                    class="w-full h-64"
                                                    src="https://www.youtube.com/embed/{{ $podcast->video_id }}"
                                                    frameBorder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowFullScreen
                                                    title={title}></iframe>
                                            </div>
                                            <div class="absolute inset-0 bg-gray-900 opacity-25 hover:opacity-50 transition"></div>
                                            <div class="absolute bottom-0 left-0 bg-indigo-600 text-white px-4 py-2 text-sm">
                                                Episode {{$podcast->episode}}
                                            </div>

                                        </div>
                                        <div class="p-4">
                                            <a href="#" class="font-semibold text-lg text-gray-900 dark:text-gray-200 hover:text-indigo-600 transition">
                                                {{$podcast->title}}
                                            </a>
                                            <p class="text-gray-600 dark:text-gray-400 text-sm mt-2">
                                                {{$podcast->description}}
                                            </p>
                                        </div>
                                        <div class="p-4 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 512 512">
                                                <path d="M256,0C114.837,0,0,114.837,0,256s114.837,256,256,256s256-114.837,256-256S397.163,0,256,0z M277.333,256
                                    c0,11.797-9.536,21.333-21.333,21.333h-85.333c-11.797,0-21.333-9.536-21.333-21.333s9.536-21.333,21.333-21.333h64v-128
                                    c0-11.797,9.536-21.333,21.333-21.333s21.333,9.536,21.333,21.333V256z"></path>
                                            </svg>
                                            <span>Created at {{ \Carbon\Carbon::parse($podcast->created_at)->format('Y-m-d') }}

                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">

                            <button
                                @click="openModal = false"
                                data-modal-hide="default-modal"
                                type="button"
                                class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Close</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>