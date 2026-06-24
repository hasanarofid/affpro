@extends('theme::layouts.app')
@section('title', __('order.checkout') . ' — ' . app(\App\Services\SettingService::class)->storeName())

@section('content')
<div x-data="checkoutForm()" @set-guest-city.window="setGuestCity($event.detail)">
 <div class="container py-4">
 <h4 class="fw-bold mb-4"><i class="bi bi-credit-card me-2"></i>{{ __('order.checkout') }}</h4>

 <form action="{{ route('checkout.process') }}" method="POST">
 @csrf
 <input type="hidden" name="voucher_code" :value="voucher.code">
 <div class="row g-4">
 <div>
 <!-- Shipping Address -->
 @if(!$cart->is_digital_only)
 <div class="card mb-3" style="border:none;border-radius:14px">
 <div class="card-body">
 <h6 class="fw-semibold mb-3"><i
 class="bi bi-geo-alt me-2"></i>{{ __('order.shipping_address') }}</h6>

 <!-- Guest info consolidated into shipping address block below --> @if(auth()->check())
 <input type="hidden" name="address_id" :value="selectedAddressId">

 <template x-if="selectedAddress">
 <div class="card bg-light border-0 shadow-sm" style="border-radius:12px;">
 <div class="card-body p-3">
 <div class="d-flex justify-content-between align-items-start mb-2">
 <div class="fw-bold text-dark">
 <span x-text="selectedAddress.recipient_name"></span> 
 <span class="fw-normal text-muted ms-1" x-text="'(' + selectedAddress.phone + ')'"></span>
 </div>
 <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-3" data-bs-toggle="modal" data-bs-target="#selectAddressModal" style="font-size:0.8rem">Ubah Alamat</button>
 </div>
 <div class="text-muted small" x-text="selectedAddress.address_line"></div>
 <template x-if="selectedAddress.city || selectedAddress.province">
 <div class="text-muted small" x-text="selectedAddress.city + ', ' + selectedAddress.province + ' ' + (selectedAddress.postal_code || '')"></div>
 </template>
 </div>
 </div>
 </template>
 
 <template x-if="!selectedAddress">
 <div class="text-center py-4 bg-light rounded" style="border: 2px dashed #dee2e6;">
 <i class="bi bi-geo text-muted" style="font-size:2rem"></i>
 <p class="text-muted small mt-2 mb-3">Pilih alamat pengiriman Anda.</p>
 <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectAddressModal">
 <i class="bi bi-geo-alt me-1"></i> Pilih Alamat
 </button>
 </div>
 </template>
 @else
 <div class="row g-3" x-data="locationSearch('{{ old('shipping.province') }}', '{{ old('shipping.province_id') }}', '{{ old('shipping.city') }}', '{{ old('shipping.city_id') }}', '{{ old('shipping.postal_code') }}')">
 <div>
 <label class="form-label small">{{ __('order.recipient_name') }} <span
 class="text-danger">*</span></label>
 <input type="text" name="guest_name" class="form-control"
 value="{{ old('guest_name') }}" required>
 </div>
 <div>
 <label class="form-label small">{{ __('order.recipient_phone') }} <span
 class="text-danger">*</span></label>
 <input type="text" name="guest_phone" class="form-control"
 value="{{ old('guest_phone') }}" required>
 </div>

 <div class="col-12">
 <label class="form-label small fw-medium">Kecamatan/Kota (RajaOngkir) *</label>
 <div class="position-relative">
 <input type="text" x-model="search" @input.debounce.500ms="fetchLocations" @focus="showDropdown = true" @click.away="showDropdown = false" class="form-control" placeholder="Ketik minimal 3 huruf..." autocomplete="off" required>
 
 <div x-show="loading" class="position-absolute end-0 top-50 translate-middle-y me-3">
 <span class="spinner-border spinner-border-sm text-primary"></span>
 </div>

 <div x-show="showDropdown && results.length > 0" class="position-absolute w-100 bg-white border mt-1 shadow-sm" style="max-height: 200px; overflow-y: auto; border-radius: 10px; z-index: 1050; display: none;" x-transition>
 <template x-for="item in results" :key="item.id">
 <div @click="selectLocation(item)" class="p-2 border-bottom" style="cursor: pointer; font-size: 0.85rem;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
 <i class="bi bi-geo-alt text-muted me-2"></i><span x-text="item.label || item.name || (item.city_name + ', ' + item.province)"></span>
 </div>
 </template>
 </div>
 </div>
 
 <input type="hidden" name="shipping[province]" x-model="province">
 <input type="hidden" name="shipping[province_id]" x-model="province_id">
 <input type="hidden" name="shipping[city]" x-model="city">
 <input type="hidden" name="shipping[city_id]" x-model="city_id">
 </div>
 <div>
 <label class="form-label small">Kode Pos</label>
 <input type="text" name="shipping[postal_code]" x-model="postal_code" class="form-control"
 value="{{ old('shipping.postal_code') }}">
 </div>
 <div class="col-12">
 <label class="form-label small">{{ __('order.address') }} Lengkap <span
 class="text-danger">*</span></label>
 <textarea name="shipping[address]" class="form-control" rows="3"
 required>{{ old('shipping.address') }}</textarea>
 </div>
 </div>
 @endif
 </div>
 </div>
 @else
 <div class="card mb-3 bg-light border-0" style="border-radius:14px">
 <div class="card-body">
 <h6 class="fw-semibold mb-2"><i class="bi bi-cloud-arrow-down me-2 text-primary"></i>Produk Digital</h6>
 <p class="text-muted small mb-0">Pesanan ini hanya berisi produk digital. Tidak diperlukan alamat pengiriman. Bukti/Link akses akan dikirimkan otomatis setelah pembayaran lunas.</p>
 @guest
 <hr>
 <div class="row g-3">
 <div>
 <label class="form-label small">{{ __('order.name') }} <span
 class="text-danger">*</span></label>
 <input type="text" name="guest_name" class="form-control"
 value="{{ old('guest_name') }}" required>
 </div>
 <div>
 <label class="form-label small">{{ __('order.phone') }} <span
 class="text-danger">*</span></label>
 <input type="text" name="guest_phone" class="form-control"
 value="{{ old('guest_phone') }}" required>
 </div>

 </div>
 @endguest
 </div>
 </div>
 @endif

 <!-- Courier / Shipping Selection -->
 @if(!$cart->is_digital_only)
 <div class="card mb-3" style="border:none;border-radius:14px" x-show="selectedAddress || guestCityId">
 <div class="card-body">
 <h6 class="fw-semibold mb-3"><i class="bi bi-truck me-2"></i>Kurir Pengiriman</h6>
 <input type="hidden" name="shipping_cost" :value="shippingCost">
 <template x-if="shippingOptions.length > 0">
 <div class="row g-2">
 <template x-for="(opt, index) in shippingOptions" :key="index">
 <div class="col-12 ">
 <!-- courier struct format -->
 <input type="hidden" :name="'courier_info['+index+'][code]'" :value="opt.courier_code" :disabled="selectedCourierIndex !== index">
 <input type="hidden" :name="'courier_info['+index+'][service]'" :value="opt.service" :disabled="selectedCourierIndex !== index">
 <input type="hidden" :name="'courier_info['+index+'][etd]'" :value="opt.etd" :disabled="selectedCourierIndex !== index">

 <div class="card h-100 border transition-all" 
 :class="selectedCourierIndex === index ? 'border-primary bg-primary bg-opacity-10 shadow-sm' : 'border-light bg-white'" 
 style="border-radius:14px; cursor:pointer;" @click="selectShippingOption(index)">
 <div class="card-body p-2 px-3">
 <div class="d-flex align-items-center">
 <div class="me-2">
 <div class="bg-white rounded border d-flex align-items-center justify-content-center shadow-sm" 
 style="width: 48px; height: 48px; min-width: 48px;">
 <img :src="getCourierLogo(opt.courier_code)" :alt="opt.courier_code" class="img-fluid p-1" 
 style="max-height: 38px; object-fit: contain;"
 onerror="this.onerror=null; this.src='/assets/images/shipping/toko.png';">
 </div>
 </div>
 <div class="flex-grow-1 min-w-0">
 <div class="d-flex justify-content-between align-items-start mb-0">
 <span class="fw-bold text-dark text-uppercase small truncate-1" x-text="opt.courier_code"></span>
 <span class="fw-bold text-primary small">Rp<span x-text="formatNumber(opt.cost)"></span></span>
 </div>
 <div class="text-secondary small truncate-1" style="font-size: 0.75rem" x-text="opt.service"></div>
 <div class="d-flex align-items-center justify-content-between mt-1">
 <span class="text-muted small" style="font-size: 0.65rem">
 <i class="bi bi-truck me-1"></i><span x-text="opt.etd"></span> Hari
 </span>
 <div class="form-check p-0 m-0" style="min-height: 0;">
 <input class="form-check-input ms-0 mt-0 shadow-none" type="radio" 
 :value="index" x-model.number="selectedCourierIndex" @click.stop="" style="width: 0.9rem; height: 0.9rem;">
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </template>
 </div>

 </template>
 
 <template x-if="fetchingShipping">
 <div class="text-center py-4">
 <div class="spinner-border text-primary" role="status">
 <span class="visually-hidden">Loading...</span>
 </div>
 <p class="text-muted small mt-2 mb-0">Memeriksa ongkir tujuan...</p>
 </div>
 </template>

 <template x-if="!fetchingShipping && shippingError">
 <div class="alert alert-danger small mb-0 py-2 border-0" style="border-radius:10px">
 <i class="bi bi-exclamation-triangle me-1"></i>
 <span x-text="shippingError"></span>
 <p class="mb-0 mt-1 text-muted" style="font-size:0.75rem">Pastikan API key RajaOngkir di Pengaturan masih valid. Hubungi admin jika masalah berlanjut.</p>
 </div>
 </template>

 <template x-if="!fetchingShipping && !shippingError && shippingOptions.length === 0">
 <div class="alert alert-warning small mb-0 py-2 border-0" style="border-radius:10px">Pilih alamat pengiriman terlebih dahulu untuk melihat pilihan logistik kurir pengiriman.</div>
 </template>
 </div>
 </div>
 @endif

 <!-- Payment Method -->
 @php
 $pmManual = app(\App\Services\SettingService::class)->get('payment_method_manual', '1') == '1';
 $pmCod = !$cart->is_digital_only && (app(\App\Services\SettingService::class)->get('payment_method_cod', '1') == '1');
 $pmGatewayEnabled = app(\App\Services\SettingService::class)->get('payment_method_gateway', '0') == '1';
 $pmGateway = $pmGatewayEnabled && ($hasGateway ?? false);
 $pmWalletEnabled = app(\App\Services\SettingService::class)->get('payment_method_wallet', '1') == '1';
 $walletBalance = 0;
 $pmWallet = false;
 if ($pmWalletEnabled && auth()->check()) {
 $userWallet = auth()->user()->wallet ?? auth()->user()->wallet()->firstOrCreate(['user_id' => auth()->id()], ['balance' => 0]);
 $walletBalance = (float) ($userWallet->balance ?? 0);
 $pmWallet = true;
 }

 $defaultChecked = null;
 if ($pmGateway) $defaultChecked = 'gateway';
 elseif ($pmManual) $defaultChecked = 'manual_transfer';
 elseif ($pmCod) $defaultChecked = 'cod';
 @endphp

 <div class="card mb-3" style="border:none;border-radius:14px">
 <div class="card-body">
 <h6 class="fw-semibold mb-3"><i class="bi bi-wallet2 me-2"></i>{{ __('order.payment_method') }}
 </h6>
 
 @if($pmManual)
 <div class="form-check mb-2">
 <input type="radio" name="payment_method" value="manual_transfer" class="form-check-input"
 {{ $defaultChecked === 'manual_transfer' ? 'checked' : '' }} id="pm-transfer">
 <label class="form-check-label" for="pm-transfer">
 <strong>Transfer Bank Manual</strong>
 <p class="text-muted small mb-0">Transfer ke rekening toko, lalu upload bukti bayar di detail pesanan.</p>
 </label>
 </div>
 @endif

 @if($pmCod)
 <div class="form-check mb-2">
 <input type="radio" name="payment_method" value="cod" class="form-check-input"
 {{ $defaultChecked === 'cod' ? 'checked' : '' }} id="pm-cod">
 <label class="form-check-label" for="pm-cod">
 <strong>Cash on Delivery (COD) / Bayar di Tempat</strong>
 <p class="text-muted small mb-0">Pesanan langsung diproses, bayar ke kurir saat barang tiba.</p>
 </label>
 </div>
 @endif

 @if($pmGateway)
 @php
 $providers = $gatewayProviders ?? [];
 $providerLabels = [
 'ipaymu' => ['name' => 'iPaymu', 'desc' => 'VA, QRIS, E-Wallet, Retail — pembayaran otomatis via iPaymu.'],
 'xendit' => ['name' => 'Xendit', 'desc' => 'Virtual Account, E-Wallet, QRIS — pembayaran otomatis via Xendit.'],
 'midtrans' => ['name' => 'Midtrans', 'desc' => 'VA, GoPay, ShopeePay, Kartu Kredit, QRIS — pembayaran otomatis via Midtrans.'],
 'duitku' => ['name' => 'Duitku', 'desc' => 'VA, E-Wallet, QRIS, Kartu Kredit, Retail — pembayaran otomatis via Duitku.'],
 ];
 @endphp

 @if(count($providers) === 1)
 {{-- Single provider — simple radio --}}
 <div class="form-check pb-1">
 <input type="radio" name="payment_method" value="gateway" class="form-check-input"
 {{ $defaultChecked === 'gateway' ? 'checked' : '' }} id="pm-gateway">
 <input type="hidden" name="gateway_provider" value="{{ $providers[0] }}">
 <label class="form-check-label" for="pm-gateway">
 <strong>Pembayaran Instan via {{ $providerLabels[$providers[0]]['name'] ?? ucfirst($providers[0]) }}</strong>
 <p class="text-muted small mb-0">{{ $providerLabels[$providers[0]]['desc'] ?? 'Virtual Account, E-Wallet, QRIS — pembayaran otomatis terbaca sistem.' }}</p>
 </label>
 </div>
 @else
 {{-- Multiple providers — one radio per provider --}}
 @foreach($providers as $idx => $providerKey)
 <div class="form-check pb-1 {{ !$loop->last ? 'mb-2' : '' }}">
 <input type="radio" name="payment_method" value="gateway" class="form-check-input gateway-radio"
 {{ $defaultChecked === 'gateway' && $idx === 0 ? 'checked' : '' }}
 id="pm-gateway-{{ $providerKey }}" data-provider="{{ $providerKey }}">
 <label class="form-check-label" for="pm-gateway-{{ $providerKey }}">
 <strong>{{ $providerLabels[$providerKey]['name'] ?? ucfirst($providerKey) }}</strong>
 <p class="text-muted small mb-0">{{ $providerLabels[$providerKey]['desc'] ?? 'Pembayaran otomatis terbaca sistem.' }}</p>
 </label>
 </div>
 @endforeach
 <input type="hidden" name="gateway_provider" id="gateway-provider-input"
 value="{{ $defaultChecked === 'gateway' ? $providers[0] : '' }}">
 @endif
 @endif

 @if($pmWallet)
 @php $walletEnough = $walletBalance > 0; @endphp
 <div class="form-check mb-2 {{ $walletEnough ? '' : 'opacity-75' }}">
 <input type="radio" name="payment_method" value="wallet" class="form-check-input"
 id="pm-wallet" data-required-amount="1" data-wallet-balance="{{ $walletBalance }}">
 <label class="form-check-label d-flex flex-wrap align-items-center gap-2" for="pm-wallet">
 <span><strong><i class="bi bi-wallet2 me-1"></i>Bayar pakai Saldo</strong></span>
 <span class="badge bg-primary-subtle text-primary fw-semibold">Saldo: Rp{{ number_format($walletBalance, 0, ',', '.') }}</span>
 </label>
 <p class="text-muted small mb-0 ms-4 mt-1">Pesanan langsung lunas, saldo wallet otomatis terpotong.</p>
 <div class="alert alert-warning small mt-2 mb-0 py-2 border-0 d-none" id="pm-wallet-insufficient" style="border-radius:10px">
 Saldo belum cukup. <a href="{{ route('account.wallet') }}">Top up sekarang</a>.
 </div>
 </div>
 @endif
 </div>
 </div>

 <!-- Voucher -->
 <div class="card mb-3" style="border:none;border-radius:14px">
 <div class="card-body">
 <h6 class="fw-semibold mb-3"><i class="bi bi-ticket-perforated me-2"></i>Kode Voucher</h6>
 <div class="input-group">
 <input type="text" x-model="voucherInput" class="form-control"
 placeholder="Masukkan kode voucher" style="text-transform:uppercase"
 @keyup.enter.prevent="applyVoucher()">
 <button type="button" class="btn btn-outline-primary" @click="applyVoucher()"
 :disabled="voucherLoading">
 <span x-show="!voucherLoading">Terapkan</span>
 <span x-show="voucherLoading"><span
 class="spinner-border spinner-border-sm"></span></span>
 </button>
 </div>
 <div x-show="voucher.message" class="mt-2 small"
 :class="voucher.valid ? 'text-success' : 'text-danger'" x-text="voucher.message" x-cloak>
 </div>
 <template x-if="voucher.valid">
 <div class="d-flex justify-content-between align-items-center bg-light rounded mt-2 px-3 py-2"
 style="border-radius:8px!important">
 <span class="small"><i class="bi bi-check-circle-fill text-success me-1"></i> <strong
 x-text="voucher.code"></strong></span>
 <button type="button" class="btn btn-sm btn-outline-danger" @click="removeVoucher()">
 <i class="bi bi-x"></i>
 </button>
 </div>
 </template>
 </div>
 </div>

 <!-- Notes -->
 <div class="card" style="border:none;border-radius:14px">
 <div class="card-body">
 <h6 class="fw-semibold mb-3"><i class="bi bi-chat-dots me-2"></i>{{ __('order.notes') }}</h6>
 <textarea name="notes" class="form-control" rows="2"
 placeholder="Catatan untuk penjual (opsional)">{{ old('notes') }}</textarea>
 </div>
 </div>
 </div>

 <!-- Order Summary -->
 <div>
 <div class="card" style="border:none;border-radius:14px;position:sticky;top:80px">
 <div class="card-body">
 <h6 class="fw-semibold mb-3">Ringkasan Pesanan</h6>
 @foreach($cart->items as $item)
 <div class="d-flex justify-content-between mb-3">
 <div class="small">
 <div class="fw-medium text-dark">{{ Str::limit($item->product->name, 45) }}</div>
 <div class="text-muted small">x {{ $item->quantity }} {{ $item->variant ? '('.$item->variant->name.')' : '' }}</div>
 </div>
 <div class="text-end">
 @if($item->subtotal < $item->original_subtotal)
 <div class="text-muted small text-decoration-line-through">Rp {{ number_format($item->original_subtotal, 0, ',', '.') }}</div>
 @endif
 <div class="small fw-bold text-dark">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
 </div>
 </div>
 @endforeach
 <div class="d-flex justify-content-between mb-1">
 <span>Subtotal</span>
 <span>Rp {{ number_format($cart->total_original, 0, ',', '.') }}</span>
 </div>

 @if($cart->total < $cart->total_original)
 <div class="d-flex justify-content-between mb-1 text-danger">
 <span>Potongan Produk</span>
 <span>-Rp {{ number_format($cart->total_original - $cart->total, 0, ',', '.') }}</span>
 </div>
 <hr class="my-2 opacity-50">
 <div class="d-flex justify-content-between mb-2 fw-medium">
 <span>Subtotal Belanja</span>
 <span>Rp {{ number_format($cart->total, 0, ',', '.') }}</span>
 </div>
 @endif

 <div class="d-flex justify-content-between mb-1">
 <span>Ongkir</span>
 <span class="fw-medium text-dark" x-show="shippingCost > 0">Rp <span x-text="formatNumber(shippingCost)"></span></span>
 <span class="text-muted small" x-show="shippingCost === 0">Belum dihitung</span>
 </div>

 <!-- Discount row from Voucher -->
 <template x-if="voucher.valid && voucher.type !== 'shipping'">
 <div class="d-flex justify-content-between mb-1 text-success">
 <span>Potongan Voucher (<span x-text="voucher.code"></span>)</span>
 <span>-Rp <span x-text="formatNumber(voucher.discount)"></span></span>
 </div>
 </template>
 <template x-if="voucher.valid && voucher.type === 'shipping'">
 <div class="d-flex justify-content-between mb-1 text-success">
 <span>Potongan Ongkir (<span x-text="voucher.code"></span>)</span>
 <span>-Rp <span x-text="formatNumber(Math.min(voucher.discount, shippingCost))"></span></span>
 </div>
 </template>

 <hr>
 <div class="d-flex justify-content-between fw-bold fs-5">
 <span>Total Tagihan</span>
 <span style="color:var(--primary)">Rp <span x-text="formatNumber(finalTotal)"></span></span>
 </div>

 <button type="submit" class="btn btn-primary w-100 py-2 mt-3"
 style="border-radius:10px;font-weight:600" :disabled="!canSubmit">
 <i class="bi bi-check-circle me-2"></i>{{ __('order.place_order') }}
 </button>

 @if($errors->any())
 <div class="alert alert-danger mt-3 py-2 small" style="border-radius:10px">
 @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
 </div>
 @endif
 </div>
 </div>
 </div>
 </div>
 </form>
 </div>

 @auth
 <!-- Modal Pilih Alamat -->
 <div class="modal fade" id="selectAddressModal" tabindex="-1" aria-hidden="true" style="z-index: 1055;">
 <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
 <div class="modal-content" style="border-radius:16px; border:none;">
 <div class="modal-header border-bottom-0 pt-4 px-4 bg-light" style="border-radius:16px 16px 0 0;">
 <h6 class="modal-title fw-bold">Pilih Alamat Pengiriman</h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body p-0">
 <template x-if="addresses.length > 0">
 <div class="list-group list-group-flush">
 <template x-for="addr in addresses" :key="addr.id">
 <div class="list-group-item list-group-item-action border-bottom p-3" :class="selectedAddressId == addr.id ? 'bg-light border-primary border-start border-4' : ''" style="cursor:pointer;" @click="selectGlobalAddress(addr.id)">
 <div class="d-flex justify-content-between align-items-start">
 <div>
 <div class="fw-bold mb-1">
 <span class="text-dark" x-text="addr.recipient_name"></span>
 <template x-if="addr.title">
 <span class="badge bg-secondary ms-2 small" x-text="addr.title"></span>
 </template>
 <template x-if="addr.is_main">
 <span class="badge bg-primary ms-1 small">Utama</span>
 </template>
 </div>
 <div class="text-muted small mb-1" x-text="addr.phone"></div>
 <div class="text-muted small" x-text="addr.address_line"></div>
 <template x-if="addr.city || addr.province">
 <div class="text-muted small" x-text="addr.city + ', ' + addr.province + ' ' + (addr.postal_code || '')"></div>
 </template>
 </div>
 <div class="form-check ms-3">
 <input class="form-check-input fs-5 mt-1" type="radio" name="modal_address_id_dummy" :value="addr.id" :checked="selectedAddressId == addr.id" @change="selectGlobalAddress(addr.id)">
 </div>
 </div>
 </div>
 </template>
 </div>
 </template>
 <template x-if="addresses.length === 0">
 <div class="text-center py-5">
 <p class="text-muted small mb-3">Belum ada alamat pengiriman di akun Anda.</p>
 </div>
 </template>
 </div>
 <div class="modal-footer border-top-0 pb-4 px-4 pt-3 bg-light" style="border-radius:0 0 16px 16px;">
 <button type="button" class="btn btn-outline-primary w-100 py-2" style="border-radius:10px; border-style: dashed;" data-bs-toggle="modal" data-bs-target="#addAddressModal">
 <i class="bi bi-plus-circle me-1"></i> Tambah Alamat Baru
 </button>
 </div>
 </div>
 </div>
 </div>

 <!-- Modal Add Address -->
 <div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content" style="border-radius:16px; border:none;">
 <form id="addAddressForm" method="POST" action="{{ route('account.addresses.store') }}" @submit.prevent="submitNewAddress($event)">
 @csrf
 <div class="modal-header border-bottom-0 pt-4 px-4">
 <h6 class="modal-title fw-bold">Tambah Alamat Baru</h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body px-4">
 <div class="row g-3" x-data="locationSearch('', '', '', '', '')">
 <div class="col-12">
 <label class="form-label small fw-medium">Label (Rumah, Kantor, dll)</label>
 <input type="text" name="title" class="form-control" style="border-radius:10px">
 </div>
 <div>
 <label class="form-label small fw-medium">Nama Penerima *</label>
 <input type="text" name="recipient_name" class="form-control" required
 style="border-radius:10px" value="{{ auth()->user()->name }}">
 </div>
 <div>
 <label class="form-label small fw-medium">No. Telepon *</label>
 <input type="text" name="phone" class="form-control" required style="border-radius:10px" value="{{ auth()->user()->phone }}">
 </div>
 <div class="col-12">
 <label class="form-label small fw-medium">Kecamatan/Kota (RajaOngkir) *</label>
 <div class="position-relative">
 <input type="text" x-model="search" @input.debounce.500ms="fetchLocations" @focus="showDropdown = true" @click.away="showDropdown = false" class="form-control" placeholder="Ketik minimal 3 huruf..." required style="border-radius:10px">
 
 <div x-show="loading" class="position-absolute end-0 top-50 translate-middle-y me-3">
 <span class="spinner-border spinner-border-sm text-primary"></span>
 </div>

 <div x-show="showDropdown && results.length > 0" class="position-absolute w-100 bg-white border mt-1 shadow-sm" style="max-height: 200px; overflow-y: auto; border-radius: 10px; z-index: 1050; display: none;" x-transition>
 <template x-for="item in results" :key="item.id">
 <div @click="selectLocation(item)" class="p-2 border-bottom" style="cursor: pointer; font-size: 0.85rem;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
 <i class="bi bi-geo-alt text-muted me-2"></i><span x-text="item.label || item.name || (item.city_name + ', ' + item.province)"></span>
 </div>
 </template>
 </div>
 </div>
 
 <input type="hidden" name="province" x-model="province">
 <input type="hidden" name="province_id" x-model="province_id">
 <input type="hidden" name="city" x-model="city">
 <input type="hidden" name="city_id" x-model="city_id">
 </div>
 <div>
 <label class="form-label small fw-medium">Kode Pos</label>
 <input type="text" name="postal_code" x-model="postal_code" class="form-control" style="border-radius:10px">
 </div>
 <div class="col-12">
 <label class="form-label small fw-medium">Alamat Lengkap *</label>
 <textarea name="address_line" class="form-control" rows="3" required
 style="border-radius:10px"></textarea>
 </div>
 </div>
 </div>
 <div class="modal-footer border-top-0 pb-4 px-4">
 <button type="button" class="btn btn-light" data-bs-dismiss="modal"
 style="border-radius:10px">Batal</button>
 <button type="submit" class="btn btn-primary" style="border-radius:10px" :disabled="savingAddress">
 <span x-show="!savingAddress">Simpan Alamat</span>
 <span x-show="savingAddress"><i class="spinner-border spinner-border-sm"></i> Menyimpan...</span>
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
 @endauth
