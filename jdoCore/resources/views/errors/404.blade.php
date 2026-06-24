@extends('theme::layouts.app')
@section('title', '404 — Halaman Tidak Ditemukan')

@section('content')
    <div class="container py-5 text-center" style="max-width:500px">
        <div
            style="font-size:6rem;line-height:1;font-weight:800;background:var(--gradient);-webkit-background-clip:text;-webkit-text-fill-color:transparent">
            404</div>
        <h4 class="fw-bold mt-3 mb-2">Halaman Tidak Ditemukan</h4>
        <p class="text-muted mb-4">Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
        <a href="{{ route('home') }}" class="btn btn-primary px-4" style="border-radius:10px">
            <i class="bi bi-house me-1"></i> Kembali ke Beranda
        </a>
    </div>
@endsection