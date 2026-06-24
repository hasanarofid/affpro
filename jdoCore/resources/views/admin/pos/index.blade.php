@php
    $settingsService = app(\App\Services\SettingService::class);
    $storeName = $settingsService->storeName();
    $primary = $settingsService->primaryColor();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>POS — {{ $storeName }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    :root { --primary: {{ $primary }}; }
    html, body { height: 100%; }
    body {
        margin: 0;
        font-family: 'Inter', system-ui, sans-serif;
        background: #f1f5f9;
        color: #0f172a;
        overflow: hidden;
    }
    .pos-shell { display: grid; grid-template-columns: 1fr 420px; height: 100vh; }
    @media (max-width: 991px) { .pos-shell { grid-template-columns: 1fr; } .pos-cart { display: none; } .pos-cart.open { display: flex; position: fixed; inset: 0; z-index: 50; background: #fff; } }

    .pos-header {
        background: #fff; border-bottom: 1px solid #e2e8f0;
        padding: 12px 20px; display: flex; align-items: center; gap: 16px;
    }
    .pos-header h1 { font-size: 1rem; font-weight: 700; margin: 0; }
    .pos-header .pos-cashier { color: #64748b; font-size: .85rem; }
    .pos-header .btn-back {
        margin-left: auto; color: #64748b; text-decoration: none; font-size: .85rem;
        display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px;
        border-radius: 10px; transition: background .15s ease;
    }
    .pos-header .btn-back:hover { background: #f8fafc; color: #0f172a; }

    .pos-main { display: flex; flex-direction: column; overflow: hidden; }
    .pos-toolbar { padding: 16px 20px; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .pos-toolbar .form-control, .pos-toolbar .form-select { border-radius: 10px; }
    .pos-toolbar .search-box { flex: 1; min-width: 220px; position: relative; }
    .pos-toolbar .search-box .bi-search { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .pos-toolbar .search-box input { padding-left: 40px; }

    .pos-categories { display: flex; gap: 8px; padding: 12px 20px; overflow-x: auto; background: #fff; border-bottom: 1px solid #e2e8f0; }
    .pos-categories::-webkit-scrollbar { height: 6px; } .pos-categories::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    .pos-cat-chip {
        padding: 7px 16px; border-radius: 999px; border: 1px solid #e2e8f0; background: #fff;
        font-size: .82rem; font-weight: 500; color: #475569; cursor: pointer; white-space: nowrap;
        transition: all .15s ease;
    }
    .pos-cat-chip:hover { border-color: var(--primary); color: var(--primary); }
    .pos-cat-chip.active { background: var(--primary); color: #fff; border-color: var(--primary); }

    .pos-grid { flex: 1; overflow-y: auto; padding: 16px; display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; align-content: start; }
    .pos-card {
        background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;
        cursor: pointer; transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        display: flex; flex-direction: column;
    }
    .pos-card:hover:not(.out) { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,23,42,.08); border-color: var(--primary); }
    .pos-card.out { opacity: .55; cursor: not-allowed; }
    .pos-card-img { aspect-ratio: 1/1; background: #f1f5f9 center/cover no-repeat; position: relative; }
    .pos-card-img.placeholder::after { content: '\f1c5'; font-family: 'bootstrap-icons'; position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #cbd5e1; }
    .pos-card-body { padding: 10px 12px; }
    .pos-card-name { font-weight: 600; font-size: .85rem; line-height: 1.3; color: #0f172a; height: 2.6em; overflow: hidden; }
    .pos-card-price { font-weight: 700; color: var(--primary); margin-top: 4px; font-size: .9rem; }
    .pos-card-stock { font-size: .7rem; color: #64748b; margin-top: 2px; }

    .pos-cart {
        background: #fff; border-left: 1px solid #e2e8f0; display: flex; flex-direction: column;
    }
    .pos-cart-header { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; }
    .pos-cart-header h2 { font-size: 1rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px; }
    .pos-cart-customer { padding: 12px 20px; border-bottom: 1px solid #e2e8f0; }
    .pos-cart-customer .form-control { border-radius: 8px; font-size: .85rem; }
    .pos-cart-items { flex: 1; overflow-y: auto; padding: 8px 20px; }
    .pos-cart-empty { text-align: center; color: #94a3b8; padding: 40px 0; }
    .pos-cart-row { display: grid; grid-template-columns: 1fr auto; gap: 4px 8px; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
    .pos-cart-row .name { font-size: .85rem; font-weight: 600; color: #0f172a; }
    .pos-cart-row .meta { font-size: .72rem; color: #64748b; }
    .pos-cart-row .price { font-size: .85rem; font-weight: 600; color: #0f172a; align-self: end; text-align: right; white-space: nowrap; }
    .pos-cart-row .qty-control { display: flex; align-items: center; gap: 6px; margin-top: 4px; }
    .pos-cart-row .qty-control button { width: 26px; height: 26px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
    .pos-cart-row .qty-control button:hover { background: #f1f5f9; }
    .pos-cart-row .qty-control input { width: 50px; text-align: center; border: 1px solid #e2e8f0; border-radius: 6px; padding: 3px 4px; font-size: .8rem; }
    .pos-cart-row .row-remove { color: #b91c1c; background: transparent; border: 0; cursor: pointer; align-self: start; padding: 0; font-size: .9rem; }

    .pos-cart-summary { padding: 16px 20px; border-top: 1px solid #e2e8f0; background: #fafbfc; }
    .pos-cart-summary .line { display: flex; justify-content: space-between; padding: 4px 0; font-size: .9rem; }
    .pos-cart-summary .line.total { font-weight: 700; font-size: 1.05rem; color: #0f172a; padding-top: 10px; border-top: 1px dashed #e2e8f0; margin-top: 6px; }
    .pos-cart-summary .form-control { border-radius: 8px; font-size: .85rem; }
    .pos-cart-summary .form-select { border-radius: 8px; font-size: .85rem; }

    .pos-cart-actions { padding: 12px 20px 20px; border-top: 1px solid #e2e8f0; display: grid; gap: 8px; }
    .btn-primary, .btn-success {
        font-weight: 600; border-radius: 10px; padding: 12px 16px; font-size: .92rem;
    }
    .pos-cart-actions .btn-primary { background: var(--primary); border-color: var(--primary); }
    .pos-cart-actions .btn-success { background: #16a34a; border-color: #16a34a; }

    /* Variant picker modal */
    .variant-options { display: grid; gap: 8px; }
    .variant-option {
        padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 10px;
        display: flex; justify-content: space-between; align-items: center; cursor: pointer;
        transition: all .15s ease;
    }
    .variant-option:hover:not(.out) { border-color: var(--primary); background: #f8fafc; }
    .variant-option.out { opacity: .55; cursor: not-allowed; }

    /* Success modal */
    .success-icon {
        width: 64px; height: 64px; border-radius: 50%; background: #dcfce7;
        color: #16a34a; display: flex; align-items: center; justify-content: center;
        font-size: 2rem; margin: 0 auto;
    }
</style>
</head>
<body x-data="posApp()" x-init="init()">

<div class="pos-shell">
    <!-- Main: product grid -->
    <main class="pos-main">
        <header class="pos-header">
            <h1><i class="bi bi-shop-window me-1"></i> POS — {{ $storeName }}</h1>
            <span class="pos-cashier"><i class="bi bi-person-badge me-1"></i>{{ auth()->user()->name }}</span>
            <a href="{{ route('admin.dashboard') }}" class="btn-back">
                <i class="bi bi-arrow-left"></i> Kembali ke Admin
            </a>
        </header>

        <div class="pos-toolbar">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Cari produk berdasarkan nama atau SKU..."
                    x-model="searchQuery" @input.debounce.300ms="loadProducts()" autofocus>
            </div>
            <button type="button" class="btn btn-light d-lg-none" @click="cartOpen = true">
                <i class="bi bi-cart3"></i>
                <span class="badge bg-danger" x-show="cart.length > 0" x-text="cart.length"></span>
            </button>
        </div>

        <div class="pos-categories">
            <div class="pos-cat-chip" :class="{ active: !selectedCategory }" @click="selectedCategory = null; loadProducts()">Semua</div>
            @foreach($categories as $cat)
                <div class="pos-cat-chip" :class="{ active: selectedCategory == {{ $cat->id }} }"
                     @click="selectedCategory = {{ $cat->id }}; loadProducts()">
                    {{ $cat->name }}
                </div>
            @endforeach
        </div>

        <div class="pos-grid">
            <template x-for="p in products" :key="p.id">
                <div class="pos-card" :class="{ out: !p.has_variants && p.stock <= 0 }" @click="addProduct(p)">
                    <div class="pos-card-img" :class="{ placeholder: !p.image }" :style="p.image ? `background-image: url('${p.image}')` : ''"></div>
                    <div class="pos-card-body">
                        <div class="pos-card-name" x-text="p.name"></div>
                        <div class="pos-card-price">Rp <span x-text="formatNum(p.effective_price)"></span></div>
                        <div class="pos-card-stock">
                            <template x-if="p.has_variants"><span><i class="bi bi-layers me-1"></i>Multi varian</span></template>
                            <template x-if="!p.has_variants && p.stock > 0"><span>Stok: <span x-text="p.stock"></span></span></template>
                            <template x-if="!p.has_variants && p.stock <= 0"><span class="text-danger">Habis</span></template>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="products.length === 0 && !loading">
                <div class="text-center text-muted py-5" style="grid-column: 1/-1">
                    <i class="bi bi-box-seam fs-1 opacity-50"></i>
                    <div class="mt-2">Tidak ada produk yang cocok.</div>
                </div>
            </template>
        </div>
    </main>

    <!-- Sidebar: cart -->
    <aside class="pos-cart" :class="{ open: cartOpen }">
        <header class="pos-cart-header">
            <h2><i class="bi bi-receipt"></i> Pesanan
                <button type="button" class="btn btn-sm btn-light ms-auto d-lg-none" @click="cartOpen = false">
                    <i class="bi bi-x-lg"></i>
                </button>
            </h2>
        </header>

        <div class="pos-cart-customer">
            <div class="row g-2">
                <div class="col-7">
                    <input type="text" class="form-control" placeholder="Nama pelanggan (opsional)" x-model="customerName">
                </div>
                <div class="col-5">
                    <input type="text" class="form-control" placeholder="No. HP" x-model="customerPhone">
                </div>
            </div>
        </div>

        <div class="pos-cart-items">
            <template x-if="cart.length === 0">
                <div class="pos-cart-empty">
                    <i class="bi bi-cart3 fs-1 opacity-50"></i>
                    <div class="mt-2 small">Klik produk di kiri untuk menambah ke keranjang.</div>
                </div>
            </template>
            <template x-for="(item, idx) in cart" :key="item.uid">
                <div class="pos-cart-row">
                    <div>
                        <div class="name" x-text="item.name"></div>
                        <div class="meta" x-show="item.variant_label" x-text="item.variant_label"></div>
                        <div class="qty-control">
                            <button type="button" @click="decrementQty(idx)">−</button>
                            <input type="number" min="1" :max="item.max_stock" x-model.number="item.quantity"
                                @change="ensureQty(idx)">
                            <button type="button" @click="incrementQty(idx)">+</button>
                        </div>
                    </div>
                    <div class="d-flex flex-column align-items-end">
                        <button type="button" class="row-remove" @click="cart.splice(idx, 1)" title="Hapus">
                            <i class="bi bi-x-circle"></i>
                        </button>
                        <div class="price">Rp <span x-text="formatNum(item.price * item.quantity)"></span></div>
                    </div>
                </div>
            </template>
        </div>

        <div class="pos-cart-summary">
            <div class="line"><span>Subtotal</span><span>Rp <span x-text="formatNum(subtotal)"></span></span></div>
            <div class="line">
                <span>Diskon Manual</span>
                <input type="number" class="form-control form-control-sm text-end" style="width:120px"
                    min="0" step="1" inputmode="numeric" x-model.number="manualDiscount">
            </div>
            <div class="line total"><span>Total</span><span>Rp <span x-text="formatNum(total)"></span></span></div>

            <div class="row g-2 mt-2">
                <div class="col-6">
                    <label class="form-label small mb-1">Pembayaran</label>
                    <select class="form-select" x-model="paymentMethod">
                        <option value="cash">Tunai</option>
                        <option value="manual_transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">Uang Diterima</label>
                    <input type="number" class="form-control" min="0" step="1" inputmode="numeric"
                        x-model.number="paymentAmount" placeholder="Otomatis">
                </div>
                <div class="col-12" x-show="paymentAmount > 0 && paymentAmount >= total">
                    <div class="alert alert-success py-2 mb-0 small">
                        Kembalian: <strong>Rp <span x-text="formatNum(paymentAmount - total)"></span></strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="pos-cart-actions">
            <button type="button" class="btn btn-success" @click="submitOrder()" :disabled="cart.length === 0 || submitting">
                <span x-show="!submitting"><i class="bi bi-check-circle me-1"></i> Bayar & Cetak</span>
                <span x-show="submitting"><span class="spinner-border spinner-border-sm me-1"></span> Memproses...</span>
            </button>
            <button type="button" class="btn btn-outline-secondary" @click="resetCart()" :disabled="cart.length === 0">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
            </button>
        </div>
    </aside>
</div>

<!-- Variant Picker Modal -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Varian — <span x-text="variantTarget?.name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="variant-options">
                    <template x-for="v in (variantTarget?.variants || [])" :key="v.id">
                        <div class="variant-option" :class="{ out: v.stock <= 0 }" @click="addVariant(v)">
                            <div>
                                <div class="fw-semibold" x-text="v.label"></div>
                                <div class="small text-muted">Stok: <span x-text="v.stock"></span><span x-show="v.sku"> · SKU: <span x-text="v.sku"></span></span></div>
                            </div>
                            <div class="fw-bold" style="color: var(--primary)">Rp <span x-text="formatNum(v.price)"></span></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:16px">
            <div class="modal-body p-4 text-center">
                <div class="success-icon mb-3"><i class="bi bi-check-lg"></i></div>
                <h5 class="fw-bold mb-1">Pesanan Berhasil</h5>
                <div class="text-muted small mb-3">
                    No: <strong x-text="lastOrder?.order_number"></strong><br>
                    Total: <strong>Rp <span x-text="formatNum(lastOrder?.total)"></span></strong>
                    <template x-if="lastOrder?.change > 0">
                        <span> · Kembalian: <strong>Rp <span x-text="formatNum(lastOrder?.change)"></span></strong></span>
                    </template>
                </div>
                <div class="d-grid gap-2">
                    <a :href="lastOrder?.thermal_url + '?width=80&autoprint=1'" target="_blank" class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i> Cetak Thermal 80mm
                    </a>
                    <a :href="lastOrder?.thermal_url + '?width=58&autoprint=1'" target="_blank" class="btn btn-outline-primary">
                        <i class="bi bi-printer me-1"></i> Cetak Thermal 58mm
                    </a>
                    <a :href="lastOrder?.pdf_url" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-file-pdf me-1"></i> Download Invoice PDF
                    </a>
                    <button type="button" class="btn btn-light" @click="closeSuccess()">
                        Pesanan Baru
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
function posApp() {
    return {
        products: [],
        loading: false,
        searchQuery: '',
        selectedCategory: null,
        cart: [],
        cartOpen: false,
        customerName: '',
        customerPhone: '',
        paymentMethod: 'cash',
        paymentAmount: 0,
        manualDiscount: 0,
        submitting: false,
        variantTarget: null,
        variantModal: null,
        successModal: null,
        lastOrder: null,

        init() {
            this.variantModal = new bootstrap.Modal(document.getElementById('variantModal'));
            this.successModal = new bootstrap.Modal(document.getElementById('successModal'));
            this.loadProducts();
        },

        formatNum(n) { return new Intl.NumberFormat('id-ID').format(Math.max(0, Number(n) || 0)); },

        async loadProducts() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.searchQuery) params.append('q', this.searchQuery);
                if (this.selectedCategory) params.append('category_id', this.selectedCategory);
                const res = await fetch(`{{ route('admin.pos.products') }}?` + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                this.products = data.data || [];
            } catch (e) { console.error(e); }
            this.loading = false;
        },

        addProduct(p) {
            if (p.has_variants) {
                this.variantTarget = p;
                this.variantModal.show();
                return;
            }
            if (p.stock <= 0) return;
            this._pushCart({
                product_id: p.id,
                product_variant_id: null,
                name: p.name,
                variant_label: '',
                price: p.effective_price,
                max_stock: p.stock,
            });
        },

        addVariant(v) {
            if (v.stock <= 0) return;
            this._pushCart({
                product_id: this.variantTarget.id,
                product_variant_id: v.id,
                name: this.variantTarget.name,
                variant_label: v.label,
                price: v.price,
                max_stock: v.stock,
            });
            this.variantModal.hide();
        },

        _pushCart(payload) {
            const existing = this.cart.find(c => c.product_id === payload.product_id && c.product_variant_id === payload.product_variant_id);
            if (existing) {
                existing.quantity = Math.min(existing.quantity + 1, existing.max_stock);
                return;
            }
            this.cart.push({
                ...payload,
                uid: `${payload.product_id}-${payload.product_variant_id || 0}-${Date.now()}`,
                quantity: 1,
            });
        },

        incrementQty(idx) {
            const item = this.cart[idx];
            if (item.quantity < item.max_stock) item.quantity++;
        },
        decrementQty(idx) {
            const item = this.cart[idx];
            if (item.quantity > 1) item.quantity--; else this.cart.splice(idx, 1);
        },
        ensureQty(idx) {
            const item = this.cart[idx];
            if (item.quantity < 1) item.quantity = 1;
            if (item.quantity > item.max_stock) item.quantity = item.max_stock;
        },

        get subtotal() {
            return this.cart.reduce((s, c) => s + (Number(c.price) * Number(c.quantity)), 0);
        },
        get total() {
            return Math.max(0, this.subtotal - (Number(this.manualDiscount) || 0));
        },

        async submitOrder() {
            if (this.cart.length === 0) return;
            this.submitting = true;
            try {
                const payload = {
                    items: this.cart.map(c => ({
                        product_id: c.product_id,
                        product_variant_id: c.product_variant_id,
                        quantity: c.quantity,
                        price_override: c.price,
                    })),
                    customer_name: this.customerName,
                    customer_phone: this.customerPhone,
                    payment_method: this.paymentMethod,
                    payment_amount: this.paymentAmount || this.total,
                    discount_amount: this.manualDiscount || 0,
                };
                const res = await fetch(`{{ route('admin.pos.orders.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (!data.success) {
                    alert(data.message || 'Gagal membuat pesanan.');
                    return;
                }
                this.lastOrder = data.data;
                this.successModal.show();
                this.resetCart(true);
                this.loadProducts(); // refresh stock
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan jaringan.');
            }
            this.submitting = false;
        },

        resetCart(silent = false) {
            this.cart = [];
            this.customerName = '';
            this.customerPhone = '';
            this.manualDiscount = 0;
            this.paymentAmount = 0;
            this.paymentMethod = 'cash';
        },

        closeSuccess() {
            this.successModal.hide();
            this.lastOrder = null;
        },
    };
}
</script>
</body>
</html>
