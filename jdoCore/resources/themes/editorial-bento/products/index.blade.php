@extends('theme::layouts.app')
@section('title', __('product.products') . ' — ' . app(\App\Services\SettingService::class)->storeName())

@section('styles')
<style>
	.eb-catalog-shell { display:grid; grid-template-columns: 300px 1fr; gap: 24px; }
	.eb-filter-card { position: sticky; top: 104px; background: rgba(255,255,255,.92); border:1px solid rgba(255,255,255,.65); border-radius: 24px; box-shadow: 0 20px 50px rgba(15,23,42,.06); overflow: hidden; }
	.eb-filter-card .head { padding: 22px 22px 14px; border-bottom: 1px solid #f1ede6; }
	.eb-filter-card .body { padding: 18px 22px 22px; }
	.eb-filter-group + .eb-filter-group { margin-top: 18px; padding-top: 18px; border-top: 1px dashed #ece7de; }
	.eb-filter-title { font-size: .76rem; letter-spacing: .08em; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 12px; }
	.eb-filter-option { display:flex; align-items:center; justify-content:space-between; gap:10px; padding: 9px 0; font-size: .92rem; }
	.eb-filter-option label { cursor:pointer; color:#334155; }
	.eb-catalog-banner { border-radius: 32px; padding: 30px; background: linear-gradient(135deg, rgba(255,255,255,.94), rgba(255,255,255,.75)); border:1px solid rgba(255,255,255,.75); box-shadow: 0 22px 52px rgba(15,23,42,.06); }
	.eb-catalog-banner h1 { font-family: var(--heading); font-size: clamp(2rem, 4vw, 3.4rem); line-height:1.05; letter-spacing:-.04em; margin-bottom: 12px; }
	.eb-toolbar { display:flex; align-items:center; justify-content:space-between; gap:16px; margin: 24px 0 18px; flex-wrap: wrap; }
	.eb-sort-select { min-width: 210px; border-radius: 999px; border:1px solid #e9e4dc; padding: 10px 16px; background:#fff; box-shadow: 0 10px 26px rgba(15,23,42,.04); }
	.eb-results-meta { color:#64748b; font-size:.92rem; }
	.eb-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
	.eb-empty { text-align:center; padding: 70px 20px; background: rgba(255,255,255,.85); border-radius: 28px; border:1px dashed #e5e7eb; }
	@media (max-width: 1399.98px) { .eb-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
	@media (max-width: 1199.98px) { .eb-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
	@media (max-width: 991.98px) {
		.eb-catalog-shell { grid-template-columns: 1fr; }
		.eb-filter-card { position: static; }
	}
	@media (max-width: 575.98px) {
		.eb-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
		.eb-catalog-banner { padding: 22px; border-radius: 24px; }
	}
</style>
@endsection

@section('content')
<div class="container-xxl px-3">
	<section class="eb-catalog-banner mb-4">
		<div class="row align-items-end g-4">
			<div class="col-lg-8">
				<span class="eb-kicker mb-3">Curated Catalog</span>
				<h1>{{ request('q') ? 'Hasil pencarian yang terasa lebih curated.' : 'Katalog produk yang lebih tenang, premium, dan fokus ke brand.' }}</h1>
				<p class="text-muted mb-0" style="max-width: 760px; font-size: 1rem;">
					{{ request('q') ? 'Menampilkan hasil pencarian untuk “' . request('q') . '”.' : 'Jelajahi koleksi pilihan dengan tata letak yang bersih, elegan, dan tetap nyaman di mobile.' }}
				</p>
			</div>
			<div class="col-lg-4 text-lg-end">
				<div class="d-inline-flex flex-column align-items-lg-end gap-1">
					<div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Total Produk</div>
					<div class="fw-bold" style="font-size:2rem; line-height:1">{{ $products->total() }}</div>
				</div>
			</div>
		</div>
	</section>

	<div class="eb-catalog-shell">
		<aside>
			<form method="GET" class="eb-filter-card">
				<div class="head d-flex align-items-center justify-content-between">
					<div>
						<div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Filter</div>
						<h6 class="fw-bold mb-0 mt-1">Refine Catalog</h6>
					</div>
					@if(request('category') || request('sort') !== 'latest')
						<a href="{{ route('products.index', request('q') ? ['q' => request('q')] : []) }}" class="btn btn-sm btn-light rounded-pill px-3">Reset</a>
					@endif
				</div>
				<div class="body">
					@if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
					<div class="eb-filter-group">
						<div class="eb-filter-title">Kategori</div>
						<div class="d-grid gap-1">
							<div class="eb-filter-option">
								<label class="d-flex align-items-center gap-2 mb-0">
									<input type="radio" name="category" value="" class="form-check-input mt-0" {{ request('category') ? '' : 'checked' }} onchange="this.form.submit()">
									<span>Semua Kategori</span>
								</label>
							</div>
							@foreach($categories as $cat)
								<div class="eb-filter-option">
									<label class="d-flex align-items-center gap-2 mb-0 w-100">
										<input type="radio" name="category" value="{{ $cat->slug }}" class="form-check-input mt-0"
											{{ request('category') === $cat->slug ? 'checked' : '' }} onchange="this.form.submit()">
										<span>{{ $cat->name }}</span>
									</label>
								</div>
							@endforeach
						</div>
					</div>

					<div class="eb-filter-group">
						<div class="eb-filter-title">Urutan</div>
						<select name="sort" class="form-select eb-sort-select" onchange="this.form.submit()">
							<option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
							<option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
							<option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
							<option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
						</select>
					</div>
				</div>
			</form>
		</aside>

		<section>
			<div class="eb-toolbar">
				<div class="eb-results-meta">
					@if(request('q'))
						Hasil pencarian untuk <strong>"{{ request('q') }}"</strong> · {{ $products->total() }} produk ditemukan
					@else
						Menampilkan <strong>{{ $products->count() }}</strong> dari {{ $products->total() }} produk
					@endif
				</div>
				<form method="GET" class="d-flex align-items-center gap-2">
					@if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
					@if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
					<select name="sort" class="form-select eb-sort-select" onchange="this.form.submit()">
						<option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Terbaru</option>
						<option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Harga Terendah</option>
						<option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Harga Tertinggi</option>
						<option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
					</select>
				</form>
			</div>

			@if($products->count() > 0)
				<div class="eb-grid">
					@foreach($products as $product)
						@include('theme::partials.product-card', ['product' => $product])
					@endforeach
				</div>

				@if($products->hasPages())
					<div class="mt-4 d-flex justify-content-center">
						{{ $products->withQueryString()->links() }}
					</div>
				@endif
			@else
				<div class="eb-empty">
					<i class="bi bi-search fs-1 text-muted opacity-50"></i>
					<h5 class="fw-bold mt-3">Produk tidak ditemukan</h5>
					<p class="text-muted mb-4">Coba ganti kata kunci atau gunakan kategori lain.</p>
					<a href="{{ route('products.index') }}" class="btn btn-dark rounded-pill px-4">Kembali ke katalog</a>
				</div>
			@endif
		</section>
	</div>
</div>
@endsection