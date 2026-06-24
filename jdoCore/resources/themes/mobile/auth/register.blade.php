<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 @php $settings = app(\App\Services\SettingService::class); @endphp
 <title>Daftar Akun Baru — {{ $settings->storeName() }}</title>
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
 rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
 <style>
 :root {
 --primary:
 {{ $settings->primaryColor() }}
 ;
 --secondary:
 {{ $settings->secondaryColor() }}
 ;
 --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
 }

 body {
 font-family: 'Plus Jakarta Sans', sans-serif;
 min-height: 100vh;
 background-color: #f8fafc;
 background-image:
 radial-gradient(at 100% 0%, rgba(var(--secondary-rgb, 236, 72, 153), 0.1) 0px, transparent 50%),
 radial-gradient(at 0% 100%, rgba(var(--primary-rgb, 79, 70, 229), 0.1) 0px, transparent 50%);
 display: flex;
 align-items: center;
 justify-content: center;
 padding: 40px 20px;
 }

 .auth-wrapper {
 position: relative;
 width: 100%;
 max-width: 500px;
 animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
 padding-top: 15px;
 }

 .auth-card {
 background: rgba(255, 255, 255, 0.85);
 backdrop-filter: blur(20px);
 -webkit-backdrop-filter: blur(20px);
 border: 1px solid rgba(255, 255, 255, 0.5);
 border-radius: 24px;
 padding: 48px 40px;
 box-shadow: 0 20px 40px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
 position: relative;
 overflow: hidden;
 }

 .auth-card::before {
 content: '';
 position: absolute;
 top: 0;
 left: 0;
 right: 0;
 height: 4px;
 background: var(--gradient);
 }

 .brand-logo-icon {
 background: var(--gradient);
 color: white;
 width: 48px;
 height: 48px;
 border-radius: 14px;
 display: flex;
 align-items: center;
 justify-content: center;
 font-size: 1.5rem;
 box-shadow: 0 8px 16px rgba(var(--primary-rgb, 79, 70, 229), 0.2);
 margin: 0 auto 16px;
 }

 .form-control {
 border-radius: 14px;
 padding: 14px 18px;
 border: 1.5px solid #e2e8f0;
 background-color: #f8fafc;
 font-size: 0.95rem;
 transition: all 0.3s ease;
 }

 .form-control:focus {
 border-color: var(--primary);
 background-color: #fff;
 box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
 }

 .form-label {
 font-weight: 600;
 font-size: 0.85rem;
 color: #475569;
 margin-bottom: 8px;
 }

 .btn-auth {
 background: var(--gradient);
 border: none;
 border-radius: 14px;
 padding: 14px;
 font-weight: 700;
 color: #fff;
 letter-spacing: 0.5px;
 transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
 margin-top: 16px;
 }

 .btn-auth:hover {
 transform: translateY(-2px);
 box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
 color: #fff;
 }

 .input-icon-wrapper {
 position: relative;
 }

 .input-icon-wrapper i {
 position: absolute;
 left: 18px;
 top: 50%;
 transform: translateY(-50%);
 color: #94a3b8;
 }

 .input-icon-wrapper .form-control {
 padding-left: 48px;
 }

 .back-link {
 position: absolute;
 top: -50px;
 left: 0;
 color: #64748b;
 text-decoration: none;
 font-size: 0.85rem;
 font-weight: 600;
 display: flex;
 align-items: center;
 gap: 6px;
 transition: all 0.3s ease;
 padding: 8px 16px;
 border-radius: 12px;
 background: #fff;
 box-shadow: 0 4px 12px rgba(0,0,0,0.03);
 z-index: 100;
 }

 .back-link:hover {
 color: var(--primary);
 transform: translateX(-3px);
 background: #f8fafc;
 box-shadow: 0 4px 15px rgba(0,0,0,0.06);
 }

 @keyframes slideUp {
 from {
 opacity: 0;
 transform: translateY(20px);
 }

 to {
 opacity: 1;
 transform: translateY(0);
 }
 }
 </style>
</head>