</div>
@endsection

@push('scripts')
 <script>
 function checkoutForm() {
 @php
 $initialAddressId = '';
 @endphp
 return {
 subtotal: {{ $cart->total }},
 totalWeight: {{ max(1, $cart->total_weight ?? 1) }},
 isDigitalOnly: {{ $cart->is_digital_only ? 'true' : 'false' }},
 addresses: @json($addresses ?? []),
 selectedAddressId: '',
 selectedAddress: null,
 
 fetchingShipping: false,
 shippingOptions: [],
 selectedCourierIndex: null,
 shippingCost: 0,
 shippingError: '',
 
 guestCityId: null,
 setGuestCity(cityId) {
 this.guestCityId = cityId;
 this.fetchShippingCosts();
 },
 
 voucherInput: '',
 savingAddress: false,
 voucherLoading: false,
 voucher: { valid: false, code: '', discount: 0, message: '', type: 'product' },
 
 get finalTotal() {
 const productDiscount = this.voucher.valid && this.voucher.type !== 'shipping' ? this.voucher.discount : 0;
 const shippingDiscount = this.voucher.valid && this.voucher.type === 'shipping' ? this.voucher.discount : 0;
 const effectiveShipping = Math.max(0, this.shippingCost - shippingDiscount);
 return Math.max(0, this.subtotal - productDiscount + effectiveShipping);
 },
 get canSubmit() {
 if (this.isDigitalOnly) return true;
 // For guests, we don't have this.addresses or selectedAddressId populated in modal 
 // but wait, is auth logic required for addresses? Guest uses different rule.
 // If no address selected but they are guest? Guests aren't bound to `selectedAddressId`, they type it out.
 // Oh! The blade `@ auth` wrappers are there. Let's make it safe:
 const isGuest = this.addresses.length === 0 && !document.querySelector('input[name="address_id"]');
 if(isGuest) return true;

 if (!this.selectedAddressId) return false;
 if (this.fetchingShipping) return false;
 if (this.selectedCourierIndex === null && this.shippingOptions.length > 0) return false;
 return true;
 },
 formatNumber(n) {
 return new Intl.NumberFormat('id-ID').format(n);
 },
 getCourierLogo(code) {
 if (!code) return '/assets/images/shipping/toko.png';
 const c = code.toLowerCase();
 if (c.includes('jne')) return '/assets/images/shipping/jne.png';
 if (c.includes('jnt') || c.includes('j&t')) return '/assets/images/shipping/jnt.png';
 if (c.includes('pos')) return '/assets/images/shipping/pos.png';
 if (c.includes('tiki')) return '/assets/images/shipping/tiki.png';
 if (c.includes('sicepat')) return '/assets/images/shipping/sicepat.png';
 if (c.includes('wahana')) return '/assets/images/shipping/wahana.png';
 if (c.includes('ninja')) return '/assets/images/shipping/ninja.png';
 if (c.includes('lion')) return '/assets/images/shipping/lion.png';
 if (c.includes('anteraja')) return '/assets/images/shipping/anteraja.png';
 if (c.includes('sap')) return '/assets/images/shipping/sap.png';
 if (c.includes('ide')) return '/assets/images/shipping/ide.png';
 if (c.includes('jet')) return '/assets/images/shipping/jet.png';
 if (c.includes('indah')) return '/assets/images/shipping/indah.png';
 if (c.includes('cod')) return '/assets/images/shipping/cod.png';
 return '/assets/images/shipping/toko.png';
 },
 
 selectGlobalAddress(id) {
 this.selectedAddressId = id;
 this.selectedAddress = this.addresses.find(a => a.id == id);
 
 // close modal if open
 const modalEl = document.getElementById('selectAddressModal');
 let modalInstance = bootstrap.Modal.getInstance(modalEl);
 if(modalInstance) modalInstance.hide();
 
 if (!this.isDigitalOnly) {
 this.fetchShippingCosts();
 }
 },
 
 selectShippingOption(index) {
 this.selectedCourierIndex = index;
 this.shippingCost = this.shippingOptions[index].cost;
 if (this.voucher.valid && this.voucher.type === 'shipping') {
 this.applyVoucher(true);
 }
 },

 async fetchShippingCosts() {
 let cityIdToUse = null;
 if (this.selectedAddress && this.selectedAddress.city_id) {
 cityIdToUse = this.selectedAddress.city_id;
 } else if (this.guestCityId) {
 cityIdToUse = this.guestCityId;
 }

 if (!cityIdToUse) return;
 
 this.fetchingShipping = true;
 this.shippingOptions = [];
 this.selectedCourierIndex = null;
 this.shippingCost = 0;
 this.shippingError = '';
 
 try {
 let formData = new FormData();
 formData.append('destination_city_id', cityIdToUse);
 formData.append('weight', this.totalWeight);
 
 const res = await fetch('/api/shipping/cost', {
 method: 'POST',
 headers: { 
 'Accept': 'application/json',
 'X-CSRF-TOKEN': '{{ csrf_token() }}'
 },
 body: formData
 });
 const data = await res.json();
 
 if (data && data.error && data.message) {
 // API returned an error (e.g. invalid API key)
 this.shippingError = data.message;
 console.error('Shipping API error:', data.message);
 } else if (data && data.data && data.data.length > 0) {
 this.shippingOptions = data.data;
 // Optionally auto-select the cheapest
 this.selectShippingOption(0);
 }
 } catch (e) {
 console.error('Cannot fetch shipping costs:', e);
 this.shippingError = 'Gagal memuat ongkos kirim. Pastikan koneksi internet Anda stabil.';
 }
 this.fetchingShipping = false;
 },

 async applyVoucher(silent = false) {
 const code = (this.voucher.valid ? this.voucher.code : this.voucherInput).trim();
 if (!code) return;
 if (!silent) this.voucherLoading = true;
 try {
 const res = await fetch('{{ route("checkout.applyVoucher") }}', {
 method: 'POST',
 headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
 body: JSON.stringify({
 code: code,
 subtotal: this.subtotal,
 shipping_cost: this.shippingCost,
 })
 });
 const data = await res.json();
 this.voucher = {
 valid: !!data.valid,
 code: data.valid ? code.toUpperCase() : '',
 discount: data.discount || 0,
 type: data.discount_type || 'product',
 message: data.message || ''
 };
 } catch (e) {
 this.voucher = { valid: false, code: '', discount: 0, type: 'product', message: 'Gagal memvalidasi voucher' };
 }
 this.voucherLoading = false;
 },
 removeVoucher() {
 this.voucher = { valid: false, code: '', discount: 0, type: 'product', message: '' };
 this.voucherInput = '';
 },
 submitNewAddress(e) {
 this.savingAddress = true;
 let form = e.target;
 let formData = new FormData(form);

 fetch(form.action, {
 method: 'POST',
 body: formData,
 headers: {
 'X-Requested-With': 'XMLHttpRequest',
 'Accept': 'application/json'
 }
 })
 .then(response => response.json())
 .then(data => {
 this.savingAddress = false;
 if (data.success && data.address) {
 // Add new address to the list and select it
 this.addresses.unshift(data.address);
 this.selectGlobalAddress(data.address.id);
 
 // Close modal
 const modalEl = document.getElementById('addAddressModal');
 let modalInstance = bootstrap.Modal.getInstance(modalEl);
 if(modalInstance) modalInstance.hide();
 form.reset();
 
 // Try to reset alpine bindings by dispatching event if necessary
 // Show toast
 Swal.fire({
 icon: 'success',
 title: 'Berhasil',
 text: data.message,
 timer: 2000,
 showConfirmButton: false
 });
 } else {
 throw new Error(data.message || 'Error saving address');
 }
 })
 .catch(error => {
 this.savingAddress = false;
 Swal.fire({
 icon: 'error',
 title: 'Error',
 text: 'Gagal menambahkan alamat: ' + error.message,
 });
 });
 }
 };
 }

 document.addEventListener('alpine:init', () => {
 Alpine.data('locationSearch', (initProvince, initProvId, initCity, initCityId, initPostalCode) => ({
 search: initCity ? (initCity + ', ' + initProvince) : '',
 province: initProvince || '',
 province_id: initProvId || '',
 city: initCity || '',
 city_id: initCityId || '',
 postal_code: initPostalCode || '',
 
 loading: false,
 results: [],
 showDropdown: false,

 async fetchLocations() {
 if (this.search.length < 3) {
 this.results = [];
 return;
 }
 
 this.loading = true;
 
 try {
 const response = await fetch(`{{ route('shipping.destinations') }}?search=${encodeURIComponent(this.search)}`);
 const data = await response.json();
 
 if (data && data.data) {
 this.results = data.data;
 this.showDropdown = true;
 } else {
 this.results = [];
 }
 } catch (error) {
 console.error('Error fetching locations:', error);
 this.results = [];
 } finally {
 this.loading = false;
 }
 },

 selectLocation(item) {
 if (item.id && item.city_name && item.province_name) {
 this.city_id = item.id; 
 this.province_id = item.province_id || '';
 
 this.city = item.label || (item.district_name ? (item.district_name + ', ' + item.city_name) : item.city_name);
 
 this.province = item.province_name;
 this.postal_code = item.zip_code || this.postal_code;
 this.search = item.label || (this.city + ', ' + this.province);
 } 
 else {
 this.city_id = item.city_id || item.id || '';
 this.province_id = item.province_id || '';
 
 this.city = item.label || item.city_name || item.name || '';
 
 this.province = item.province || '';
 this.postal_code = item.postal_code || this.postal_code;
 this.search = item.label || (this.city + ', ' + this.province);
 }
 
 this.showDropdown = false;
 this.results = [];
 
 this.$dispatch('set-guest-city', this.city_id);
 }
 }));
 });

 // Handle multiple gateway provider radios
 document.querySelectorAll('.gateway-radio').forEach(function(radio) {
 radio.addEventListener('change', function() {
 var providerInput = document.getElementById('gateway-provider-input');
 if (providerInput) {
 providerInput.value = this.dataset.provider || '';
 }
 });
 });

 // Clear gateway_provider when non-gateway method selected
 document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
 radio.addEventListener('change', function() {
 if (this.value !== 'gateway') {
 var providerInput = document.getElementById('gateway-provider-input');
 if (providerInput) providerInput.value = '';
 }
 });
 });
 </script>
@endpush