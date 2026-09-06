<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden bg-slate-50"
     style="background-image: radial-gradient(at 0% 0%, rgba(88, 112, 109, 0.12) 0px, transparent 50%), radial-gradient(at 100% 100%, rgba(124, 138, 110, 0.12) 0px, transparent 50%);">
    
    <!-- Background subtle shapes -->
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-[#58706D]/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 rounded-full bg-[#7C8A6E]/5 blur-3xl pointer-events-none"></div>

    <div class="w-full relative z-10 mx-auto" style="max-width: 440px;">
        <!-- Brand Header -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white shadow-md shadow-[#58706D]/15 border border-slate-100 p-2.5 mb-3 group hover:scale-105 transition-transform">
                <img src="{{ asset('assets/img/log.png') }}" alt="Smart Exam Logo" class="w-full h-full object-contain">
            </div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mb-1">Smart Exam Console</h2>
            <p class="text-xs text-slate-500 max-w-xs mx-auto">Sign in with your administrative credentials to manage curriculum, exams, and contests.</p>
        </div>

        <!-- Login Card -->
        <div class="card bg-white border border-slate-200/80 rounded-2xl p-6 sm:p-8 shadow-xl shadow-slate-200/50">
            <form wire:submit="login" class="space-y-4">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        {{ __('Email Address') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-slate-400 z-10">
                            <i class="fas fa-envelope text-xs"></i>
                        </div>
                        <input wire:model.live="email" id="email" type="email" 
                               class="form-control ps-5 @error('email') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror"
                               style="padding-left: 2.25rem !important;"
                               placeholder="admin@example.com" 
                               required 
                               autocomplete="email" 
                               autofocus>
                    </div>
                    @error('email') 
                        <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-0">
                            {{ __('Password') }}
                        </label>
                        <a href="{{ route('forgot-password') }}" class="text-xs font-semibold text-[#58706D] hover:underline text-decoration-none">
                            Forgot?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none text-slate-400 z-10">
                            <i class="fas fa-lock text-xs"></i>
                        </div>
                        <input wire:model.live="password" id="password" type="password" 
                               class="form-control @error('password') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror"
                               style="padding-left: 2.25rem !important;"
                               placeholder="••••••••" 
                               required 
                               autocomplete="current-password">
                    </div>
                    @error('password') 
                        <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input wire:model="remember_me" id="remember-me" type="checkbox" 
                           class="w-4 h-4 rounded text-[#58706D] focus:ring-[#58706D] border-slate-300">
                    <label for="remember-me" class="ms-2 block text-xs text-slate-600 font-medium">
                        Remember this session
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="btn-brand w-full justify-center py-2.5 rounded-xl text-sm font-bold shadow-md shadow-[#58706D]/20 hover:shadow-lg transition-all"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="login" class="flex items-center gap-2">
                            <i class="fas fa-arrow-right-to-bracket text-xs"></i>
                            {{ __('Sign In to Dashboard') }}
                        </span>
                        <span wire:loading wire:target="login" class="flex items-center gap-2">
                            <i class="fas fa-circle-notch fa-spin text-xs"></i>
                            {{ __('Authenticating...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer Note -->
        <p class="text-center text-xs text-slate-400 mt-6 flex items-center justify-center gap-1.5">
            <i class="fas fa-shield-halved text-[11px]"></i>
            Encrypted Administrative Session &middot; Smart Exam
        </p>
    </div>
</div>