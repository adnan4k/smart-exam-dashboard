@if (session('success'))
    <div class="alert alert-success text-white mx-4 text-sm" role="alert">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger text-white mx-4 text-sm" role="alert">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger text-white mx-4 text-sm" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif
