<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Metas -->
    @if (env('IS_DEMO'))
    <x-demo-metas></x-demo-metas>
    @endif
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/log.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/log.png') }}">
    <title>Smart Exam &mdash; Admin Dashboard</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <!-- Icons -->
    <link href="{{ asset('assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" referrerpolicy="no-referrer" />

    <!-- Dashboard & Plugin Styles -->
    <link id="pagestyle" href="{{ asset('assets/css/soft-ui-dashboard.css?v=1') }}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.5/dist/quill.snow.css" rel="stylesheet" />

    @livewireStyles
    @bukStyles
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

<body class="g-sidenav-show z-0 bg-[#f8fafc] text-slate-700 font-sans antialiased">

    {{ $slot }}

    <!--   Core JS Files   -->
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
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


    
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{ asset('assets/js/soft-ui-dashboard.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script>

    {{-- Quill --}}
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.0-rc.5/dist/quill.js"></script>

    @livewireScripts
    
    {{-- <x-toaster-hub /> --}}
    {{-- <script src="https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js"></script> --}}

</body>

</html>