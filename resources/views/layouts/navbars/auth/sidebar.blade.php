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

<!-- Update the JavaScript with mobile-friendly implementation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let sidebarState = {
        isOpen: false,
        isAnimating: false,
        init: function() {
            this.sidebar = document.getElementById('sidenav-main');
            this.toggle = document.getElementById('sidebarToggle');
            this.backdrop = document.getElementById('sidebarBackdrop');
            
            if (!this.sidebar || !this.toggle) {
                console.error('Sidebar elements not found');
                return;
            }
            
            this.bindEvents();
            this.setInitialState();
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
            // Handle click events
            this.toggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSidebar();
            });
            
            // Handle backdrop click
            this.backdrop.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeSidebar();
            });
            
            // Handle touch events for mobile
            this.toggle.addEventListener('touchstart', (e) => {
                e.preventDefault();
                e.stopPropagation();
            });
            
            this.toggle.addEventListener('touchend', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSidebar();
            });
            
            // Handle escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.closeSidebar();
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    this.openSidebar();
                } else {
                    this.closeSidebar();
                }
            });
            
            // Handle navigation clicks to close sidebar on mobile
            document.addEventListener('click', (e) => {
                if (this.isOpen && window.innerWidth < 1024) {
                    const navLink = e.target.closest('.nav-link');
                    if (navLink) {
                        setTimeout(() => this.closeSidebar(), 100);
                    }
                }
            });
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
            
            // Add a small delay to prevent rapid toggling
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
            
            // Add a small delay to prevent rapid toggling
            setTimeout(() => {
                this.isAnimating = false;
            }, 300);
        }
    };

    sidebarState.init();
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

