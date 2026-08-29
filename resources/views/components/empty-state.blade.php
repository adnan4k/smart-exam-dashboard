@props([
    'icon' => 'fas fa-inbox',
    'title',
    'message' => null,
])

<div {{ $attributes->merge(['class' => 'text-center py-5']) }}>
    <div class="icon icon-shape border-radius-md d-flex align-items-center justify-content-center mx-auto mb-3"
         style="background-color: #f8f9fa;">
        <i class="{{ $icon }} text-secondary" style="top: 0;" aria-hidden="true"></i>
    </div>
    <p class="text-sm font-weight-bold mb-1">{{ $title }}</p>
    @if ($message)
        <p class="text-xs text-secondary mb-0">{{ $message }}</p>
    @endif
</div>
