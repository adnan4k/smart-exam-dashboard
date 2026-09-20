<!-- Mobile Toggle Button -->
<button id="sidebarToggle" class="fixed top-4 right-4 z-[999] p-2.5 rounded-xl bg-[#58706D] text-white lg:hidden hover:bg-[#455956] shadow-lg shadow-[#58706D]/30 transition-all flex items-center justify-center" aria-label="Toggle navigation">
    <i class="fas fa-bars text-lg"></i>
</button>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 
             lg:translate-x-0 -translate-x-full transition-transform duration-300 ease-in-out bg-white shadow-sm border border-slate-100" 
       id="sidenav-main">

    <!-- Brand Header -->
    <div class="px-4 py-3.5 border-b border-slate-100 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#58706D]/10 flex items-center justify-center p-1.5 shadow-sm">
            <img src="{{ asset('assets/img/log.png') }}" alt="Smart Exam Logo" class="w-full h-full object-contain">
        </div>
        <div class="flex flex-col">
            <span class="font-bold text-slate-800 text-sm tracking-tight flex items-center gap-1.5">
                Smart Exam
                <span class="text-[10px] font-bold text-[#58706D] bg-[#58706D]/15 px-1.5 py-0.2 rounded">PRO</span>
            </span>
            <span class="text-[11px] font-medium text-slate-400">Admin Console</span>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div id="sidebar-container" class="z-[999] h-[calc(100vh-6rem)] overflow-y-auto no-scrollbar px-3 py-2">
        <ul id="navbar-nav" class="navbar-nav space-y-1">
            
            <!-- GROUP: MAIN -->
            <li class="pt-2 pb-1 px-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Main</span>
            </li>

            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('dashboard') }}">
                    <div class="nav-icon">
                        <i class="fas fa-chart-pie text-xs"></i>
                    </div>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- GROUP: CURRICULUM -->
            <li class="pt-3 pb-1 px-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Curriculum</span>
            </li>

            <!-- Questions -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'questions' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('questions') }}">
                    <div class="nav-icon">
                        <i class="fas fa-circle-question text-xs"></i>
                    </div>
                    <span class="nav-text">Questions</span>
                </a>
            </li>

            <!-- Year Group -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'year-group' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('year-group') }}">
                    <div class="nav-icon">
                        <i class="fas fa-calendar-days text-xs"></i>
                    </div>
                    <span class="nav-text">Year Groups</span>
                </a>
            </li>

            <!-- Subject -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'subject' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('subject') }}">
                    <div class="nav-icon">
                        <i class="fas fa-book-bookmark text-xs"></i>
                    </div>
                    <span class="nav-text">Subjects</span>
                </a>
            </li>

            <!-- Chapter -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'chapter' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('chapter') }}">
                    <div class="nav-icon">
                        <i class="fas fa-layer-group text-xs"></i>
                    </div>
                    <span class="nav-text">Chapters</span>
                </a>
            </li>

            <!-- Type -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'type' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('type') }}">
                    <div class="nav-icon">
                        <i class="fas fa-tags text-xs"></i>
                    </div> 
                    <span class="nav-text">Exam Types</span>
                </a>
            </li>

            <!-- GROUP: COMPETITIONS -->
            <li class="pt-3 pb-1 px-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Competitions</span>
            </li>

            <!-- Contests -->
            <li class="nav-item">
                <a class="nav-link {{ str_starts_with(Route::currentRouteName() ?? '', 'contests.') ? 'is-active' : '' }}"
                    href="{{ route('contests.index') }}">
                    <div class="nav-icon">
                        <i class="fas fa-trophy text-xs"></i>
                    </div>
                    <span class="nav-text">Contests</span>
                </a>
            </li>

            <!-- Contest bank -->
            <li class="nav-item">
                <a class="nav-link {{ str_starts_with(Route::currentRouteName() ?? '', 'contest-questions.') ? 'is-active' : '' }}"
                    href="{{ route('contest-questions.index') }}">
                    <div class="nav-icon">
                        <i class="fas fa-vault text-xs"></i>
                    </div>
                    <span class="nav-text">Contest Bank</span>
                </a>
            </li>

            <!-- GROUP: LEARNING MEDIA -->
            <li class="pt-3 pb-1 px-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Learning Media</span>
            </li>

            <!-- Notes -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'notes' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('notes') }}">
                    <div class="nav-icon">
                        <i class="fas fa-file-lines text-xs"></i>
                    </div>
                    <span class="nav-text">Notes</span>
                </a>
            </li>

            <!-- Videos -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'videos' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('videos') }}">
                    <div class="nav-icon">
                        <i class="fas fa-video text-xs"></i>
                    </div>
                    <span class="nav-text">Videos</span>
                </a>
            </li>

            <!-- GROUP: ADMINISTRATION -->
            <li class="pt-3 pb-1 px-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Administration</span>
            </li>

            <!-- Users -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'users' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('users') }}">
                    <div class="nav-icon">
                        <i class="fas fa-users-gear text-xs"></i>
                    </div>
                    <span class="nav-text">Users</span>
                </a>
            </li>

            <!-- Subscription -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'subscription' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('subscription') }}">
                    <div class="nav-icon">
                        <i class="fas fa-credit-card text-xs"></i>
                    </div>
                    <span class="nav-text">Subscriptions</span>
                </a>
            </li>

            <!-- Packages -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'packages' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('packages') }}">
                    <div class="nav-icon">
                        <i class="fas fa-cubes-stacked text-xs"></i>
                    </div>
                    <span class="nav-text">Packages</span>
                </a>
            </li>

            <!-- Referrals -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'referral' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('referral') }}">
                    <div class="nav-icon">
                        <i class="fas fa-share-nodes text-xs"></i>
                    </div>
                    <span class="nav-text">Referrals</span>
                </a>
            </li>

            <!-- Referral Setting -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'referral-setting' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('referral-setting') }}">
                    <div class="nav-icon">
                        <i class="fas fa-sliders text-xs"></i>
                    </div>
                    <span class="nav-text">Referral Settings</span>
                </a>
            </li>

            <!-- Notifications -->
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'notifications' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('notifications') }}">
                    <div class="nav-icon">
                        <i class="fas fa-bell text-xs"></i>
                    </div>
                    <span class="nav-text">Notifications</span>
                </a>
            </li>

            <!-- API Documentation -->
            <li class="nav-item">
                <a class="nav-link" href="{{ url('/docs/api') }}" target="_blank">
                    <div class="nav-icon">
                        <i class="fas fa-book-open text-xs"></i>
                    </div>
                    <span class="nav-text">API Docs</span>
                    <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-400 ms-auto me-2"></i>
                </a>
            </li>

            <!-- GROUP: ACCOUNT -->
            <li class="pt-3 pb-1 px-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Account</span>
            </li>

            <!-- Profile -->
            <li class="nav-item pb-4">
                <a class="nav-link {{ Route::currentRouteName() == 'profile' ? 'is-active' : '' }}"
                    wire:navigate href="{{ route('profile') }}">
                    <div class="nav-icon">
                        <i class="fas fa-user-circle text-xs"></i>
                    </div>
                    <span class="nav-text">Profile</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

