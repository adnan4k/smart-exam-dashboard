<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
    <div id="sidebar-container" class="z-999 md:z-0">
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

        </ul>
    </div>
</aside>
<?php /**PATH C:\Users\Administrator\Desktop\apps\quiz\resources\views/layouts/navbars/auth/sidebar.blade.php ENDPATH**/ ?>