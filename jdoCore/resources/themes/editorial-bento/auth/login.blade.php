<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	@php $settings = app(\App\Services\SettingService::class); @endphp
	<title>Masuk — {{ $settings->storeName() }}</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
	<style>
		:root { --primary: {{ $settings->primaryColor() }}; --secondary: {{ $settings->secondaryColor() }}; --gradient: linear-gradient(135deg, var(--primary), var(--secondary)); }
		body { margin:0; min-height:100vh; font-family:'Inter',sans-serif; display:grid; place-items:center; background: radial-gradient(circle at top left, rgba(79,70,229,.12), transparent 26%), radial-gradient(circle at bottom right, rgba(124,58,237,.12), transparent 30%), #f6f3ee; padding:20px; }
		.eb-auth-shell { width:100%; max-width:1080px; display:grid; grid-template-columns: 1fr 460px; background:rgba(255,255,255,.78); backdrop-filter:blur(18px); border:1px solid rgba(255,255,255,.7); border-radius:32px; overflow:hidden; box-shadow:0 30px 70px rgba(15,23,42,.08); }
		.eb-auth-hero { padding:48px; background:linear-gradient(135deg, #111827 0%, color-mix(in srgb, var(--primary) 56%, black 44%) 55%, color-mix(in srgb, var(--secondary) 58%, black 42%) 100%); color:#fff; position:relative; }
		.eb-auth-hero h1 { font-family:'Playfair Display',serif; font-size: clamp(2rem,4vw,4rem); line-height:1.04; letter-spacing:-.04em; }
		.eb-auth-card { padding:42px 34px; background:rgba(255,255,255,.9); }
		.eb-auth-input { border-radius:16px; padding:14px 16px; border:1px solid #e5e7eb; background:#fff; }
		.eb-auth-btn { background:var(--gradient); border:0; color:#fff; padding:14px 18px; border-radius:999px; font-weight:700; }
		@media (max-width: 991.98px) { .eb-auth-shell { grid-template-columns:1fr; } .eb-auth-hero { min-height: 260px; } }
	</style>
</head>
<body>
	<div class="eb-auth-shell">
		<div class="eb-auth-hero d-flex flex-column justify-content-between">
			<div>
				<a href="{{ route('home') }}" class="text-white text-decoration-none small d-inline-flex align-items-center gap-2 mb-4"><i class="bi bi-arrow-left"></i> Kembali ke toko</a>
				<div class="small text-uppercase fw-bold opacity-75 mb-3" style="letter-spacing:.08em">Selamat datang kembali</div>
				<h1 class="mb-3">Lanjutkan belanja dengan pengalaman yang lebih personal.</h1>
				<p class="text-white-50 mb-0">Pantau status pesanan, akses riwayat pembelian, dan kelola alamat pengiriman dalam satu akun.</p>
			</div>
			<div class="d-flex gap-3 flex-wrap mt-5">
				<span class="badge rounded-pill text-bg-light px-3 py-2"><i class="bi bi-truck me-1"></i> Lacak Pesanan</span>
				<span class="badge rounded-pill text-bg-light px-3 py-2"><i class="bi bi-bookmark-heart me-1"></i> Wishlist</span>
				<span class="badge rounded-pill text-bg-light px-3 py-2"><i class="bi bi-lightning-charge me-1"></i> Checkout Cepat</span>
			</div>
		</div>
		<div class="eb-auth-card">
			<div class="text-center mb-4">
				@if($settings->get('store_logo'))
					<img src="{{ asset($settings->get('store_logo')) }}" alt="{{ $settings->storeName() }}" style="max-height:48px;border-radius:8px" class="mb-3">
				@endif
				<h3 class="fw-bold mb-1">Masuk Akun</h3>
				<p class="text-muted small mb-0">Selamat datang kembali di {{ $settings->storeName() }}</p>
			</div>
			@if($errors->any())<div class="alert alert-danger rounded-4 border-0 py-2 small">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
			<form method="POST" action="{{ route('login') }}">
				@csrf
				<div class="mb-3"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control eb-auth-input" value="{{ old('email') }}" required></div>
				<div class="mb-3"><label class="form-label small fw-semibold">Password</label><input type="password" name="password" class="form-control eb-auth-input" required></div>
				<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label small" for="remember">Ingat saya</label></div>
				<button type="submit" class="btn eb-auth-btn w-100">Masuk Sekarang</button>
			</form>
			<div class="text-center mt-4 small">Belum punya akun? <a href="{{ route('register') }}" class="fw-bold text-decoration-none" style="color:var(--primary)">Daftar di sini</a></div>
			@if(in_array($settings->get('login_method', 'both'), ['both', 'otp']))
				<div class="text-center mt-3"><a href="{{ route('otp.login') }}" class="text-decoration-none small" style="color:var(--secondary)"><i class="bi bi-chat-dots me-1"></i>Masuk dengan OTP WhatsApp</a></div>
			@endif
		</div>
	</div>
</body>
</html>