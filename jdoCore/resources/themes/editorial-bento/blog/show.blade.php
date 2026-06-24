@extends('theme::layouts.app')
@section('title', $post->meta_title ?? $post->title)

@section('styles')
<style>
	.eb-article-hero { padding: 48px 0 74px; background: linear-gradient(135deg, #111827 0%, color-mix(in srgb, var(--primary) 56%, black 44%) 55%, color-mix(in srgb, var(--secondary) 58%, black 42%) 100%); color:#fff; margin-bottom:-48px; }
	.eb-article-wrap { max-width: 920px; margin: 0 auto; }
	.eb-article-card { background:#fff; border-radius:28px; box-shadow:0 24px 60px rgba(15,23,42,.08); border:1px solid rgba(255,255,255,.7); overflow:hidden; }
	.eb-article-cover { width:100%; max-height:520px; object-fit:cover; }
	.eb-article-content { line-height:1.95; color:#334155; font-size:1.02rem; }
	.eb-article-content h2,.eb-article-content h3,.eb-article-content h4 { font-family:var(--heading); letter-spacing:-.03em; color:#111827; margin-top:2rem; }
	.eb-article-content img { max-width:100%; height:auto; border-radius:20px; margin: 1.75rem 0; }
	.eb-article-content a { color:var(--primary); text-decoration:none; border-bottom:1px dashed var(--primary); }
</style>
@endsection

@section('content')
	<div class="eb-article-hero">
		<div class="container-xxl px-3">
			<div class="eb-article-wrap">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-3 small">
						<li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-decoration-none opacity-75">Beranda</a></li>
						<li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-white text-decoration-none opacity-75">Editorial</a></li>
						<li class="breadcrumb-item active text-white" aria-current="page">Artikel</li>
					</ol>
				</nav>
				<div class="eb-kicker mb-3">{{ $post->category ?? 'Editorial' }}</div>
				<h1 style="font-family:var(--heading); font-size: clamp(2rem,5vw,3.8rem); line-height:1.05; letter-spacing:-.04em;">{{ $post->title }}</h1>
				<div class="d-flex flex-wrap gap-3 mt-4 text-white-50 small">
					@if($post->author)<span><i class="bi bi-person me-1"></i>{{ $post->author->name }}</span>@endif
					<span><i class="bi bi-calendar3 me-1"></i>{{ $post->created_at->translatedFormat('d F Y') }}</span>
				</div>
			</div>
		</div>
	</div>

	<div class="container-xxl px-3 mb-5" style="position:relative; z-index:2;">
		<div class="eb-article-wrap">
			<article class="eb-article-card">
				@if($post->featured_image || $post->image_path)
					<img src="{{ asset($post->featured_image ?? $post->image_path) }}" class="eb-article-cover" alt="{{ $post->title }}">
				@endif
				<div class="p-4 p-lg-5">
					<div class="eb-article-content">{!! $post->content !!}</div>
					<hr class="my-5" style="border-color:#ece7de">
					<div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
						<a href="{{ route('blog.index') }}" class="btn btn-outline-dark rounded-pill px-4"><i class="bi bi-arrow-left me-1"></i> Kembali ke Editorial</a>
						<div class="d-flex gap-2">
							<a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="btn btn-light rounded-circle"><i class="bi bi-facebook"></i></a>
							<a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" class="btn btn-light rounded-circle"><i class="bi bi-twitter-x"></i></a>
							<a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . request()->url()) }}" target="_blank" class="btn btn-light rounded-circle"><i class="bi bi-whatsapp"></i></a>
						</div>
					</div>
				</div>
			</article>
		</div>
	</div>
@endsection