@extends('theme::layouts.app')
@section('title', __('product.cart') . ' — ' . app(\App\Services\SettingService::class)->storeName())

@section('styles')
<style>
    .cart-item { transition: all 0.3s ease; }
    .cart-item.removing { opacity: 0; transform: translateX(-20px); }
    .quantity-input {
        width: 40px !important;
        background-color: transparent !important;
        border: none !important;
        text-align: center;
        font-weight: 600;
    }
    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    .btn-qty {
        width: 32px;
        height: 32px;
        border-radius: 50% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        transition: all 0.2s;
        border: 1px solid #dee2e6;
        background: white;
        color: #495057;
    }
    .btn-qty:hover:not(:disabled) {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    .btn-qty:disabled { opacity: 0.5; cursor: not-allowed; }
</style>
@endsection

@section('content')
    <div class="container py-5">
        <h3 class="fw-bold mb-4 font-heading text-dark"><i class="bi bi-cart3 me-2" style="color:var(--primary)"></i>{{ __('product.cart') }}</h3>

        @if($cart && $cart->items->count() > 0)
            <div class="row g-4" id="cart-container">
                <div class="col-lg-8" id="cart-items-wrapper">
                    <div class="card shadow-sm border-0" style="border-radius:18px; overflow:hidden;">
                        <div class="card-body p-0">
                            @foreach($cart->items as $item)
                                <div class="cart-item d-flex flex-column flex-sm-row align-items-sm-center p-4 {{ !$loop->last ? 'border-bottom' : '' }}" id="item-{{ $item->id }}">
                                    <!-- Image -->
                                    <div class="me-sm-4 mb-3 mb-sm-0" style="width:100px; height:100px; flex-shrink: 0;">
                                        @if($item->product->primaryImage)
                                            <img src="{{ asset($item->product->primaryImage->path) }}" class="rounded-4 object-fit-cover w-100 h-100 border bg-light">
                                        @else
                                            <div class="bg-light rounded-4 border d-flex align-items-center justify-content-center w-100 h-100">
                                                <i class="bi bi-image text-muted fs-3"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Info -->
                                    <div class="flex-grow-1 pe-sm-3 mb-3 mb-sm-0" style="min-width: 0;">
                                        <a href="{{ route('products.show', $item->product->slug) }}" class="text-decoration-none text-dark hover-primary fw-bold" style="font-size:1.1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $item->product->name }}</a>
                                        @if($item->variant)
                                            <span class="badge bg-light text-dark border mt-2 px-2 py-1 fw-medium" style="font-size:0.75rem">{{ $item->variant->label }}</span>
                                        @endif
                                        <div class="fw-bold mt-2 item-subtotal" style="color:var(--primary); font-size:1.1rem;" data-price="{{ $item->variant ? $item->variant->price : $item->product->effective_price }}">
                                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="d-flex align-items-center justify-content-between justify-content-sm-end gap-3 flex-shrink-0" style="min-width: 140px;">
                                        <div class="d-flex align-items-center bg-light rounded-pill p-1 border">
                                            <button type="button" class="btn btn-qty btn-minus" data-id="{{ $item->id }}" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <input type="number" class="form-control quantity-input item-qty-{{ $item->id }}" value="{{ $item->quantity }}" min="1" readonly>
                                            <button type="button" class="btn btn-qty btn-plus" data-id="{{ $item->id }}">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </div>
                                        <button class="btn btn-outline-danger btn-remove-item rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 p-0" data-id="{{ $item->id }}" title="Hapus produk" style="width: 36px; height: 36px;">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="col-lg-4" id="cart-summary-wrapper">
                    <div class="card shadow-sm border-0 position-sticky" style="top: 90px; border-radius:18px;">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4 font-heading">Ringkasan Belanja</h5>
                            <div class="d-flex justify-content-between text-muted mb-2">
                                <span>Total Item</span>
                                <span class="fw-medium text-dark"><span id="summary-total-items">{{ $cart->total_items }}</span> produk</span>
                            </div>
                            <div class="d-flex justify-content-between mb-4 mt-3 pt-3 border-top">
                                <span class="fw-bold fs-5">Total Harga</span>
                                <span class="fw-bold fs-5" style="color:var(--primary)" id="summary-total-price">Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
                            </div>
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" style="border-radius:12px; font-size:1.05rem">
                                <i class="bi bi-bag-check me-2"></i>{{ __('order.checkout') }}
                            </a>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0 pb-4 px-4 text-center">
                            <a href="{{ route('products.index') }}" class="text-decoration-none text-muted small hover-primary" style="font-weight: 500;">
                                <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Empty State (Hidden initially if items exist) -->
            <div id="empty-cart-state" class="text-center py-5 d-none">
                <div class="mb-4">
                    <i class="bi bi-cart-x text-muted opacity-25" style="font-size:6rem"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Keranjang Belanja Kosong</h4>
                <p class="text-muted mb-4 max-w-sm mx-auto">Sepertinya Anda belum menambahkan produk apapun ke keranjang Anda.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" style="border-radius:10px">
                    <i class="bi bi-shop me-2"></i>Mulai Belanja
                </a>
            </div>
        @else
            <!-- Initially Empty -->
            <div class="text-center py-5 my-md-4">
                <div class="mb-4">
                    <i class="bi bi-cart-x text-muted opacity-25" style="font-size:6rem"></i>
                </div>
                <h4 class="fw-bold text-dark mb-2">Keranjang Belanja Kosong</h4>
                <p class="text-muted mb-4">Sepertinya Anda belum menambahkan produk apapun ke keranjang Anda.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary px-4 py-3 fw-bold shadow-sm" style="border-radius:12px">
                    <i class="bi bi-shop me-2"></i>Mulai Belanja Sekarang
                </a>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Setup CSRF header
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Debounce timer
    let updateTimer;

    function formatRupiah(amount) {
        return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Update quantity ajax call
    function updateCartItem(itemId, qtyInput) {
        let quantity = parseInt(qtyInput.val());
        if(isNaN(quantity) || quantity < 1) quantity = 1;
        
        let url = "{{ route('cart.update', ':id') }}";
        url = url.replace(':id', itemId);

        $.ajax({
            url: url,
            type: 'PUT',
            data: { quantity: quantity },
            success: function(response) {
                if(response.success) {
                    // Update global count header indicator
                    $('#cart-count').text(response.count);
                    
                    // Update Item Subtotal
                    let itemRow = $('#item-' + itemId);
                    itemRow.find('.item-subtotal').text('Rp ' + response.item_subtotal);

                    // Disable minus button if qty is 1
                    itemRow.find('.btn-minus').prop('disabled', quantity <= 1);

                    // Update Totals
                    $('#summary-total-items').text(response.total_items);
                    $('#summary-total-price').text('Rp ' + response.total);
                }
            }
        });
    }

    // Minus Button
    $('.btn-minus').on('click', function() {
        let itemId = $(this).data('id');
        let input = $('.item-qty-' + itemId);
        let qty = parseInt(input.val()) - 1;
        
        if(qty >= 1) {
            input.val(qty);
            clearTimeout(updateTimer);
            updateTimer = setTimeout(() => updateCartItem(itemId, input), 300);
        }
    });

    // Plus Button
    $('.btn-plus').on('click', function() {
        let itemId = $(this).data('id');
        let input = $('.item-qty-' + itemId);
        let qty = parseInt(input.val()) + 1;
        
        input.val(qty);
        clearTimeout(updateTimer);
        updateTimer = setTimeout(() => updateCartItem(itemId, input), 300);
    });

    // Remove Item Button
    $('.btn-remove-item').on('click', function() {
        let itemId = $(this).data('id');
        let itemRow = $('#item-' + itemId);
        
        Swal.fire({
            title: 'Hapus Produk?',
            text: "Produk ini akan dihapus dari keranjang Anda.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let url = "{{ route('cart.remove', ':id') }}";
                url = url.replace(':id', itemId);

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    success: function(response) {
                        if(response.success) {
                            // Update global count
                            $('#cart-count').text(response.count);
                            
                            // Animate removal
                            itemRow.addClass('removing');
                            setTimeout(() => {
                                itemRow.remove();
                                
                                // Update Totals
                                $('#summary-total-items').text(response.total_items);
                                $('#summary-total-price').text('Rp ' + response.total);

                                // Hide borders on last item
                                $('.cart-item').removeClass('border-bottom');
                                $('.cart-item:not(:last-child)').addClass('border-bottom');

                                // Show empty state if count is 0
                                if(response.total_items == 0) {
                                    $('#cart-container').fadeOut('fast', function() {
                                        $('#empty-cart-state').removeClass('d-none').hide().fadeIn('fast');
                                    });
                                }
                            }, 300);

                            // show toast
                            const Toast = Swal.mixin({
                              toast: true,
                              position: "bottom-end",
                              showConfirmButton: false,
                              timer: 2000,
                              timerProgressBar: true,
                            });
                            Toast.fire({
                              icon: "success",
                              title: "Item dihapus dari keranjang"
                            });
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush