{{-- Contact card for a student. Expects $user (User model). --}}
@php
    $phones = collect([$user->phone, $user->phone_number])->filter()->unique()->values();
@endphp
<dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-xs mb-0">
    <div>
        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Phone</dt>
        <dd class="mb-0 font-semibold text-slate-800">
            @forelse ($phones as $phone)
                <a href="tel:{{ $phone }}" class="text-[#58706D] hover:underline" @click.stop>{{ $phone }}</a>@if (! $loop->last), @endif
            @empty
                <span class="text-slate-400 font-normal">Not provided</span>
            @endforelse
        </dd>
    </div>
    <div>
        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Email</dt>
        <dd class="mb-0 font-semibold text-slate-800 break-all">
            <a href="mailto:{{ $user->email }}" class="text-[#58706D] hover:underline" @click.stop>{{ $user->email }}</a>
        </dd>
    </div>
    <div>
        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Institution</dt>
        <dd class="mb-0 text-slate-700">
            {{ $user->institution_name ?: '—' }}@if ($user->institution_type) <span class="text-slate-400">({{ $user->institution_type }})</span>@endif
        </dd>
    </div>
    <div>
        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Exam type</dt>
        <dd class="mb-0 text-slate-700">{{ $user->type?->name ?? '—' }}</dd>
    </div>
    <div>
        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Joined</dt>
        <dd class="mb-0 text-slate-700">{{ $user->created_at?->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y') ?? '—' }}</dd>
    </div>
    <div>
        <dt class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Last login</dt>
        <dd class="mb-0 text-slate-700">
            {{ $user->last_login_at ? \Illuminate\Support\Carbon::parse($user->last_login_at)->tz(\App\Models\Contest::TIMEZONE)->format('M d, Y H:i') . ' EAT' : '—' }}
        </dd>
    </div>
</dl>
