@extends('theme::layouts.app')
@section('title', 'Editorial — ' . app(\App\Services\SettingService::class)->storeName())

@section('styles')
<style>
	.eb-blog-hero { border-radius:32px; padding:30px; background:linear-gradient(135deg, rgba(255,255,255,.95), rgba(255,255,255,.8)); border:1px solid rgba(255,255,255,.8); box-shadow:0 22px 52px rgba(15,23,42,.06); }
	.eb-blog-hero h1 { font-family:var(--heading); font-size:clamp(2rem,4vw,3.4rem); line-height:1.05; letter-spacing:-.04em; }
	.eb-blog-list { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:22px; }
	.eb-blog-list-card { background:#fff; border:1px solid #efe8dd; border-radius:24px; overflow:hidden; box-shadow:0 18px 42px rgba(15,23,42,.05); transition:transform .2s ease, box-shadow .2s ease; }
	.eb-blog-list-card:hover { transform:translateY(-3px); box-shadow:0 26px 56px rgba(15,23,42,.1); }
	.eb-blog-list-card img { width:100%; height:240px; object-fit:cover; }
	.eb-blog-placeholder { width:100%; height:240px; display:flex; align-items:center; justify-content:center; background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 65%, black 35%), color-mix(in srgb, var(--secondary) 70%, black 30%)); color:#fff; }
	.eb-blog-list-body { padding:22px; }
	.eb-blog-list-title { font-family:var(--heading); font-size:1.35rem; line-height:1.15; letter-spacing:-.03em; }
	@media (max-width: 991.98px) { .eb-blog-list { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="container-xxl px-3">
	<section class="eb-blog-hero mb-5">
		<div class="row g-4 align-items-end">
			<div class="col-lg-8">
				<span class="eb-kicker mb-3">Editorial Journal</span>
				<h1>Artikel, insight, dan inspirasi yang membuat brand Anda terasa hidup.</h1>
				<p class="text-muted mb-0" style="max-width:760px">Bukan sekadar blog biasa, tetapi ruang narasi untuk membangun kepercayaan dan karakter visual toko Anda.</p>
			</div>
			<div class="col-lg-4 text-lg-end">
				<div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">Total Artikel</div>
				<div class="fw-bold" style="font-size:2rem;line-height:1">{{ $posts->total() }}</div>
			</div>
		</div>
	</section>

	@if($posts->isEmpty())
		<div class="text-center py-5 bg-white rounded-5 border" style="border-color:#efe8dd!important">
			<i class="bi bi-journal-x fs-1 text-muted opacity-50"></i>
			<h4 class="fw-bold mt-3">Belum ada artikel</h4>
			<p class="text-muted mb-0">Artikel blog akan tampil di sini setelah diterbitkan.</p>
		</div>
	@else
		<div class="eb-blog-list">
			@foreach($posts as $post)
				<article class="eb-blog-list-card h-100">
					<a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark d-block">
						@if($post->featured_image || $post->image_path)
							<img src="{{ asset($post->featured_image ?? $post->image_path) }}" alt="{{ $post->title }}">
						@else
							<div class="eb-blog-placeholder"><i class="bi bi-journal-text" style="font-size:3rem"></i></div>
						@endif
						<div class="eb-blog-list-body">
							<div class="small text-uppercase fw-bold text-muted mb-2" style="letter-spacing:.08em">{{ $post->category ?? 'Editorial' }} · {{ $post->created_at->translatedFormat('d M Y') }}</div>
							<h3 class="eb-blog-list-title mb-2">{{ $post->title }}</h3>
							<p class="text-muted mb-3">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 120) }}</p>
							<span class="fw-semibold" style="color:var(--primary)">Baca artikel <i class="bi bi-arrow-right ms-1"></i></span>
						</div>
					</a>
				</article>
			@endforeach
		</div>
		<div class="mt-4 d-flex justify-content-center">{{ $posts->links() }}</div>
	@endif
</div>
@endsection