<!-- Backdrop for mobile -->
<div id="sidebarBackdrop" 
     class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm lg:hidden hidden z-[50]">
</div>

<!-- Update the JavaScript with Livewire-compatible implementation -->
<script>
// Global sidebar state
window.sidebarState = {
    isOpen: false,
    isAnimating: false,
    isInitialized: false,
    
    init: function() {
        if (this.isInitialized) {
            this.cleanup();
        }
        
        this.sidebar = document.getElementById('sidenav-main');
        this.toggle = document.getElementById('sidebarToggle');
        this.backdrop = document.getElementById('sidebarBackdrop');
        
        if (!this.sidebar || !this.toggle) {
            console.error('Sidebar elements not found');
            return;
        }
        
        this.bindEvents();
        this.setInitialState();
        this.isInitialized = true;
    },
    
    cleanup: function() {
        // Remove all event listeners
        if (this.toggle) {
            this.toggle.removeEventListener('click', this.handleToggleClick);
            this.toggle.removeEventListener('touchstart', this.handleTouchStart);
            this.toggle.removeEventListener('touchend', this.handleTouchEnd);
        }
        
        if (this.backdrop) {
            this.backdrop.removeEventListener('click', this.handleBackdropClick);
        }
        
        document.removeEventListener('keydown', this.handleKeydown);
        document.removeEventListener('click', this.handleDocumentClick);
        window.removeEventListener('resize', this.handleResize);
    },
    
    setInitialState: function() {
        // Ensure sidebar starts closed on mobile
        if (window.innerWidth < 1024) {
            this.sidebar.classList.add('-translate-x-full');
            this.backdrop.classList.add('hidden');
            this.isOpen = false;
        }
    },
    
    bindEvents: function() {
        // Bind events with proper context
        this.handleToggleClick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggleSidebar();
        };
        
        this.handleBackdropClick = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.closeSidebar();
        };
        
        this.handleTouchStart = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };
        
        this.handleTouchEnd = (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggleSidebar();
        };
        
        this.handleKeydown = (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeSidebar();
            }
        };
        
        this.handleDocumentClick = (e) => {
            if (this.isOpen && window.innerWidth < 1024) {
                const navLink = e.target.closest('.nav-link');
                if (navLink) {
                    setTimeout(() => this.closeSidebar(), 100);
                }
            }
        };
        
        this.handleResize = () => {
            if (window.innerWidth >= 1024) {
                this.openSidebar();
            } else {
                this.closeSidebar();
            }
        };
        
        // Add event listeners
        this.toggle.addEventListener('click', this.handleToggleClick);
        this.toggle.addEventListener('touchstart', this.handleTouchStart);
        this.toggle.addEventListener('touchend', this.handleTouchEnd);
        
        this.backdrop.addEventListener('click', this.handleBackdropClick);
        
        document.addEventListener('keydown', this.handleKeydown);
        document.addEventListener('click', this.handleDocumentClick);
        window.addEventListener('resize', this.handleResize);
    },
    
    toggleSidebar: function() {
        if (this.isAnimating) return;
        
        if (this.isOpen) {
            this.closeSidebar();
        } else {
            this.openSidebar();
        }
    },
    
    openSidebar: function() {
        if (this.isAnimating) return;
        
        this.isAnimating = true;
        this.isOpen = true;
        
        this.sidebar.classList.remove('-translate-x-full');
        this.backdrop.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            this.isAnimating = false;
        }, 300);
    },
    
    closeSidebar: function() {
        if (this.isAnimating) return;
        
        this.isAnimating = true;
        this.isOpen = false;
        
        this.sidebar.classList.add('-translate-x-full');
        this.backdrop.classList.add('hidden');
        document.body.style.overflow = '';
        
        setTimeout(() => {
            this.isAnimating = false;
        }, 300);
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    window.sidebarState.init();
});

