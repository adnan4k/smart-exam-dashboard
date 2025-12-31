<div x-data="{ openModal: @entangle('openModal') }" class="flex justify-center px-8">
    <div @click.away="openModal = false"
         x-cloak
         x-show="openModal"
         id="notification-modal"
         tabindex="-1"
         aria-hidden="true"
         class="fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-50 overflow-y-auto">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <form class="relative bg-white rounded-lg shadow dark:bg-gray-700"
                  wire:submit.prevent="saveNotification">
                <div class="flex flex-wrap border shadow rounded-lg p-3 dark:bg-gray-600">
                    <h2 class="text-xl text-gray-600 dark:text-gray-300 pb-2"
                        x-text="@entangle('is_edit') ? 'Edit Notification' : 'Create Notification'"></h2>

                    <div class="flex flex-col gap-2 w-full border-gray-400">
                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Title</label>
                            <input wire:model="title"
                                   class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                   type="text"
                                   placeholder="Hello Freshmen students!">
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Message</label>
                            <textarea wire:model="body"
                                      rows="4"
                                      class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100"
                                      placeholder="Get ready to make your first year easy..."></textarea>
                            @error('body') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Image (optional)</label>
                            <input wire:model="image"
                                   type="file"
                                   accept="image/*"
                                   class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100">
                            @error('image') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            @if($existing_image_url)
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 mb-1">Current image:</p>
                                    @php
                                        $imageUrl = $existing_image_url;
                                        // Check if it's already a full URL
                                        $isFullUrl = filter_var($imageUrl, FILTER_VALIDATE_URL);
                                        
                                        // If it's not a full URL and doesn't start with storage/, add it
                                        if (!$isFullUrl && !str_starts_with($imageUrl, 'storage/') && !str_starts_with($imageUrl, '/storage/')) {
                                            $imageUrl = asset('storage/' . $imageUrl);
                                        } elseif (!$isFullUrl) {
                                            // If it already has storage/ but isn't a full URL, make it one
                                            $imageUrl = asset($imageUrl);
                                        }
                                    @endphp
                                    <img src="{{ $imageUrl }}" 
                                         alt="Current notification image" 
                                         class="max-w-xs h-32 object-cover rounded border"
                                         onerror="this.style.display='none'">
                                </div>
                            @endif
                            
                            @if($image)
                                <div class="mt-2">
                                    <p class="text-xs text-gray-500 mb-1">New image preview:</p>
                                    <p class="text-xs text-blue-500 mb-1">File selected: {{ $image->getClientOriginalName() }}</p>
                                    <p class="text-xs text-gray-400">Preview will be available after upload</p>
                                </div>
                            @endif
                            
                            <p class="text-xs text-gray-500 mt-1">Max file size: 2MB. Supported formats: JPG, PNG, GIF</p>
                        </div>

                        <div>
                            <label class="text-gray-600 dark:text-gray-400">Exam Type <span class="text-red-500">*</span></label>
                            <select wire:model="type_id"
                                    class="w-full py-3 border border-slate-200 rounded-lg px-3 focus:outline-none focus:border-slate-500 hover:shadow dark:bg-gray-600 dark:text-gray-100 @error('type_id') border-red-500 @enderror">
                                <option value="">Select Exam Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('type_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            <p class="text-xs text-gray-500 mt-1">Only users subscribed to this exam type will receive this notification</p>
                        </div>

                        <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                            <button style="background-color:#56C596;" type="submit"
                                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <span x-show="!@entangle('is_edit')">Create</span>
                                <span x-show="@entangle('is_edit')">Save Changes</span>
                            </button>
                            <button @click="openModal = false" type="button"
                                    class="py-2.5 px-5 ms-3 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:ring-gray-100 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


