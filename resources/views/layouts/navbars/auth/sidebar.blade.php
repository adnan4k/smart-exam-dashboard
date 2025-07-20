<!-- Update the toggle button position to right side -->
<button id="sidebarToggle" class="fixed top-4 right-4 z-[999] p-2.5 rounded-lg bg-[#56C596] text-white lg:hidden hover:bg-[#4ab485] transition-colors">
    <i class="fas fa-bars text-lg"></i>
</button>

<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3 
             lg:translate-x-0 -translate-x-full transition-transform duration-300 ease-in-out bg-white" 
       id="sidenav-main">
    <div id="sidebar-container" class="z-[999] h-[calc(100vh-2rem)] overflow-y-auto">
        <ul id="navbar-nav" class="navbar-nav">
            
            <!-- Dashboard -->
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('dashboard') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-tachometer-alt text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            <!-- Questions -->
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'questions' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('questions') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-question-circle text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Questions</span>
                </a>
            </li>

            <!-- Year Group -->
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'year-group' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('year-group') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-calendar-alt text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Year Groups</span>
                </a>
            </li>

            <!-- Subject -->
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'subject' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('subject') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-book text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Subject</span>
                </a>
            </li>
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'chapter' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('chapter') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-book text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Chapter</span>
                </a>
            </li>
                  <!-- Subject -->
                  <li class="nav-item pb-2">
                    <a class="nav-link {{ Route::currentRouteName() == 'type' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                        wire:navigate href="{{ route('type') }}">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-book text-dark"></i>
                        </div> 
                        <span class="nav-link-text ms-1">Type</span>
                    </a>
                </li>

            <!-- Subscription -->
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'subscription' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('subscription') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-credit-card text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Subscription</span>
                </a>
            </li>

            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'referral' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('referral') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-credit-card text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Referrals</span>
                </a>
            </li>
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'referral-setting' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('referral-setting') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-credit-card text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Referral Setting</span>
                </a>
            </li>

            <!-- Users -->
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'users' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('users') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-users text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Users</span>
                </a>
            </li>


        </ul>
    </div>
</aside>

<!-- Update the backdrop to only show when sidebar is open -->
<div id="sidebarBackdrop" 
     class="fixed inset-0 bg-black/50 lg:hidden hidden z-[50]">
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
    }

    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }
    }
</style>

