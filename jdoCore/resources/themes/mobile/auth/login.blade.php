<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 @php $settings = app(\App\Services\SettingService::class); @endphp
 <title>Masuk — {{ $settings->storeName() }}</title>
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
 radial-gradient(at 0% 0%, rgba(var(--primary-rgb, 79, 70, 229), 0.1) 0px, transparent 50%),
 radial-gradient(at 100% 100%, rgba(var(--secondary-rgb, 236, 72, 153), 0.1) 0px, transparent 50%);
 display: flex;
 align-items: center;
 justify-content: center;
 padding: 20px;
 }

 .auth-wrapper {
 position: relative;
 width: 100%;
 max-width: 440px;
 animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
 padding-top: 10px;
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

 .brand-logo {
 font-size: 1.75rem;
 font-weight: 800;
 background: var(--gradient);
 -webkit-background-clip: text;
 -webkit-text-fill-color: transparent;
 letter-spacing: -0.5px;
 display: inline-flex;
 align-items: center;
 gap: 10px;
 }

 .brand-logo-icon {
 background: var(--gradient);
 color: white;
 width: 42px;
 height: 42px;
 border-radius: 12px;
 display: flex;
 align-items: center;
 justify-content: center;
 font-size: 1.25rem;
 box-shadow: 0 8px 16px rgba(var(--primary-rgb, 79, 70, 229), 0.2);
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
 margin-top: 10px;
 position: relative;
 overflow: hidden;
 }

 .btn-auth:hover {
 transform: translateY(-2px);
 box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
 color: #fff;
 }

 .auth-divider {
 display: flex;
 align-items: center;
 text-align: center;
 margin: 24px 0;
 color: #94a3b8;
 font-size: 0.85rem;
 font-weight: 500;
 }

 .auth-divider::before,
 .auth-divider::after {
 content: '';
 flex: 1;
 border-bottom: 1px dashed #cbd5e1;
 }

 .auth-divider::before {
 margin-right: .5em;
 }

 .auth-divider::after {
 margin-left: .5em;
 }

 .btn-outline-social {
 background: #fff;
 border: 1.5px solid #e2e8f0;
 border-radius: 14px;
 padding: 12px;
 font-weight: 600;
 color: #475569;
 transition: all 0.3s ease;
 display: flex;
 align-items: center;
 justify-content: center;
 gap: 10px;
 text-decoration: none;
 }

 .btn-outline-social:hover {
 background: #f8fafc;
 border-color: #cbd5e1;
 transform: translateY(-2px);
 box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
 color: #0f172a;
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
 <a href="{{ route('home') }}" class="back-link d-none "><i class="bi bi-arrow-left"></i> Kembali ke
 Toko</a>

 <div class="auth-card">
 <div class="text-center mb-5">
 <div class="d-flex justify-content-center mb-3">
 @if($settings->get('store_logo'))
 <img src="{{ asset($settings->get('store_logo')) }}" alt="{{ $settings->storeName() }}"
 style="max-height: 48px; border-radius: 8px;">
 @else
 <div class="brand-logo-icon"><i class="bi bi-box-seam"></i></div>
 @endif
 </div>
 @if(!$settings->get('store_logo'))
 <div class="brand-logo mb-1">{{ $settings->storeName() }}</div>
 @endif
 <p class="text-muted small mb-0">Selamat datang kembali! Silakan masuk ke akun Anda.</p>
 </div>

 @if($errors->any())
 <div class="alert alert-danger py-2 px-3 border-0 d-flex align-items-center gap-2"
 style="font-size:0.85rem; border-radius:12px; background-color: #fef2f2; color: #991b1b;">
 <i class="bi bi-exclamation-circle-fill"></i>
 <div>
 @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
 </div>
 </div>
 @endif

 @if(in_array($settings->get('login_method', 'both'), ['both', 'password']))
 <form method="POST" action="{{ route('login') }}">
 @csrf
 <div class="mb-4">
 <label class="form-label">Email Address</label>
 <div class="position-relative">
 <input type="email" name="email" class="form-control ps-5" value="{{ old('email') }}" required
 autofocus placeholder="nama@email.com">
 <i class="bi bi-envelope text-muted position-absolute"
 style="left: 18px; top: 50%; transform: translateY(-50%);"></i>
 </div>
 </div>

 <div class="mb-4">
 <div class="d-flex justify-content-between align-items-center mb-1">
 <label class="form-label mb-0">Password</label>
 <a href="#" class="small text-decoration-none fw-semibold" style="color: var(--primary);">Lupa
 Password?</a>
 </div>
 <div class="position-relative">
 <input type="password" name="password" class="form-control ps-5" required
 placeholder="••••••••">
 <i class="bi bi-lock text-muted position-absolute"
 style="left: 18px; top: 50%; transform: translateY(-50%);"></i>
 </div>
 </div>

 <div class="d-flex align-items-center mb-4">
 <div class="form-check">
 <input type="checkbox" name="remember" class="form-check-input" id="remember"
 style="cursor:pointer">
 <label class="form-check-label small user-select-none" for="remember"
 style="cursor:pointer; color:#475569">Ingat Saya</label>
 </div>
 </div>

 <button type="submit" class="btn btn-auth w-100">
 Masuk Sekarang <i class="bi bi-arrow-right ms-2"></i>
 </button>
 </form>
 @endif

 @if(in_array($settings->get('login_method', 'both'), ['both', 'otp']))
  @if(in_array($settings->get('login_method', 'both'), ['both']))
  <div class="auth-divider">atau masuk dengan</div>
  @endif

  <a href="{{ route('otp.login') }}" class="btn-outline-social w-100 mt-2">
  <i class="bi bi-whatsapp" style="color: #25D366; font-size: 1.2rem;"></i> WhatsApp OTP
  </a>
 @endif

 <div class="text-center mt-4 pt-2">
 <p class="text-muted small mb-0">Belum punya akun?
 <a href="{{ route('register') }}" class="fw-bold text-decoration-none ms-1"
 style="color: var(--primary);">Daftar di sini</a>
 </p>
 </div>
 </div>
 </div>
</body>

</html>