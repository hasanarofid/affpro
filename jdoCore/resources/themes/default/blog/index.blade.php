@extends('theme::layouts.app')
@section('title', 'Blog')

@section('content')
    <div class="container py-5">
        <h2 class="fw-bold mb-4">Blog</h2>

        @if($posts->isEmpty())
            <p class="text-muted text-center py-5">Belum ada artikel.</p>
        @else
            <div class="row g-4">
                @foreach($posts as $post)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100"
                            style="border:none;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);transition:transform .2s">
                            @if($post->featured_image || $post->image_path)
                                <img src="{{ asset($post->featured_image ?? $post->image_path) }}" class="card-img-top"
                                    style="height:200px;object-fit:cover">
                            @else
                                <div style="height:200px;background:linear-gradient(135deg,#667eea,#764ba2)"
                                    class="d-flex align-items-center justify-content-center">
                                    <i class="bi bi-journal-text text-white" style="font-size:2rem"></i>
                                </div>
                            @endif
                            <div class="card-body">
                                <small class="text-muted">{{ $post->created_at->translatedFormat('d M Y') }}</small>
                                <h6 class="fw-bold mt-1 mb-2">{{ $post->title }}</h6>
                                <p class="text-muted small">{{ Str::limit(strip_tags($post->content), 100) }}</p>
                                <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-sm btn-outline-primary"
                                    style="border-radius:8px">
                                    Baca Selengkapnya <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection