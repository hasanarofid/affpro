@extends('theme::layouts.app')
@section('title', '500 — Server Error')

@section('content')
    <div class="container py-5 text-center" style="max-width:500px">
        <div style="font-size:6rem;line-height:1;font-weight:800;color:#dc3545">500</div>
        <h4 class="fw-bold mt-3 mb-2">Terjadi Kesalahan Server</h4>
        <p class="text-muted mb-4">Maaf, terjadi kesalahan pada server kami. Silakan coba lagi nanti.</p>
        <a href="{{ route('home') }}" class="btn btn-primary px-4" style="border-radius:10px">
            <i class="bi bi-house me-1"></i> Kembali ke Beranda
        </a>
    </div>
@endsection