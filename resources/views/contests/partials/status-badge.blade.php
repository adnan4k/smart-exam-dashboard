@php
    $state = $contest->status === 'finalized' ? 'finalized'
        : (! $contest->hasStarted() ? ($contest->status === 'draft' ? 'draft' : 'upcoming')
        : ($contest->hasEnded() ? 'awaiting results' : 'live'));

    $classes = [
        'draft'            => 'bg-gradient-secondary',
        'upcoming'         => 'bg-gradient-info',
        'live'             => 'bg-gradient-success',
        'awaiting results' => 'bg-gradient-warning',
        'finalized'        => 'bg-gradient-dark',
    ];
@endphp
<span class="badge badge-sm {{ $classes[$state] ?? 'bg-gradient-secondary' }}">{{ ucfirst($state) }}</span>
