@php
 $settings = app(\App\Services\SettingService::class);
 $primaryColor = $settings->primaryColor();
 $secondaryColor = $settings->secondaryColor();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <meta name="csrf-token" content="{{ csrf_token() }}">
 <title>Login OTP — {{ $settings->storeName() }}</title>
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
 rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
 <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
 <style>
 :root {
 --primary:
 {{ $primaryColor }}
 ;
 --secondary:
 {{ $secondaryColor }}
 ;
 --gradient: linear-gradient(135deg, var(--primary), var(--secondary));
 }

 body {
 font-family: 'Plus Jakarta Sans', sans-serif;
 min-height: 100vh;
 background-color: #f8fafc;
 background-image:
 radial-gradient(at 0% 100%, rgba(var(--primary-rgb, 79, 70, 229), 0.1) 0px, transparent 50%),
 radial-gradient(at 100% 0%, rgba(37, 211, 102, 0.1) 0px, transparent 50%);
 display: flex;
 align-items: center;
 justify-content: center;
 padding: 20px;
 }

 [x-cloak] {
 display: none !important;
 }

 .auth-wrapper {
 position: relative;
 width: 100%;
 max-width: 440px;
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
 background: linear-gradient(135deg, #25D366, #128C7E);
 }

 .brand-logo-icon {
 background: linear-gradient(135deg, #25D366, #128C7E);
 color: white;
 width: 54px;
 height: 54px;
 border-radius: 16px;
 display: flex;
 align-items: center;
 justify-content: center;
 font-size: 1.75rem;
 box-shadow: 0 8px 16px rgba(37, 211, 102, 0.2);
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
 border-color: #25D366;
 background-color: #fff;
 box-shadow: 0 0 0 4px rgba(37, 211, 102, 0.15);
 }

 .otp-input {
 letter-spacing: 12px;
 font-size: 1.5rem;
 text-align: center;
 font-weight: 700;
 padding: 16px;
 text-transform: uppercase;
 }

 .form-label {
 font-weight: 600;
 font-size: 0.85rem;
 color: #475569;
 margin-bottom: 8px;
 }

 .btn-auth {
 background: linear-gradient(135deg, #25D366, #128C7E);
 border: none;
 border-radius: 14px;
 padding: 14px;
 font-weight: 700;
 color: #fff;
 letter-spacing: 0.5px;
 transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
 margin-top: 10px;
 }

 .btn-auth:hover {
 transform: translateY(-2px);
 box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
 color: #fff;
 }

 .btn-auth:disabled {
 background: #cbd5e1;
 transform: none;
 box-shadow: none;
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
 box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
 z-index: 100;
 }

 .back-link:hover {
 color: var(--primary);
 transform: translateX(-3px);
 background: #f8fafc;
 box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
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

 .slide-up {
 animation: slideUp 0.4s ease-out;
 }
 </style>
</head>

<body>

 <div class="auth-wrapper" x-data="otpLogin()">

 <div class="auth-card">
 <a href="{{ route('home') }}" class="back-link d-none "><i class="bi bi-arrow-left"></i> Batal</a>

 <div class="text-center mb-5">
 @if($settings->get('store_logo'))
 <img src="{{ asset($settings->get('store_logo')) }}" alt="{{ $settings->storeName() }}" class="mb-3"
 style="max-height: 54px; border-radius: 8px;">
 @else
 <div class="brand-logo-icon"><i class="bi bi-whatsapp"></i></div>
 @endif
 <h3 class="fw-bold text-dark mb-1">Login Cepat</h3>
 <p class="text-muted small mb-0">Masuk aman menggunakan WhatsApp OTP tanpa perlu menghafal password.</p>
 </div>

 <!-- Alert Notification -->
 <template x-if="message">
 <div x-cloak class="alert py-3 px-3 border-0 d-flex align-items-center gap-3 mb-4 slide-up"
 :class="success ? 'alert-success' : 'alert-danger'" style="font-size:0.85rem; border-radius:12px;"
 :style="success ? 'background-color: #f0fdf4; color: #166534;' : 'background-color: #fef2f2; color: #991b1b;'">

 <i :class="success ? 'bi bi-check-circle-fill fs-5' : 'bi bi-exclamation-octagon-fill fs-5'"></i>
 <div x-text="message" class="fw-medium"></div>
 </div>
 </template>

 <!-- Step 1: Input Phone -->
 <div x-show="step === 1" x-transition:enter="slide-up">
 <div class="mb-4">
 <label class="form-label">Nomor WhatsApp Aktif</label>
 <div class="input-icon-wrapper">
 <i class="bi bi-telephone"></i>
 <input type="tel" x-model="phone" class="form-control" placeholder="Contoh: 081234567890"
 maxlength="15" @keyup.enter="sendOtp()" :disabled="loading">
 </div>
 <small class="text-muted mt-2 d-block" style="font-size: 0.75rem;">Kami akan mengirimkan 6-digit
 kode OTP ke nomor ini.</small>
 </div>

 <button class="btn btn-auth w-100" @click="sendOtp()" :disabled="loading || phone.length < 10">
 <span x-show="!loading">Kirim Kode OTP <i class="bi bi-send ms-2"></i></span>
 <span x-show="loading"><span class="spinner-border spinner-border-sm me-2"></span> Mengirim
 Token...</span>
 </button>
 </div>

 <!-- Step 2: Verify OTP -->
 <div x-show="step === 2" x-cloak x-transition:enter="slide-up">
 <div class="text-center mb-4">
 <div class="d-inline-flex px-3 py-1 bg-light rounded-pill mb-3">
 <small class="text-muted fw-bold tracking-widest text-uppercase"
 style="font-size: 0.65rem;">Verifikasi Kode</small>
 </div>
 <p class="text-muted small mb-0">Kami telah mengirim kode verifikasi ke nomor</p>
 <strong class="text-dark d-block mt-1" x-text="phone"></strong>
 </div>

 <div class="mb-4">
 <input type="text" x-model="code" class="form-control otp-input" placeholder="••••••" maxlength="6"
 @keyup.enter="verifyOtp()" :disabled="loading">
 </div>

 <button class="btn btn-auth w-100 mb-3" @click="verifyOtp()" :disabled="loading || code.length < 6">
 <span x-show="!loading">Verifikasi & Masuk <i class="bi bi-shield-check ms-2"></i></span>
 <span x-show="loading"><span class="spinner-border spinner-border-sm me-2"></span>
 Memverifikasi...</span>
 </button>

 <div class="text-center mt-4">
 <button class="btn btn-sm btn-link text-decoration-none fw-semibold" @click="step=1; message=''"
 :disabled="countdown > 0" style="color: #64748b;">
 <span x-show="countdown > 0">Kirim ulang dalam <span x-text="countdown"></span> detik</span>
 <span x-show="countdown <= 0" style="color: var(--primary);">Kirim ulang kode OTP</span>
 </button>
 </div>
 </div>

 <div class="text-center mt-5 pt-2">
 <p class="text-muted small mb-0">Atau pilih
 <a href="{{ route('login') }}" class="fw-bold text-decoration-none ms-1"
 style="color: var(--primary);">Masuk dengan Password</a>
 </p>
 </div>
 </div>
 </div>

 <script>
 function otpLogin() {
 return {
 step: 1,
 phone: '',
 code: '',
 loading: false,
 message: null,
 success: false,
 countdown: 0,

 async sendOtp() {
 if (!this.phone || this.phone.length < 10) {
 this.message = 'Harap masukkan nomor handphone yang valid.';
 this.success = false;
 return;
 }
 this.loading = true;
 this.message = '';

 try {
 const res = await fetch('{{ route("otp.send") }}', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
 },
 body: JSON.stringify({ phone: this.phone })
 });
 const data = await res.json();

 if (data.success) {
 this.step = 2;
 this.message = data.message;
 this.success = true;
 if (data.debug_code) this.message += ' [DEV_MODE_CODE: ' + data.debug_code + ']';
 this.startCountdown();

 // Let's reset code input
 this.code = '';
 } else {
 this.message = data.message || 'Gagal mengirim kode OTP. Silakan coba lagi.';
 this.success = false;
 }
 } catch (e) {
 this.message = 'Terjadi kesalahan koneksi server.';
 this.success = false;
 }
 this.loading = false;
 },

 async verifyOtp() {
 if (!this.code || this.code.length < 6) {
 this.message = 'Masukkan 6 digit kode OTP dengan benar.';
 this.success = false;
 return;
 }
 this.loading = true;
 this.message = '';

 try {
 const res = await fetch('{{ route("otp.verify") }}', {
 method: 'POST',
 headers: {
 'Content-Type': 'application/json',
 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
 },
 body: JSON.stringify({ phone: this.phone, code: this.code })
 });
 const data = await res.json();

 if (data.success) {
 this.message = 'Login berhasil! Mengalihkan...';
 this.success = true;
 setTimeout(() => {
 window.location.href = data.redirect;
 }, 500);
 } else {
 this.message = data.message || 'Kode OTP tidak valid.';
 this.success = false;
 }
 } catch (e) {
 this.message = 'Terjadi kesalahan saat memverifikasi.';
 this.success = false;
 }
 this.loading = false;
 },

 startCountdown() {
 this.countdown = 60;
 const timer = setInterval(() => {
 this.countdown--;
 if (this.countdown <= 0) {
 clearInterval(timer);
 }
 }, 1000);
 }
 }
 }
 </script>
</body>

</html>