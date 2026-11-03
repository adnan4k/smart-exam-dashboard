@if (session('success'))
    <div class="pz-flash is-good" role="status">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="pz-flash is-bad" role="alert">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="pz-flash is-bad" role="alert">
        <ul>
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
