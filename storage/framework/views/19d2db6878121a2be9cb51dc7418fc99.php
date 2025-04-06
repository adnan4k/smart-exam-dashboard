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
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'dashboard' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('dashboard')); ?>">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-tachometer-alt text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>

            <!-- Questions -->
            <li class="nav-item pb-2">
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'questions' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('questions')); ?>">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-question-circle text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Questions</span>
                </a>
            </li>

            <!-- Year Group -->
            <li class="nav-item pb-2">
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'year-group' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('year-group')); ?>">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-calendar-alt text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Year Groups</span>
                </a>
            </li>

            <!-- Subject -->
            <li class="nav-item pb-2">
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'subject' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('subject')); ?>">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-book text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Subject</span>
                </a>
            </li>
            <li class="nav-item pb-2">
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'chapter' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('chapter')); ?>">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-book text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Chapter</span>
                </a>
            </li>
                  <!-- Subject -->
                  <li class="nav-item pb-2">
                    <a class="nav-link <?php echo e(Route::currentRouteName() == 'type' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                        wire:navigate href="<?php echo e(route('type')); ?>">
                        <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-book text-dark"></i>
                        </div> 
                        <span class="nav-link-text ms-1">Type</span>
                    </a>
                </li>

            <!-- Subscription -->
            <li class="nav-item pb-2">
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'subscription' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('subscription')); ?>">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-credit-card text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Subscription</span>
                </a>
            </li>

            <li class="nav-item pb-2">
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'referral' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('referral')); ?>">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-credit-card text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Referrals</span>
                </a>
            </li>
            <li class="nav-item pb-2">
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'referral-setting' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('referral-setting')); ?>">
                    <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="fas fa-credit-card text-dark"></i>
                    </div>
                    <span class="nav-link-text ms-1">Referral Setting</span>
                </a>
            </li>

            <!-- Users -->
            <li class="nav-item pb-2">
                <a class="nav-link <?php echo e(Route::currentRouteName() == 'users' ? 'bg-[#56C596] text-white font-bold rounded-2xl' : ''); ?>"
                    wire:navigate href="<?php echo e(route('users')); ?>">
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

<!-- Update the JavaScript with a more robust implementation -->
<script>
window.addEventListener('load', function() {
    let sidebarState = {
        isOpen: false,
        init: function() {
            this.sidebar = document.getElementById('sidenav-main');
            this.toggle = document.getElementById('sidebarToggle');
            this.backdrop = document.getElementById('sidebarBackdrop');
            this.bindEvents();
        },
        bindEvents: function() {
            if (this.toggle) {
                this.toggle.addEventListener('click', () => this.toggleSidebar());
            }
            if (this.backdrop) {
                this.backdrop.addEventListener('click', () => this.toggleSidebar());
            }
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.toggleSidebar();
                }
            });
        },
        toggleSidebar: function() {
            this.isOpen = !this.isOpen;
            this.sidebar.classList.toggle('-translate-x-full');
            this.backdrop.classList.toggle('hidden');
            document.body.style.overflow = this.isOpen ? 'hidden' : '';
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
        }

        #sidenav-main:not(.-translate-x-full) {
            transform: translateX(0);
        }

        #sidebar-container {
            height: 100% !important;
            overflow-y: auto !important;
            padding: 1rem;
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

<?php /**PATH C:\Users\Administrator\Desktop\apps\quiz\resources\views/layouts/navbars/auth/sidebar.blade.php ENDPATH**/ ?>