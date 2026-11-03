{{-- Contact details for a student. Expects $user (User model). Styled by contests.partials.pz-styles. --}}
@php
    $phones = collect([$user->phone, $user->phone_number])->filter()->unique()->values();
@endphp
<dl class="pz-contact">
    <div>
        <dt>Phone</dt>
        <dd>
            @forelse ($phones as $phone)
                <a href="tel:{{ $phone }}" @click.stop>{{ $phone }}</a>@if (! $loop->last), @endif
            @empty
                <span class="pz-faint" style="font-weight: 500;">Not provided</span>
            @endforelse
        </dd>
    </div>
    <div>
        <dt>Email</dt>
        <dd><a href="mailto:{{ $user->email }}" @click.stop>{{ $user->email }}</a></dd>
    </div>
    <div>
        <dt>Institution</dt>
        <dd>
            {{ $user->institution_name ?: '—' }}
            @if ($user->institution_type)<span class="pz-faint" style="font-weight: 500;">({{ $user->institution_type }})</span>@endif
        </dd>
    </div>
    <div>
        <dt>Exam type</dt>
        <dd>{{ $user->type?->name ?? '—' }}</dd>
    </div>
    <div>
        <dt>Joined</dt>
        <dd>{{ $user->created_at?->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y') ?? '—' }}</dd>
    </div>
    <div>
        <dt>Last login</dt>
        <dd>{{ $user->last_login_at ? \Illuminate\Support\Carbon::parse($user->last_login_at)->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y H:i') . ' EAT' : '—' }}</dd>
    </div>
</dl>
