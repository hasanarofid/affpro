<div class="col-6 col-md-4 col-lg-3">
    <div class="card h-100 border-0 shadow-sm rounded-4 text-dark text-decoration-none bg-white p-2 group-hover"
        style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
        <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark d-block">
            <div class="position-relative">
                @if($product->primaryImage)
                    <img src="{{ asset($product->primaryImage->image_path ?? $product->primaryImage->path) }}"
                        class="card-img-top rounded-3" style="height: 180px; object-fit: cover; background-color: #f8f9fa;">
                @else
                    <div class="card-img-top rounded-3 bg-light d-flex align-items-center justify-content-center"
                        style="height: 180px;">
                        <i class="bi bi-image" style="font-size: 2rem; color: #ccc;"></i>
                    </div>
                @endif

                <!-- Heart Icon (Top Right) -->
                <button
                    class="btn btn-light rounded-circle position-absolute top-0 end-0 m-2 shadow-sm d-flex align-items-center justify-content-center border-0"
                    style="width: 32px; height: 32px; padding: 0; z-index: 2;">
                    <i class="bi bi-heart text-muted" style="font-size: 0.9rem;"></i>
                </button>

                @if($product->is_featured)
                    <div class="position-absolute top-0 start-0 m-2 z-1">
                        <span class="badge bg-warning text-dark rounded-1 shadow-sm px-2 py-1" style="font-size: 0.65rem;">
                            ★
                        </span>
                    </div>
                @endif
            </div>
        </a>

        <div class="card-body p-2 pt-3 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-info bg-opacity-10 text-info fw-bold rounded-pill px-2 py-1 text-truncate"
                    style="font-size: 0.65rem; letter-spacing: 0.5px; max-width: 60%;">{{ $product->category->name ?? 'PRODUK' }}</span>
                <div class="small fw-bold text-dark d-flex align-items-center" style="font-size: 0.75rem;">
                    <i class="bi bi-star-fill text-warning me-1"></i> {{ rand(45, 50) / 10 }}
                </div>
            </div>

            <a href="{{ route('products.show', $product->slug) }}" class="text-decoration-none text-dark">
                <h6 class="card-title fw-bold mb-1 line-clamp-2" style="font-size: 0.95rem; line-height: 1.3;">
                    {{ $product->name }}
                </h6>
            </a>

            <div class="d-flex align-items-end justify-content-between mt-auto pt-2">
                <div>
                    <span
                        class="fs-6 fw-bold text-dark">Rp{{ number_format($product->effective_price, 0, ',', '.') }}</span>
                    @if($product->has_variants)
                        <div class="text-muted" style="font-size: 0.65rem;">Mulai dari</div>
                    @else
                        <div class="text-white" style="font-size: 0.65rem; user-select: none;">-</div>
                    @endif
                </div>
                <button type="button"
                    class="btn btn-dark rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                    style="width: 36px; height: 36px; padding: 0;"
                    onclick="openQuickView('{{ $product->slug }}'); event.preventDefault(); return false;">
                    <i class="bi bi-plus" style="font-size: 1.5rem;"></i>
                </button>
            </div>
        </div>
    </div>
</div>