// Initialize on Livewire navigation
document.addEventListener('livewire:navigated', function() {
    setTimeout(() => {
        window.sidebarState.init();
    }, 100);
});

// Initialize on Livewire page loads
document.addEventListener('livewire:load', function() {
    setTimeout(() => {
        window.sidebarState.init();
    }, 100);
});

// Initialize on any Livewire updates
document.addEventListener('livewire:update', function() {
    setTimeout(() => {
        window.sidebarState.init();
    }, 100);
});
</script>

<!-- Update the styles to maintain consistent design -->
<style>
    #sidenav-main {
        z-index: 55 !important;
    }

    #sidenav-main .nav-link {
        display: flex !important;
        align-items: center !important;
        gap: 0.65rem !important;
        padding: 0.45rem 0.75rem !important;
        font-size: 0.82rem !important;
        border-radius: 0.75rem !important;
        color: #475569 !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    #sidenav-main .nav-link .nav-icon {
        width: 30px !important;
        min-width: 30px !important;
        height: 30px !important;
        border-radius: 0.5rem !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        background-color: #f1f5f9;
        color: #64748b;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    #sidenav-main .nav-link .nav-text {
        font-weight: 500 !important;
        letter-spacing: -0.01em !important;
        line-height: 1.2 !important;
    }
    #sidenav-main .nav-link.is-active {
        background-color: #58706D !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px -2px rgba(88, 112, 109, 0.35) !important;
    }
    #sidenav-main .nav-link.is-active .nav-text {
        font-weight: 600 !important;
        color: #ffffff !important;
    }
    #sidenav-main .nav-link.is-active .nav-icon {
        background-color: rgba(255, 255, 255, 0.22) !important;
        color: #ffffff !important;
    }
    #sidenav-main .nav-link:not(.is-active):hover {
        background-color: #f1f5f9 !important;
        color: #58706D !important;
    }
    #sidenav-main .nav-link:not(.is-active):hover .nav-icon {
        background-color: rgba(88, 112, 109, 0.12) !important;
        color: #58706D !important;
    }

    #sidebarToggle {
        position: fixed !important;
        display: block !important;
        right: 1rem !important;
        z-index: 999 !important;
        /* Improve touch target size on mobile */
        min-width: 44px !important;
        min-height: 44px !important;
        /* Prevent text selection */
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
        /* Improve touch responsiveness */
        -webkit-tap-highlight-color: transparent !important;
    }

    @media (min-width: 1024px) {
        #sidebarToggle {
            display: none !important;
        }
    }

    /* Ensure backdrop is below sidebar but above content */
    #sidebarBackdrop {
        z-index: 54 !important;
    }

    @media (max-width: 1024px) {
        #sidenav-main {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            width: 250px !important;
            max-width: 250px !important;
            margin: 1rem !important;
            transform: translateX(-100%);
            border-radius: 1rem;
            /* Improve mobile performance */
            -webkit-transform: translateX(-100%);
            -webkit-transition: -webkit-transform 0.3s ease-in-out;
            transition: transform 0.3s ease-in-out;
        }

        #sidenav-main:not(.-translate-x-full) {
            transform: translateX(0);
            -webkit-transform: translateX(0);
        }

        #sidebar-container {
            height: 100% !important;
            overflow-y: auto !important;
            padding: 1rem;
            /* Improve scrolling on mobile */
            -webkit-overflow-scrolling: touch;
        }
    }

    /* Remove the black background from main content */
    .main-content {
        background: transparent !important;
    }

    @media (min-width: 1024px) {
        .main-content {
            margin-left: 17.125rem !important;
        }
        .main-content .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }
    }

    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }
    }
</style>

