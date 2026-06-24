<!-- Modern Mobile Card -->
<div class="col-6 mb-2">
    <div class="card h-100 border-0 text-dark text-decoration-none bg-white product-card-mobile position-relative" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); overflow: hidden;">
        
        <!-- Image Section (Edge-to-Edge) -->
        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark d-block position-relative">
            @if($product->primaryImage)
                <img src="{{ asset($product->primaryImage->image_path ?? $product->primaryImage->path) }}" class="w-100" style="height: 155px; object-fit: cover; border-bottom: 1px solid #f1f5f9;">
            @else
                <div class="w-100 bg-light d-flex align-items-center justify-content-center" style="height: 155px; border-bottom: 1px solid #f1f5f9;">
                    <i class="bi bi-image" style="font-size: 2rem; color: #cbd5e1;"></i>
                </div>
            @endif

            <!-- Favorite & Badges overlapping image -->
            <button type="button" class="btn btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-2 d-flex align-items-center justify-content-center border-0" style="width: 28px; height: 28px; padding: 0; background: rgba(255,255,255,0.9); z-index: 2;">
                <i class="bi bi-heart" style="font-size: 0.8rem; color: #64748b;"></i>
            </button>

            @if($product->is_featured)
            <div class="position-absolute top-0 start-0 m-2 z-1">
                <span class="badge" style="background: rgba(0,0,0,0.6); color: #fff; font-size: 0.65rem; padding: 4px 8px; backdrop-filter: blur(4px);">Pilihan</span>
            </div>
            @endif
            @if($product->discount_price)
                <div class="position-absolute bottom-0 start-0 m-2 z-1">
                    @php $discount = round((($product->base_price - $product->discount_price) / $product->base_price) * 100); @endphp
                    <span class="badge bg-danger" style="font-size: 0.65rem; border-radius: 4px; box-shadow: 0 2px 4px rgba(220, 53, 69, 0.4);">{{ $discount }}% OFF</span>
                </div>
            @endif
        </a>

        <!-- Content Section -->
        <div class="card-body p-2 d-flex flex-column bg-white">
            <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark flex-grow-1">
                <h6 class="card-title mb-1 line-clamp-2 fw-medium" style="font-size: 0.8rem; line-height: 1.35; color: #334155;">
                    {{ $product->name }}
                </h6>
            </a>

            <!-- Price -->
            <div class="mt-1 mb-1">
                <div class="fw-bold text-dark" style="font-size: 0.95rem; letter-spacing: -0.5px;">
                    Rp{{ number_format($product->effective_price, 0, ',', '.') }}
                </div>
                @if($product->has_variants)
                    <div class="text-muted" style="font-size: 0.6rem; margin-top:-2px;">Mulai dari</div>
                @elseif($product->discount_price)
                    <div class="text-muted text-decoration-line-through" style="font-size: 0.6rem; margin-top:-2px;">Rp{{ number_format($product->base_price, 0, ',', '.') }}</div>
                @else
                    <div class="text-transparent" style="font-size: 0.6rem; margin-top:-2px; user-select:none;">-</div>
                @endif
            </div>

            <!-- Footer: Rating & Button -->
            <div class="mt-auto pt-2">
                <div class="text-muted d-flex align-items-center gap-1 mb-2" style="font-size: 0.65rem;">
                    <i class="bi bi-star-fill text-warning" style="font-size: 0.6rem;"></i> <span class="fw-bold text-dark">{{ rand(45, 50) / 10 }}</span> <span class="ms-1 border-start ps-1">{{ rand(10,99) }} Terjual</span>
                </div>
                <!-- Full-width Cart Button -->
                <button type="button" class="btn btn-primary btn-sm w-100 fw-medium d-flex align-items-center justify-content-center gap-2 shadow-sm"
                    style="border-radius: 8px; font-size: 0.75rem; padding: 6px 0;"
                    onclick="openQuickView('{{ $product->slug }}'); event.preventDefault(); return false;">
                    <i class="bi bi-cart-plus" style="font-size: 0.9rem;"></i> Tambah
                </button>
            </div>
        </div>
    </div>
</div>