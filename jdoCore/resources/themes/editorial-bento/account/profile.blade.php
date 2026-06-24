@extends('theme::account.layout')
@section('title', 'Akun Saya')

@section('account_content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <span class="eb-kicker">Profil</span>
            <h3 class="fw-bold mt-2 mb-0" style="font-family: var(--heading); letter-spacing:-.03em">Halo, {{ auth()->user()->name }}</h3>
            <div class="text-muted small mt-1">Selamat datang kembali. Berikut ringkasan akun Anda.</div>
        </div>
    </div>

    <form action="{{ route('account.profile.update') }}" method="POST">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Nama Lengkap</label>
                <input type="text" name="name" class="form-control rounded-3 py-3" value="{{ old('name', auth()->user()->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Email</label>
                <input type="email" name="email" class="form-control rounded-3 py-3" value="{{ old('email', auth()->user()->email) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Nomor HP</label>
                <input type="tel" name="phone" class="form-control rounded-3 py-3" value="{{ old('phone', auth()->user()->phone) }}" inputmode="numeric" maxlength="15">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Password Baru <span class="text-muted">(opsional)</span></label>
                <input type="password" name="password" class="form-control rounded-3 py-3" placeholder="Min. 6 karakter">
            </div>
        </div>
        @if($errors->any())<div class="alert alert-danger small rounded-4 mt-3">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <div class="mt-4">
            <button class="btn btn-dark rounded-pill px-4"><i class="bi bi-check-lg me-1"></i> Simpan Perubahan</button>
        </div>
    </form>
@endsection
