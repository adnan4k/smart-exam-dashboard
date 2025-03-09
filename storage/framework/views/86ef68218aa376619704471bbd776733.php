<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Metas -->
    <?php if(env('IS_DEMO')): ?>
    <?php if (isset($component)) { $__componentOriginalb028e3c120f9eb4dcf5f37307a919070 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb028e3c120f9eb4dcf5f37307a919070 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.demo-metas','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('demo-metas'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb028e3c120f9eb4dcf5f37307a919070)): ?>
<?php $attributes = $__attributesOriginalb028e3c120f9eb4dcf5f37307a919070; ?>
<?php unset($__attributesOriginalb028e3c120f9eb4dcf5f37307a919070); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb028e3c120f9eb4dcf5f37307a919070)): ?>
<?php $component = $__componentOriginalb028e3c120f9eb4dcf5f37307a919070; ?>
<?php unset($__componentOriginalb028e3c120f9eb4dcf5f37307a919070); ?>
<?php endif; ?>
    <?php endif; ?>
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/logo.png">
    <link rel="icon" type="image/png" href="../assets/img/logo.png">
    <title>
        Quiz Dashboard
    </title>
    <!-- Fonts and icons     -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- CSS Files -->
    <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css?v=1" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    <?php echo BladeUIKit\BladeUIKit::outputStyles(); ?>
    <link href="/css/app.css" rel="stylesheet">

    <!-- <script src="https://cdn.tailwindcss.com"></script> -->


</head>

<body class="g-sidenav-show z-0 bg-gray-100">

    <?php echo e($slot); ?>


    <!--   Core JS Files   -->
    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggleButton = document.getElementById("iconNavbarSidenav");
            const sidenav = document.getElementById("sidebar-container");
            console.log('here is me faya', sidenav)
            toggleButton.addEventListener("click", () => {
                console.log('clicked here')
                // Toggle a class to show/hide the sidebar
                console.log(sidenav.classList.toggle("hidden"));
            });
        });
    </script>

    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="resources/js/custom.js" defer></script>


    
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="assets/js/soft-ui-dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>

    
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>

    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <?php if (isset($component)) { $__componentOriginalafb36adf865af54d1e1d61c1adc535d1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalafb36adf865af54d1e1d61c1adc535d1 = $attributes; } ?>
<?php $component = Masmerise\Toaster\ToasterHub::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('toaster-hub'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Masmerise\Toaster\ToasterHub::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalafb36adf865af54d1e1d61c1adc535d1)): ?>
<?php $attributes = $__attributesOriginalafb36adf865af54d1e1d61c1adc535d1; ?>
<?php unset($__attributesOriginalafb36adf865af54d1e1d61c1adc535d1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalafb36adf865af54d1e1d61c1adc535d1)): ?>
<?php $component = $__componentOriginalafb36adf865af54d1e1d61c1adc535d1; ?>
<?php unset($__componentOriginalafb36adf865af54d1e1d61c1adc535d1); ?>
<?php endif; ?>
    

</body>

</html><?php /**PATH C:\Users\Administrator\Desktop\apps\quiz\resources\views/layouts/base.blade.php ENDPATH**/ ?>