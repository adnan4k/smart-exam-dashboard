@props([
    'label',
    'value',
    'icon' => 'fas fa-chart-simple',
    'tone' => 'brand',
    'delta' => null,
    'note' => null,
    'href' => null,
])

@php
    $tones = [
        'brand' => '#56C596',
        'info' => '#17c1e8',
        'warning' => '#fbcf33',
        'danger' => '#ea0606',
    ];

    $directions = [
        'up' => ['icon' => 'fa-arrow-up', 'class' => 'text-success'],
        'down' => ['icon' => 'fa-arrow-down', 'class' => 'text-danger'],
        'flat' => ['icon' => 'fa-minus', 'class' => 'text-secondary'],
    ];

    $background = $tones[$tone] ?? $tones['brand'];
    $trend = $delta ? ($directions[$delta['direction']] ?? $directions['flat']) : null;
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate class="d-block h-100 text-decoration-none">
@endif

<div {{ $attributes->merge(['class' => 'card h-100']) }}>
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-8">
                <p class="text-sm mb-1 text-capitalize font-weight-bold text-secondary">{{ $label }}</p>
                <h5 class="font-weight-bolder mb-1">{{ $value }}</h5>

                @if ($trend || $note)
                    <p class="mb-0 text-sm">
                        @if ($trend)
                            <span class="{{ $trend['class'] }} font-weight-bolder">
                                <i class="fas {{ $trend['icon'] }} text-xs" aria-hidden="true"></i>
                                {{ $delta['percent'] === null ? 'New' : $delta['percent'] . '%' }}
                            </span>
                        @endif

                        @if ($note)
                            <span class="text-secondary">{{ $note }}</span>
                        @endif
                    </p>
                @endif
            </div>

            <div class="col-4 text-end">
                <div class="icon icon-shape shadow border-radius-md d-flex align-items-center justify-content-center ms-auto"
                     style="background-color: {{ $background }};">
                    {{-- .icon-shape offsets its icon by 11px for the theme's non-flex markup. --}}
                    <i class="{{ $icon }} text-white" style="top: 0;" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($href)
    </a>
@endif
