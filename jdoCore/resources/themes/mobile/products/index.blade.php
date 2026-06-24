@extends('theme::layouts.app')
@section('title', __('product.products') . ' — ' . app(\App\Services\SettingService::class)->storeName())

@section('content')
<div class="container py-4 pb-5">
    <!-- Search Box -->
    <div class="mb-4">
        <form action="{{ route('products.index') }}" method="GET">
            <div class="input-group shadow-sm" style="border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; background: #fff;">
                <span class="input-group-text bg-transparent border-0 text-muted px-3"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control border-0 bg-transparent py-3 shadow-none" style="font-size: 0.95rem;" placeholder="Cari produk impianmu..." value="{{ request('q') }}">
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                @if(request('sort'))<input type="hidden" name="sort" value="{{ request('sort') }}">@endif
                
                @if(request('q'))
                    <a href="{{ route('products.index', ['category' => request('category'), 'sort' => request('sort')]) }}" class="input-group-text bg-transparent border-0 text-danger text-decoration-none px-3">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Toggle Filter & Sorting -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="fw-bold text-dark fs-5">{{ __('product.products') }}</span>
        <button class="btn btn-white border shadow-sm rounded-pill px-3 py-2 fw-medium d-flex align-items-center gap-2" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" style="font-size: 0.85rem; background: #fff; color: #475569;">
            <i class="bi bi-sliders"></i> Filter & Urut
        </button>
    </div>

    <!-- Collapsible Filters -->
    <div class="collapse mb-4 {{ request('category') || request('sort') && request('sort') !== 'latest' ? 'show' : '' }}" id="filterCollapse">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-body p-4">
                <form method="GET">
                    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-uppercase tracking-widest text-muted">Kategori</label>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <label class="btn btn-outline-secondary rounded-pill btn-sm fw-medium {{ !request('category') ? 'active' : '' }}" style="font-size: 0.8rem;">
                                    <input type="radio" name="category" value="" class="d-none" onchange="this.form.submit()" {{ !request('category') ? 'checked' : '' }}> Semua
                                </label>
                                @foreach($categories as $cat)
                                    <label class="btn btn-outline-secondary rounded-pill btn-sm fw-medium {{ request('category') === $cat->slug ? 'active' : '' }}" style="font-size: 0.8rem;">
                                        <input type="radio" name="category" value="{{ $cat->slug }}" class="d-none" onchange="this.form.submit()" {{ request('category') === $cat->slug ? 'checked' : '' }}> 
                                        {{ $cat->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <label class="form-label small fw-bold text-uppercase tracking-widest text-muted">Urutkan Berdasarkan</label>
                            <select name="sort" class="form-select py-2 shadow-none" style="border-radius: 12px; border-color: #e2e8f0; font-size: 0.9rem; color: #475569;" onchange="this.form.submit()">
                                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Termurah</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Termahal</option>
                                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
                            </select>
                        </div>
                        @if(request('category') || request('sort') && request('sort') !== 'latest')
                        <div class="col-12 mt-3 pt-3 border-top">
                            <a href="{{ route('products.index', ['q' => request('q')]) }}" class="btn btn-danger-subtle text-danger w-100 py-2 rounded-pill fw-bold" style="background-color: #fee2e2;"><i class="bi bi-x-circle me-1"></i> Reset Filter</a>
                        </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div>
        @if(request('q'))
 <p class="text-muted mb-3">Hasil pencarian "{{ request('q') }}" — {{ $products->total() }} produk</p>
 @endif
 <div class="row g-3">
 @forelse($products as $product)
 @include('theme::partials.product-card', ['product' => $product])
 @empty
 <div class="col-12 text-center py-5">
 <i class="bi bi-emoji-frown" style="font-size:3rem;color:#ddd"></i>
 <p class="text-muted mt-3">{{ __('general.no_data') }}</p>
 </div>
 @endforelse
 </div>

 @if($products->hasPages())
 <div class="mt-4 d-flex justify-content-center">
 {{ $products->withQueryString()->links() }}
 </div>
 @endif
 </div>
</div>
@endsection
