@extends('theme::layouts.app')
@section('title', $post->meta_title ?? $post->title)

@section('styles')
    <style>
        .blog-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding-top: 3.5rem;
            padding-bottom: 7rem;
            margin-bottom: -5rem;
        }

        .blog-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.02);
        }

        .blog-content {
            line-height: 1.8;
            font-size: 1.1rem;
            color: #4b5563;
            font-family: 'Inter', sans-serif;
        }

        .blog-content p {
            margin-bottom: 1.5rem;
        }

        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            margin: 2rem 0;
        }

        .blog-content h2 {
            font-size: 1.8rem;
            color: #1f2937;
            font-weight: 700;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
        }

        .blog-content h3 {
            font-size: 1.5rem;
            color: #1f2937;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }

        .blog-content h4 {
            font-size: 1.25rem;
            color: #1f2937;
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .blog-content h2 { font-size: 1.5rem; margin-top: 2rem; }
            .blog-content h3 { font-size: 1.3rem; margin-top: 1.75rem; }
            .blog-content h4 { font-size: 1.15rem; margin-top: 1.5rem; }
        }

        .blog-content a {
            color: var(--primary);
            text-decoration: none;
            border-bottom: 1px dashed var(--primary);
            transition: all 0.2s;
        }

        .blog-content a:hover {
            color: var(--secondary);
            border-bottom-color: var(--secondary);
        }

        .blog-author-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.9rem;
        }

        /* Breadcrumb styling for dark bg */
        .blog-breadcrumb .breadcrumb-item+.breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
        }
    </style>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="blog-header">
        <div class="container" style="max-width: 850px;">
            <nav aria-label="breadcrumb" class="blog-breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}"
                            class="text-white text-decoration-none opacity-75"><i class="bi bi-house-door"></i> Home</a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('blog.index') }}"
                            class="text-white text-decoration-none opacity-75">Blog</a></li>
                    <li class="breadcrumb-item text-white active fw-medium" aria-current="page" style="opacity: 0.9;">Detail
                    </li>
                </ol>
            </nav>

            <h1 class="fw-bold mt-3 mb-4 text-white" style="line-height: 1.3; font-size: clamp(1.5rem, 6vw, 2.8rem);">
                {{ $post->title }}</h1>

            <div class="d-flex flex-wrap align-items-center gap-3">
                @if($post->author)
                    <div class="blog-author-badge">
                        <i class="bi bi-person-circle me-2 text-white"></i> {{ $post->author->name }}
                    </div>
                @endif
                <div class="blog-author-badge">
                    <i class="bi bi-calendar3 me-2 text-white"></i> {{ $post->created_at->translatedFormat('d F Y') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mb-5 position-relative" style="max-width: 850px; z-index: 10;">
        <div class="blog-card">
            @if($post->featured_image || $post->image_path)
                <div class="position-relative">
                    <img src="{{ asset($post->featured_image ?? $post->image_path) }}" class="w-100"
                        style="max-height: 500px; object-fit: cover; border-bottom: 1px solid rgba(0,0,0,0.05);">
                </div>
            @endif

            <div class="p-4 p-md-5">
                <div class="blog-content">
                    {!! $post->content !!}
                </div>

                <hr class="my-5" style="border-color: rgba(0,0,0,0.08);">

                <!-- Share & Action -->
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-4">
                    <a href="{{ route('blog.index') }}"
                        class="btn btn-light rounded-pill px-4 py-2 border fw-medium d-flex align-items-center shadow-sm"
                        style="transition: all 0.3s; color: #4b5563;">
                        <i class="bi bi-arrow-left me-2"></i> Kembali ke Blog
                    </a>

                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Bagikan:</span>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                                target="_blank"
                                class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center border-0 bg-light shadow-sm"
                                style="width: 40px; height: 40px; transition: 0.3s; font-size: 1.1rem;"
                                onmouseover="this.classList.add('bg-primary', 'text-white');"
                                onmouseout="this.classList.remove('bg-primary', 'text-white');">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}"
                                target="_blank"
                                class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center border-0 bg-light shadow-sm"
                                style="width: 40px; height: 40px; transition: 0.3s; font-size: 1.1rem;"
                                onmouseover="this.classList.add('bg-dark', 'text-white');"
                                onmouseout="this.classList.remove('bg-dark', 'text-white');">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($post->title . ' ' . request()->url()) }}"
                                target="_blank"
                                class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center border-0 bg-light shadow-sm"
                                style="width: 40px; height: 40px; transition: 0.3s; font-size: 1.1rem;"
                                onmouseover="this.classList.add('bg-success', 'text-white');"
                                onmouseout="this.classList.remove('bg-success', 'text-white');">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection