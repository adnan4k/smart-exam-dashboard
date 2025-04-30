<div x-data="{ openDeleteModal: @entangle('openDeleteModal') }">        
    <!-- Backdrop -->
    <div x-cloak x-show="openDeleteModal" 
         class="fixed inset-0 bg-black bg-opacity-50 z-[100] flex justify-center items-center">
        
        <!-- Modal Container -->
        <div class="relative p-4 w-full max-w-md bg-white rounded-lg shadow dark:bg-gray-700 z-[110]">
            <!-- Close Button -->
            <button type="button" 
                    @click="openDeleteModal = false"
                    class="absolute top-3 right-3 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            
            <!-- Modal Content -->
            <div class="p-4 text-center">
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this?</h3>
                
                <!-- Action Buttons -->
                <div class="flex justify-center gap-3">
                    <button wire:click="deleteItem"
                            type="button"
                            class="py-2.5 px-5 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 dark:bg-red-500 dark:hover:bg-red-600 dark:focus:ring-red-900">
                        Delete
                    </button>
                    <button @click="openDeleteModal=false"
                            type="button" 
                            class="py-2.5 px-5 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>