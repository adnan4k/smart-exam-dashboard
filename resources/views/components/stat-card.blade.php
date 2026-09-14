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
        'brand' => ['bg' => 'bg-[#58706D]', 'color' => '#58706D', 'glow' => 'rgba(88, 112, 109, 0.25)'],
        'sage'  => ['bg' => 'bg-[#7C8A6E]', 'color' => '#7C8A6E', 'glow' => 'rgba(124, 138, 110, 0.25)'],
        'ink'   => ['bg' => 'bg-[#4B5757]', 'color' => '#4B5757', 'glow' => 'rgba(75, 87, 87, 0.25)'],
        'khaki' => ['bg' => 'bg-[#96835B]', 'color' => '#96835B', 'glow' => 'rgba(150, 131, 91, 0.25)'],
        'info'  => ['bg' => 'bg-sky-600',   'color' => '#0284c7', 'glow' => 'rgba(2, 132, 199, 0.25)'],
    ];

    $chosenTone = $tones[$tone] ?? $tones['brand'];
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate class="block h-full text-decoration-none group">
@endif

<div {{ $attributes->merge(['class' => 'card h-full border border-slate-200/70 rounded-2xl bg-white p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200']) }}>
    <div class="flex items-center justify-between gap-3">
        <div class="flex flex-col">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                {{ $label }}
            </span>
            <h3 class="text-2xl font-black text-slate-800 tracking-tight mb-0">
                {{ $value }}
            </h3>

            @if ($note || $href)
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    @if ($note)
                        <span class="text-slate-400 font-medium">{{ $note }}</span>
                    @elseif ($href)
                        <span class="text-[#58706D] font-semibold flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                            View details <i class="fas fa-arrow-right text-[10px]"></i>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="w-12 h-12 rounded-xl {{ $chosenTone['bg'] }} text-white flex items-center justify-center shadow-md flex-shrink-0"
             style="box-shadow: 0 4px 12px {{ $chosenTone['glow'] }};">
            <i class="{{ $icon }} text-lg text-white" aria-hidden="true"></i>
        </div>
    </div>
</div>

@if ($href)
    </a>
@endif
