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
                    <span class="nav-link-text ms-1">Referral Settings</span>
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

            <!-- Notes -->
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'notes' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('notes') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-sticky-note text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Notes</span>
                </a>
            </li>

            <!-- Profile -->
            <li class="nav-item pb-2">
                <a class="nav-link {{ Route::currentRouteName() == 'profile' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : '' }}"
                    wire:navigate href="{{ route('profile') }}">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-user-cog text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profile</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

<!-- Update the backdrop to only show when sidebar is open -->
<div id="sidebarBackdrop"
     class="fixed inset-0 bg-black bg-opacity-50 z-[998] hidden lg:hidden"
     onclick="window.sidebarState.closeSidebar()">
</div>

<script>
// Global sidebar state
window.sidebarState = {
    isOpen: false,
    isMobile: false,
    
    init: function() {
        this.sidebar = document.getElementById('sidenav-main');
        this.toggle = document.getElementById('sidebarToggle');
        this.backdrop = document.getElementById('sidebarBackdrop');
        
        if (!this.sidebar || !this.toggle) {
            console.error('Sidebar elements not found');
            return;
        }
        
        // Check if mobile
        this.isMobile = window.innerWidth < 1024;
        
        // Set initial state
        if (this.isMobile) {
            // Ensure sidebar starts closed on mobile
            this.sidebar.classList.add('-translate-x-full');
            this.isOpen = false;
        } else {
            // Desktop: sidebar is always visible
            this.sidebar.classList.remove('-translate-x-full');
            this.isOpen = true;
        }
        
        // Add event listeners
        this.toggle.addEventListener('click', () => {
            this.toggleSidebar();
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (this.isMobile && this.isOpen) {
                if (!this.sidebar.contains(e.target) && !this.toggle.contains(e.target)) {
                    this.closeSidebar();
                }
            }
        });
        
        // Handle window resize
        window.addEventListener('resize', () => {
            const wasMobile = this.isMobile;
            this.isMobile = window.innerWidth < 1024;
            
            if (wasMobile !== this.isMobile) {
                // Screen size changed
                if (this.isMobile) {
                    // Switched to mobile
                    this.closeSidebar();
                } else {
                    // Switched to desktop
                    this.openSidebar();
                }
            }
        });
        
        // Handle escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMobile && this.isOpen) {
                this.closeSidebar();
            }
        });
    },
    
    toggleSidebar: function() {
        if (this.isMobile) {
            if (this.isOpen) {
                this.closeSidebar();
            } else {
                this.openSidebar();
            }
        }
    },
    
    openSidebar: function() {
        if (this.isMobile) {
            this.sidebar.classList.remove('-translate-x-full');
            this.backdrop.classList.remove('hidden');
            this.isOpen = true;
            
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }
    },
    
    closeSidebar: function() {
        if (this.isMobile) {
            this.sidebar.classList.add('-translate-x-full');
            this.backdrop.classList.add('hidden');
            this.isOpen = false;
            
            // Restore body scroll
            document.body.style.overflow = '';
        }
    }
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.sidebarState.init();
    });
} else {
    window.sidebarState.init();
}

// Re-initialize after Livewire updates
document.addEventListener('livewire:navigated', () => {
    window.sidebarState.init();
});

// Re-initialize after page load
window.addEventListener('load', () => {
    window.sidebarState.init();
});

// Re-initialize after any DOM changes
const observer = new MutationObserver(() => {
    window.sidebarState.init();
});
observer.observe(document.body, { childList: true, subtree: true });
</script>

<style>
/* Mobile-first responsive design */
@media (max-width: 1023px) {
    #sidebarToggle {
        display: block;
    }
    
    .sidenav {
        width: 280px;
        z-index: 999;
    }
}

@media (min-width: 1024px) {
    #sidebarToggle {
        display: none;
    }
    
    .sidenav {
        width: 280px;
    }
}

/* Ensure backdrop is below sidebar but above content */
#sidebarBackdrop {
    z-index: 998;
}

/* Smooth transitions */
.sidenav {
    transition: transform 0.3s ease-in-out;
}

/* Ensure proper stacking */
#sidebar-container {
    position: relative;
    z-index: 999;
}

/* Mobile optimizations */
@media (max-width: 1023px) {
    .sidenav {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
}

/* Desktop optimizations */
@media (min-width: 1024px) {
    .sidenav {
        box-shadow: none;
    }
}
</style>

