@php
    $state = $contest->status === 'finalized' ? 'finalized'
        : (! $contest->hasStarted() ? ($contest->status === 'draft' ? 'draft' : 'upcoming')
        : ($contest->hasEnded() ? 'awaiting results' : 'live'));

    $classes = [
        'draft'            => 'ct-badge-draft',
        'upcoming'         => 'ct-badge-upcoming',
        'live'             => 'ct-badge-live',
        'awaiting results' => 'ct-badge-awaiting',
        'finalized'        => 'ct-badge-finalized',
    ];
@endphp
<span class="ct-badge {{ $classes[$state] ?? 'ct-badge-quiet' }}">{{ ucfirst($state) }}</span>
