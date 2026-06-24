@extends('theme::layouts.app')
@section('title', __('product.products') . ' — ' . app(\App\Services\SettingService::class)->storeName())

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card" style="border:none;border-radius:14px">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Filter</h6>
                    <form method="GET">
                        @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Kategori</label>
                            @foreach($categories as $cat)
                            <div class="form-check">
                                <input type="radio" name="category" value="{{ $cat->slug }}" class="form-check-input"
                                    {{ request('category') === $cat->slug ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small">{{ $cat->name }}</label>
                            </div>
                            @endforeach
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Urutkan</label>
                            <select name="sort" class="form-select form-select-sm" style="border-radius:8px" onchange="this.form.submit()">
                                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Termurah</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Termahal</option>
                                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama A-Z</option>
                            </select>
                        </div>
                        @if(request('category') || request('sort') !== 'latest')
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary w-100" style="border-radius:8px">Reset Filter</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
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
</div>
@endsection
