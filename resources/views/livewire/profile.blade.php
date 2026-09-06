<div>
    <div class="max-w-4xl mx-auto">
        <!-- Profile Banner Card -->
        <div class="card border border-slate-200/80 rounded-2xl bg-white shadow-sm overflow-hidden mb-6">
            <div class="h-28 bg-gradient-to-r from-[#58706D] via-[#7C8A6E] to-[#B0B087] relative"></div>
            <div class="px-6 pb-6 pt-0 relative">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 -mt-12 mb-4">
                    <div class="flex items-end gap-4">
                        <div class="w-20 h-20 rounded-2xl bg-white p-1 shadow-md">
                            <div class="w-full h-full rounded-xl bg-[#58706D] text-white flex items-center justify-center font-bold text-xl uppercase shadow-xs">
                                {{ substr(Auth::user()->name ?? 'Admin', 0, 2) }}
                            </div>
                        </div>
                        <div class="mb-1">
                            <h4 class="font-bold text-slate-800 text-lg tracking-tight mb-0.5">{{ Auth::user()->name }}</h4>
                            <p class="text-xs text-slate-400 mb-0 flex items-center gap-1.5">
                                <i class="fa-regular fa-envelope text-slate-400"></i>
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mb-1">
                        <span class="badge-subtle-brand">{{ ucfirst(Auth::user()->role ?? 'Administrator') }}</span>
                        <span class="badge-subtle-success">{{ ucfirst(Auth::user()->status ?? 'Active') }}</span>
                    </div>
                </div>

                @if (session()->has('message'))
                    <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-[#58706D] text-xs font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600"></i>
                        <span>{{ session('message') }}</span>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Profile Information Form -->
                <form wire:submit.prevent="updateProfile" class="pt-2">
                    <div class="border-t border-slate-100 pt-5 mb-6">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-7 h-7 rounded-lg bg-[#58706D]/10 text-[#58706D] flex items-center justify-center text-xs font-bold">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <h6 class="font-bold text-slate-800 text-sm mb-0">Personal Information</h6>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                                <input type="text" id="name" wire:model="name"
                                       class="input-modern text-xs @error('name') border-rose-500 @enderror"
                                       placeholder="Enter your full name">
                                @error('name') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address <span class="text-rose-500">*</span></label>
                                <input type="email" id="email" wire:model="email"
                                       class="input-modern text-xs @error('email') border-rose-500 @enderror"
                                       placeholder="Enter your email address">
                                @error('email') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="submit" wire:loading.attr="disabled"
                                    class="btn-brand text-xs px-5 py-2.5 shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                                <i class="fa-solid fa-check text-xs"></i>
                                <span wire:loading.remove>Update Profile</span>
                                <span wire:loading>Saving...</span>
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Password Section -->
                <div class="border-t border-slate-100 pt-5 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold">
                                <i class="fa-solid fa-lock"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-slate-800 text-sm mb-0">Security &amp; Password</h6>
                                <p class="text-[11px] text-slate-400 mb-0">Manage password and account protection credentials.</p>
                            </div>
                        </div>
                        <button type="button" class="btn-brand-outline text-xs px-3.5 py-1.5 flex items-center gap-1.5"
                                wire:click="togglePasswordSection">
                            <i class="fa-solid fa-{{ $showPasswordSection ? 'chevron-up' : 'key' }} text-[11px]"></i>
                            <span>{{ $showPasswordSection ? 'Collapse' : 'Change Password' }}</span>
                        </button>
                    </div>

                    @if($showPasswordSection)
                        <form wire:submit.prevent="updatePassword" class="p-4 rounded-xl bg-slate-50/70 border border-slate-200/80 mb-2">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Current Password</label>
                                    <input type="password" id="current_password" wire:model="current_password"
                                           class="input-modern bg-white text-xs @error('current_password') border-rose-500 @enderror"
                                           placeholder="Current password">
                                    @error('current_password') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">New Password</label>
                                    <input type="password" id="new_password" wire:model="new_password"
                                           class="input-modern bg-white text-xs @error('new_password') border-rose-500 @enderror"
                                           placeholder="Minimum 8 characters">
                                    @error('new_password') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="confirm_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Confirm Password</label>
                                    <input type="password" id="confirm_password" wire:model="confirm_password"
                                           class="input-modern bg-white text-xs @error('confirm_password') border-rose-500 @enderror"
                                           placeholder="Repeat new password">
                                    @error('confirm_password') <p class="mt-1 text-[11px] text-rose-500 font-medium">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-2 mt-4">
                                <button type="button" class="btn-brand-outline text-xs px-3.5 py-1.5" wire:click="togglePasswordSection">
                                    Cancel
                                </button>
                                <button type="submit" wire:loading.attr="disabled"
                                        class="btn-brand text-xs px-4 py-2 shadow-sm flex items-center gap-1.5 disabled:opacity-50">
                                    <i class="fa-solid fa-shield-halved text-xs"></i>
                                    <span wire:loading.remove>Update Password</span>
                                    <span wire:loading>Updating...</span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>

                <!-- Account Diagnostics Metadata -->
                <div class="border-t border-slate-100 pt-5">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>
                        <h6 class="font-bold text-slate-800 text-sm mb-0">Account Metadata</h6>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/70">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Account ID</span>
                            <span class="text-xs font-mono font-bold text-slate-800">#{{ Auth::user()->id }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/70">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Permissions</span>
                            <span class="text-xs font-bold text-[#58706D]">{{ ucfirst(Auth::user()->role ?? 'User') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/70">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Account Status</span>
                            <span class="text-xs font-bold text-emerald-700">{{ ucfirst(Auth::user()->status ?? 'Active') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/70">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Last Login</span>
                            <span class="text-[11px] font-medium text-slate-600">
                                {{ Auth::user()->last_login_at ? Auth::user()->last_login_at->format('M d, H:i') : 'Never' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 