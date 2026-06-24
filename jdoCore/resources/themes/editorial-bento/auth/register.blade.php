<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	@php $settings = app(\App\Services\SettingService::class); @endphp
	<title>Daftar — {{ $settings->storeName() }}</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
	<style>
		:root { --primary: {{ $settings->primaryColor() }}; --secondary: {{ $settings->secondaryColor() }}; --gradient: linear-gradient(135deg, var(--primary), var(--secondary)); }
		body { margin:0; min-height:100vh; font-family:'Inter',sans-serif; display:grid; place-items:center; background: radial-gradient(circle at top left, rgba(79,70,229,.12), transparent 26%), radial-gradient(circle at bottom right, rgba(124,58,237,.12), transparent 30%), #f6f3ee; padding:20px; }
		.eb-auth-shell { width:100%; max-width:1180px; display:grid; grid-template-columns: 1.08fr 480px; background:rgba(255,255,255,.78); backdrop-filter:blur(18px); border:1px solid rgba(255,255,255,.7); border-radius:32px; overflow:hidden; box-shadow:0 30px 70px rgba(15,23,42,.08); }
		.eb-auth-hero { padding:48px; background:linear-gradient(135deg, #111827 0%, color-mix(in srgb, var(--primary) 56%, black 44%) 55%, color-mix(in srgb, var(--secondary) 58%, black 42%) 100%); color:#fff; }
		.eb-auth-hero h1 { font-family:'Playfair Display',serif; font-size: clamp(2rem,4vw,4rem); line-height:1.04; letter-spacing:-.04em; }
		.eb-auth-card { padding:42px 34px; background:rgba(255,255,255,.9); }
		.eb-auth-input { border-radius:16px; padding:14px 16px; border:1px solid #e5e7eb; background:#fff; }
		.eb-auth-btn { background:var(--gradient); border:0; color:#fff; padding:14px 18px; border-radius:999px; font-weight:700; }
		@media (max-width: 991.98px) { .eb-auth-shell { grid-template-columns:1fr; } .eb-auth-hero { min-height: 240px; } }
	</style>
</head>
<body>
	<div class="eb-auth-shell">
		<div class="eb-auth-hero d-flex flex-column justify-content-between">
			<div>
				<a href="{{ route('home') }}" class="text-white text-decoration-none small d-inline-flex align-items-center gap-2 mb-4"><i class="bi bi-arrow-left"></i> Kembali ke toko</a>
				<div class="small text-uppercase fw-bold opacity-75 mb-3" style="letter-spacing:.08em">Mulai berbelanja</div>
				<h1 class="mb-3">Buat akun untuk pengalaman belanja yang lebih cepat dan personal.</h1>
				<p class="text-white-50 mb-0">Simpan alamat pengiriman, pantau status setiap pesanan, dan dapatkan info promo eksklusif langsung di akun Anda.</p>
			</div>
			<div class="d-flex gap-3 flex-wrap mt-5">
				<span class="badge rounded-pill text-bg-light px-3 py-2"><i class="bi bi-geo-alt me-1"></i> Simpan Alamat</span>
				<span class="badge rounded-pill text-bg-light px-3 py-2"><i class="bi bi-receipt me-1"></i> Riwayat Pesanan</span>
				<span class="badge rounded-pill text-bg-light px-3 py-2"><i class="bi bi-tag me-1"></i> Promo Member</span>
			</div>
		</div>
		<div class="eb-auth-card">
			<div class="text-center mb-4">
				@if($settings->get('store_logo'))
					<img src="{{ asset($settings->get('store_logo')) }}" alt="{{ $settings->storeName() }}" style="max-height:48px;border-radius:8px" class="mb-3">
				@endif
				<h3 class="fw-bold mb-1">Buat Akun Baru</h3>
				<p class="text-muted small mb-0">Lengkapi data Anda untuk mulai berbelanja.</p>
			</div>
			@if($errors->any())<div class="alert alert-danger rounded-4 border-0 py-2 small">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
			<form method="POST" action="{{ route('register') }}">
				@csrf
				<div class="mb-3"><label class="form-label small fw-semibold">Nama Lengkap</label><input type="text" name="name" class="form-control eb-auth-input" value="{{ old('name') }}" required></div>
				<div class="mb-3"><label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control eb-auth-input" value="{{ old('email') }}" required></div>
				<div class="mb-3"><label class="form-label small fw-semibold">Nomor HP / WhatsApp</label><input type="tel" name="phone" class="form-control eb-auth-input" value="{{ old('phone') }}" required inputmode="tel" autocomplete="off"></div>
				<div class="row g-3">
					<div class="col-md-6"><label class="form-label small fw-semibold">Password</label><input type="password" name="password" class="form-control eb-auth-input" required></div>
					<div class="col-md-6"><label class="form-label small fw-semibold">Konfirmasi Password</label><input type="password" name="password_confirmation" class="form-control eb-auth-input" required></div>
				</div>
				<div class="form-check mt-3 mb-3"><input class="form-check-input" type="checkbox" id="terms" required><label class="form-check-label small" for="terms">Saya setuju dengan syarat & ketentuan yang berlaku.</label></div>
				<button type="submit" class="btn eb-auth-btn w-100">Daftar Sekarang</button>
			</form>
			<div class="text-center mt-4 small">Sudah punya akun? <a href="{{ route('login') }}" class="fw-bold text-decoration-none" style="color:var(--primary)">Masuk di sini</a></div>
		</div>
	</div>
</body>
</html>