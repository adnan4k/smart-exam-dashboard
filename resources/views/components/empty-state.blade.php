@props([
    'icon' => 'fas fa-inbox',
    'title' => 'No records found',
    'message' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-10 px-4']) }}>
    <div class="w-14 h-14 rounded-2xl bg-[#58706D]/10 text-[#58706D] flex items-center justify-center mx-auto mb-3.5 shadow-sm">
        <i class="{{ $icon }} text-xl" aria-hidden="true"></i>
    </div>
    <h6 class="text-base font-bold text-slate-800 mb-1 tracking-tight">{{ $title }}</h6>
    @if ($message)
        <p class="text-xs text-slate-400 max-w-md mx-auto mb-4 leading-relaxed">{{ $message }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-2 flex justify-center items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>

