@extends('theme::layouts.app')
@section('title', $page->title)

@section('styles')
 <style>
 .page-header {
 background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
 color: white;
 padding-top: 3.5rem;
 padding-bottom: 7rem;
 margin-bottom: -5rem;
 }

 .page-card {
 background: #fff;
 border-radius: 20px;
 box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
 overflow: hidden;
 border: 1px solid rgba(0, 0, 0, 0.02);
 }

 .page-content {
 line-height: 1.8;
 font-size: 1.1rem;
 color: #4b5563;
 font-family: 'Inter', sans-serif;
 }

 .page-content p {
 margin-bottom: 1.5rem;
 }

 .page-content img {
 max-width: 100%;
 height: auto;
 border-radius: 12px;
 box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
 margin: 2rem 0;
 }

        .page-content h2 {
            font-size: 1.5rem;
            color: #1f2937;
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
        }

        .page-content h3 {
            font-size: 1.3rem;
            color: #1f2937;
            font-weight: 700;
            margin-top: 1.75rem;
            margin-bottom: 0.75rem;
        }

        .page-content h4 {
            font-size: 1.15rem;
            color: #1f2937;
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }

 .page-content a {
 color: var(--primary);
 text-decoration: none;
 border-bottom: 1px dashed var(--primary);
 transition: all 0.2s;
 }

 .page-content a:hover {
 color: var(--secondary);
 border-bottom-color: var(--secondary);
 }

 .page-breadcrumb .breadcrumb-item+.breadcrumb-item::before {
 color: rgba(255, 255, 255, 0.5);
 }
 </style>
@endsection

@section('content')
 <!-- Header Section -->
 <div class="page-header">
 <div class="container" style="max-width: 850px;">
 <nav aria-label="breadcrumb" class="page-breadcrumb">
 <ol class="breadcrumb">
 <li class="breadcrumb-item"><a href="{{ route('home') }}"
 class="text-white text-decoration-none opacity-75"><i class="bi bi-house-door"></i> Home</a>
 </li>
 <li class="breadcrumb-item text-white active fw-medium" aria-current="page" style="opacity: 0.9;">
 {{ $page->title }}</li>
 </ol>
 </nav>

  <h1 class="fw-bold mt-3 mb-4 text-white" style="line-height: 1.3; font-size: clamp(1.5rem, 7vw, 2.2rem);">
 {{ $page->title }}</h1>
 </div>
 </div>

 <!-- Main Content -->
 <div class="container mb-5 position-relative" style="max-width: 850px; z-index: 10;">
 <div class="page-card">
 <div class="p-4 ">
 <div class="page-content">
 {!! $page->content !!}
 </div>
 </div>
 </div>
 </div>
@endsection