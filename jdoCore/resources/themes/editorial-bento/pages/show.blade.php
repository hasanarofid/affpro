@extends('theme::layouts.app')
@section('title', $page->title)

@section('styles')
<style>
	.eb-page-hero { padding: 48px 0 74px; background: linear-gradient(135deg, #111827 0%, color-mix(in srgb, var(--primary) 56%, black 44%) 55%, color-mix(in srgb, var(--secondary) 58%, black 42%) 100%); color:#fff; margin-bottom:-48px; }
	.eb-page-wrap { max-width: 920px; margin: 0 auto; }
	.eb-page-card { background:#fff; border-radius:28px; box-shadow:0 24px 60px rgba(15,23,42,.08); border:1px solid rgba(255,255,255,.7); overflow:hidden; }
	.eb-page-content { line-height:1.95; color:#334155; font-size:1.02rem; }
	.eb-page-content h2,.eb-page-content h3,.eb-page-content h4 { font-family:var(--heading); letter-spacing:-.03em; color:#111827; margin-top:2rem; }
	.eb-page-content img { max-width:100%; height:auto; border-radius:20px; margin: 1.75rem 0; }
	.eb-page-content a { color:var(--primary); text-decoration:none; border-bottom:1px dashed var(--primary); }
</style>
@endsection

@section('content')
	<div class="eb-page-hero">
		<div class="container-xxl px-3">
			<div class="eb-page-wrap">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-3 small">
						<li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-decoration-none opacity-75">Beranda</a></li>
						<li class="breadcrumb-item active text-white" aria-current="page">{{ $page->title }}</li>
					</ol>
				</nav>
				<div class="eb-kicker mb-3">Static Page</div>
				<h1 style="font-family:var(--heading); font-size: clamp(2rem,5vw,3.8rem); line-height:1.05; letter-spacing:-.04em;">{{ $page->title }}</h1>
			</div>
		</div>
	</div>

	<div class="container-xxl px-3 mb-5" style="position:relative; z-index:2;">
		<div class="eb-page-wrap">
			<article class="eb-page-card p-4 p-lg-5">
				<div class="eb-page-content">{!! $page->content !!}</div>
			</article>
		</div>
	</div>
@endsection
