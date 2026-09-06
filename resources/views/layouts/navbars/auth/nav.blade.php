<nav class="navbar navbar-main navbar-expand-lg px-4 py-2.5 mx-3 mt-3 shadow-sm rounded-2xl bg-white/80 backdrop-blur-md border border-slate-100" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid px-0 flex items-center justify-between">
        
        <!-- Breadcrumb & Title -->
        <div class="flex flex-col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent mb-1 p-0 flex items-center gap-1.5 text-xs">
                    <li class="breadcrumb-item">
                        <a class="text-slate-400 hover:text-[#58706D] flex items-center gap-1" href="{{ route('dashboard') }}">
                            <i class="fas fa-home text-[11px]"></i>
                            <span>Console</span>
                        </a>
                    </li>
                    <li class="text-slate-300">/</li>
                    @php
                        $routeName = Route::currentRouteName() ?? 'Dashboard';
                        $cleanTitle = ucwords(str_replace(['-', '.', '_'], ' ', preg_replace('/(\.index|\.show|\.create|\.edit)$/', '', $routeName)));
                        if (empty($cleanTitle) || $cleanTitle === ' ') $cleanTitle = 'Dashboard';
                    @endphp
                    <li class="breadcrumb-item font-semibold text-[#58706D]" aria-current="page">
                        {{ $cleanTitle }}
                    </li>
                </ol>
            </nav>
            <h5 class="font-bold text-slate-800 text-lg mb-0 tracking-tight">
                {{ $cleanTitle }}
            </h5>
        </div>

        <!-- Right-side Actions & User Profile -->
        <div class="flex items-center gap-3">
            
            <!-- Quick System Status Badge -->
            <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-[#58706D]/10 border border-[#58706D]/20 text-[#58706D] text-xs font-medium">
                <span class="w-2 h-2 rounded-full bg-[#58706D] animate-pulse"></span>
                <span>Active Session</span>
            </div>

            <!-- User Profile Pill -->
            <div class="flex items-center gap-2.5 ps-2 border-s border-slate-200">
                <div class="w-8 h-8 rounded-xl bg-[#58706D] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                </div>
                <div class="hidden md:flex flex-col text-left">
                    <span class="text-xs font-semibold text-slate-800 leading-tight">
                        {{ auth()->user()->name ?? 'Admin User' }}
                    </span>
                    <span class="text-[10px] font-medium text-slate-400 capitalize">
                        {{ auth()->user()->role ?? 'Administrator' }}
                    </span>
                </div>
            </div>

            <!-- Sign Out Button -->
            <div class="ms-1">
                <livewire:auth.logout />
            </div>

        </div>
    </div>
</nav>