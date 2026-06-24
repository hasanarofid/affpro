<article class="eb-product-card h-100">
    <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark d-block h-100">
        <div class="eb-product-media">
            @if($product->primaryImage)
                <img src="{{ asset($product->primaryImage->image_path ?? $product->primaryImage->path) }}" alt="{{ $product->name }}">
            @else
                <div class="eb-product-placeholder"><i class="bi bi-image"></i></div>
            @endif
            @if($product->discount_price)
                <span class="eb-badge-sale">Sale</span>
            @endif
        </div>
        <div class="eb-product-body d-flex flex-column">
            <div class="eb-product-meta">{{ $product->category->name ?? 'Produk' }}</div>
            <h6 class="eb-product-title">{{ $product->name }}</h6>
            <div class="eb-product-price-wrap mt-auto">
                <span class="eb-product-price">Rp{{ number_format($product->effective_price, 0, ',', '.') }}</span>
                @if($product->discount_price)
                    <span class="eb-product-price-old">Rp{{ number_format($product->base_price, 0, ',', '.') }}</span>
                @endif
            </div>
        </div>
    </a>
</article>
