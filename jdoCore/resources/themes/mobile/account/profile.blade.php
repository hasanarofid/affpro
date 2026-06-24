@extends('theme::account.layout')
@section('title', 'Profil Saya')

@section('account_content')
 <div class="card-body p-4 ">
 <div class="mb-5">
 <h5 class="fw-bold mb-1 text-dark">Profil Saya</h5>
 <p class="text-muted small">Kelola informasi profil Anda untuk mengontrol, mengamankan, dan melindungi akun.</p>
 </div>
 @if($demoMode)
            <div class="alert alert-warning d-flex align-items-center mb-4" style="border-radius:12px; background: rgba(255,193,7,0.08); border: 1px solid rgba(255,193,7,0.2);">
                <i class="bi bi-shield-exclamation fs-4 me-3 text-warning"></i>
                <div class="small">
                    <strong>Mode Demo:</strong> Perubahan profil dan kata sandi tidak diperbolehkan.
                </div>
            </div>
 @endif

 <form action="{{ route('account.profile.update') }}" method="POST">
 @csrf @method('PUT')

 <div class="row g-4">
 <div>
 <label class="form-label small fw-bold text-uppercase text-muted"
 style="letter-spacing: 0.5px; font-size: 0.7rem;">Nama Lengkap</label>
 <input type="text" name="name" class="form-control form-control-lg border-0 bg-light px-4"
 value="{{ auth()->user()->name }}" required style="border-radius:12px; font-size: 0.95rem;" {{ $demoMode ? 'disabled' : '' }}>
 </div>

 <div>
 <label class="form-label small fw-bold text-uppercase text-muted"
 style="letter-spacing: 0.5px; font-size: 0.7rem;">Nomor Telepon</label>
 <input type="text" name="phone" class="form-control form-control-lg border-0 bg-light px-4"
 value="{{ auth()->user()->phone }}" style="border-radius:12px; font-size: 0.95rem;" {{ $demoMode ? 'disabled' : '' }}>
 </div>

 <div class="col-12">
 <label class="form-label small fw-bold text-uppercase text-muted"
 style="letter-spacing: 0.5px; font-size: 0.7rem;">Alamat Email</label>
 <input type="email" class="form-control form-control-lg border-0 bg-light px-4 opacity-75"
 value="{{ auth()->user()->email }}" disabled style="border-radius:12px; font-size: 0.95rem;">
 <div class="form-text small mt-2">Email tidak dapat diubah untuk alasan keamanan.</div>
 </div>

 <div class="col-12 mt-5">
 <div class="d-flex align-items-center mb-4">
 <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex justify-content-center align-items-center me-3"
 style="width:32px; height:32px;">
 <i class="bi bi-shield-lock-fill"></i>
 </div>
 <h6 class="fw-bold mb-0 text-dark">Ubah Kata Sandi</h6>
 </div>
 <div class="row g-4 pt-2">
 <div>
 <label class="form-label small fw-bold text-uppercase text-muted"
 style="letter-spacing: 0.5px; font-size: 0.7rem;">Kata Sandi Baru</label>
 <input type="password" name="password"
 class="form-control form-control-lg border-0 bg-light px-4" placeholder="••••••••"
 style="border-radius:12px; font-size: 0.95rem;" {{ $demoMode ? 'disabled' : '' }}>
 </div>
 <div>
 <label class="form-label small fw-bold text-uppercase text-muted"
 style="letter-spacing: 0.5px; font-size: 0.7rem;">Konfirmasi Kata Sandi</label>
 <input type="password" name="password_confirmation"
 class="form-control form-control-lg border-0 bg-light px-4" placeholder="••••••••"
 style="border-radius:12px; font-size: 0.95rem;" {{ $demoMode ? 'disabled' : '' }}>
 </div>
 </div>
 </div>

 <div class="col-12 mt-4 pt-3 border-top">
 <button class="btn btn-primary px-5 py-2 fw-bold" style="border-radius:12px" {{ $demoMode ? 'disabled' : '' }}>Simpan Perubahan</button>
 </div>
 </div>
 </form>
 </div>
@endsection