<body>

 <div class="auth-wrapper">
 <div class="auth-card">
 <a href="{{ route('home') }}" class="back-link d-none "><i class="bi bi-arrow-left"></i> Batal</a>

 <div class="text-center mb-5">
 @if($settings->get('store_logo'))
 <img src="{{ asset($settings->get('store_logo')) }}" alt="{{ $settings->storeName() }}" class="mb-3"
 style="max-height: 48px; border-radius: 8px;">
 @else
 <div class="brand-logo-icon"><i class="bi bi-person-plus-fill"></i></div>
 @endif
 <h3 class="fw-bold text-dark mb-1">Buat Akun Baru</h3>
 <p class="text-muted small mb-0">Lengkapi data di bawah untuk mulai berbelanja di
 {{ $settings->storeName() }}.
 </p>
 </div>

 @if($errors->any())
 <div class="alert alert-danger py-2 px-3 border-0 d-flex align-items-center gap-2 mb-4"
 style="font-size:0.85rem; border-radius:12px; background-color: #fef2f2; color: #991b1b;">
 <i class="bi bi-exclamation-octagon-fill fs-5"></i>
 <div>
 @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
 </div>
 </div>
 @endif

 <form method="POST" action="{{ route('register') }}">
 @csrf

 <div class="mb-3">
 <label class="form-label">Nama Lengkap</label>
 <div class="input-icon-wrapper">
 <i class="bi bi-person"></i>
 <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus
 placeholder="John Doe">
 </div>
 </div>

 <div class="mb-3">
 <label class="form-label">Email Address</label>
 <div class="input-icon-wrapper">
 <i class="bi bi-envelope"></i>
 <input type="email" name="email" class="form-control" value="{{ old('email') }}" required
 placeholder="nama@email.com">
 </div>
 </div>

 <div class="mb-3">
 <label class="form-label">Nomor HP/WhatsApp</label>
 <div class="input-icon-wrapper">
 <i class="bi bi-telephone"></i>
 <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" required
 inputmode="tel" autocomplete="off"
 placeholder="081234567890">
 </div>
 <small class="text-muted" style="font-size:0.75rem">Hanya angka, contoh <code>081234567890</code> atau <code>6281234567890</code>.</small>
 </div>

 <div class="row g-3">
 <div class=" mb-3">
 <label class="form-label">Password</label>
 <div class="input-icon-wrapper">
 <i class="bi bi-lock"></i>
 <input type="password" name="password" class="form-control" required
 placeholder="Min. 8 Karakter">
 </div>
 </div>
 <div class=" mb-3">
 <label class="form-label">Ulangi Password</label>
 <div class="input-icon-wrapper">
 <i class="bi bi-shield-lock"></i>
 <input type="password" name="password_confirmation" class="form-control" required
 placeholder="Min. 8 Karakter">
 </div>
 </div>
 </div>

 <div class="form-check mt-3 mb-4">
 <input type="checkbox" name="terms" class="form-check-input" id="terms" required
 style="cursor:pointer">
 <label class="form-check-label text-muted" for="terms" style="font-size: 0.8rem; cursor:pointer">
 Saya menyetujui <a href="{{ route('page.show', 'syarat-ketentuan') }}"
 class="text-decoration-none fw-semibold" style="color: var(--primary);">Syarat &
 Ketentuan</a> serta <a href="{{ route('page.show', 'kebijakan-privasi') }}"
 class="text-decoration-none fw-semibold" style="color: var(--primary);">Kebijakan
 Privasi</a> yang berlaku.
 </label>
 </div>

 <button type="submit" class="btn btn-auth w-100">
 Daftar Sekarang <i class="bi bi-arrow-right ms-2"></i>
 </button>
 </form>

 <div class="text-center mt-5">
 <p class="text-muted small mb-0">Sudah punya akun?
 <a href="{{ route('login') }}" class="fw-bold text-decoration-none ms-1"
 style="color: var(--primary);">Masuk di sini</a>
 </p>
 </div>
 </div>
 </div>
</body>

</html>