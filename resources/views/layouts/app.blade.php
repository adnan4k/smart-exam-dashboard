<x-layouts.base>
    {{-- If the user is authenticated --}}
    @auth()
        @if (in_array(request()->route()->getName(), ['static-sign-up', 'sign-up']))
            {{ $slot }}
        @elseif (in_array(request()->route()->getName(), ['sign-in', 'login']))
            {{ $slot }}
        @else
            @include('layouts.navbars.auth.sidebar')
            <div class="main-content position-relative bg-[#f8fafc] min-h-screen pb-6">
                @include('layouts.navbars.auth.nav')
                <div class="content-wrapper px-3 pt-2">
                    {{ $slot }}
                </div>
                @include('layouts.footers.auth.footer')
            </div>
        @endif
    @endauth

    {{-- If the user is not authenticated (guest) --}}
    @guest
        {{ $slot }}
    @endguest

</x-layouts.base>
