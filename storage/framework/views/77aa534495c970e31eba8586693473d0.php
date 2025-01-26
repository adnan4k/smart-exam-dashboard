<?php if (isset($component)) { $__componentOriginala7e3e3ab156e6fa1f86927d4765c5327 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala7e3e3ab156e6fa1f86927d4765c5327 = $attributes; } ?>
<?php $component = App\View\Components\Layouts\Base::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.base'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\Layouts\Base::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    
    <?php if(auth()->guard()->check()): ?>
        
        <?php if(in_array(request()->route()->getName(),['static-sign-up', 'sign-up'],)): ?>
            <?php echo e($slot); ?>

            
        <?php elseif(in_array(request()->route()->getName(),['sign-in', 'login'],)): ?>
            <?php echo e($slot); ?>

            <?php echo $__env->make('layouts.footers.guest.description', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php elseif(in_array(request()->route()->getName(),['profile', 'my-profile'],)): ?>
            <?php echo $__env->make('layouts.navbars.auth.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <div class="main-content position-relative bg-gray-100">
                <?php echo $__env->make('layouts.navbars.auth.nav-profile', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div>
                    <?php echo e($slot); ?>

                    <?php echo $__env->make('layouts.footers.auth.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
            <?php echo $__env->make('components.plugins.fixed-plugin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('layouts.navbars.auth.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('layouts.navbars.auth.nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('components.plugins.fixed-plugin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo e($slot); ?>

            <main>
                <div class="container-fluid">
                    <div class="row">
                        <?php echo $__env->make('layouts.footers.auth.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                </div>
            </main>
        <?php endif; ?>
    <?php endif; ?>

    
    <?php if(auth()->guard()->guest()): ?>
        
        <?php if(!auth()->check() && in_array(request()->route()->getName(),['login'],)): ?>
            <?php echo e($slot); ?>

          

            
        <?php elseif(!auth()->check() && in_array(request()->route()->getName(),['sign-up'],)): ?>
        <?php endif; ?>
    <?php endif; ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala7e3e3ab156e6fa1f86927d4765c5327)): ?>
<?php $attributes = $__attributesOriginala7e3e3ab156e6fa1f86927d4765c5327; ?>
<?php unset($__attributesOriginala7e3e3ab156e6fa1f86927d4765c5327); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala7e3e3ab156e6fa1f86927d4765c5327)): ?>
<?php $component = $__componentOriginala7e3e3ab156e6fa1f86927d4765c5327; ?>
<?php unset($__componentOriginala7e3e3ab156e6fa1f86927d4765c5327); ?>
<?php endif; ?>
<?php /**PATH /home/faysal/Desktop/apps/kasma/tour-travel-dashbaord/resources/views/layouts/app.blade.php ENDPATH**/ ?>