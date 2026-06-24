@extends('admin.layouts.app')
@section('title', __('admin.menu.settings'))
@section('page-title', __('admin.menu.settings'))

@section('content')
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')

        <style>
            .settings-tabs-wrapper {
                background: #f8fafc;
                padding: 6px;
                border-radius: 16px;
                border: 1px solid #e2e8f0;
                display: inline-flex;
                flex-wrap: wrap;
                gap: 5px;
            }
            .settings-tabs-wrapper .nav-link {
                color: #64748b;
                border-radius: 12px;
                font-weight: 600;
                padding: 10px 20px;
                transition: all 0.3s ease;
                border: none;
                display: flex;
                align-items: center;
                background: transparent;
            }
            .settings-tabs-wrapper .nav-link:hover:not(.active) {
                background: rgba(0,0,0,0.03);
                color: #0f172a;
            }
            .settings-tabs-wrapper .nav-link.active {
                background: #fff;
                color: var(--bs-primary, #0d6efd);
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            }
        </style>

        <div class="mb-4 text-center">
            <ul class="nav nav-pills settings-tabs-wrapper justify-content-center" id="settings-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="umum-tab" data-bs-toggle="pill"
                        data-bs-target="#umum" type="button" role="tab" aria-controls="umum" aria-selected="true">
                        <i class="bi bi-shop me-2"></i>Umum
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="toko-tab" data-bs-toggle="pill"
                        data-bs-target="#toko" type="button" role="tab" aria-controls="toko" aria-selected="false">
                        <i class="bi bi-gear me-2"></i>Toko & DK-DevStore
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="transaksi-tab" data-bs-toggle="pill"
                        data-bs-target="#transaksi" type="button" role="tab" aria-controls="transaksi" aria-selected="false">
                        <i class="bi bi-truck me-2"></i>Pengiriman & Pembayaran
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="ekstra-tab" data-bs-toggle="pill"
                        data-bs-target="#ekstra" type="button" role="tab" aria-controls="ekstra" aria-selected="false">
                        <i class="bi bi-plug me-2"></i>API & Ekstra
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content" id="settings-tabContent">
            <!-- TAB: UMUM -->
            <div class="tab-pane fade show active" id="umum" role="tabpanel">
                <div class="row g-4">
                    <!-- General -->
                    <div class="col-lg-6">
                <div class="card" style="border:none;border-radius:12px">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-shop me-2"></i>Umum</h6>
                        @foreach(['store_name' => 'Nama Toko', 'store_phone' => 'Telepon', 'store_email' => 'Email', 'store_address' => 'Alamat', 'store_description' => 'Deskripsi', 'phone_country_code' => 'Kode Negara (Telepon)'] as $key => $label)
                            <div class="mb-3">
                                <label class="form-label small fw-medium">{{ $label }}</label>
                                @if($key === 'phone_country_code')
                                    <input type="text" name="{{ $key }}" class="form-control"
                                        value="{{ $settings['general'][$key] ?? '62' }}" placeholder="62">
                                    <small class="text-muted">Gunakan angka saja (contoh: 62 untuk Indonesia). Berfungsi menormalisasi nomor yang berawal dari 0.</small>
                                @else
                                    <input type="text" name="{{ $key }}" class="form-control"
                                        value="{{ $settings['general'][$key] ?? '' }}">
                                @endif
                                <input type="hidden" name="_group[{{ $key }}]" value="general">
                                <input type="hidden" name="_type[{{ $key }}]" value="string">
                            </div>
                        @endforeach

                        @php
                            $palette = app(\App\Services\SettingService::class)->curatedColorPalette();
                            $selectedPrimaryPalette = $settings['general']['theme_primary_palette'] ?? 'indigo';
                            $selectedSecondaryPalette = $settings['general']['theme_secondary_palette'] ?? 'purple';
                        @endphp

                        <div class="border rounded-4 p-3 mb-3" style="background:#fafcff;border-color:#e8edf5!important">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-palette2 me-2"></i>Warna Template</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium">Template Primary Color</label>
                                    <select name="theme_primary_palette" class="form-select">
                                        @foreach($palette as $slug => $item)
                                            <option value="{{ $slug }}" {{ $selectedPrimaryPalette === $slug ? 'selected' : '' }}>
                                                {{ $item['name'] }} ({{ $item['hex'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="_group[theme_primary_palette]" value="general">
                                    <input type="hidden" name="_type[theme_primary_palette]" value="string">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-medium">Template Secondary Color</label>
                                    <select name="theme_secondary_palette" class="form-select">
                                        @foreach($palette as $slug => $item)
                                            <option value="{{ $slug }}" {{ $selectedSecondaryPalette === $slug ? 'selected' : '' }}>
                                                {{ $item['name'] }} ({{ $item['hex'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="_group[theme_secondary_palette]" value="general">
                                    <input type="hidden" name="_type[theme_secondary_palette]" value="string">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @foreach($palette as $slug => $item)
                                            <div class="d-flex align-items-center gap-2 px-2 py-1 rounded-pill border bg-white" style="font-size:.72rem">
                                                <span style="width:16px;height:16px;border-radius:999px;display:inline-block;background:{{ $item['hex'] }}"></span>
                                                <span>{{ $item['name'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <small class="text-muted d-block mt-2">Warna dipilih dari palette elegan/modern agar template tetap konsisten dan nyaman dilihat.</small>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Logo Toko (Opsional)</label>
                                @if(app(\App\Services\SettingService::class)->get('store_logo'))
                                    <div class="mb-2">
                                        <img src="{{ asset(app(\App\Services\SettingService::class)->get('store_logo')) }}"
                                            alt="Logo" style="max-height: 40px; border-radius: 4px;">
                                    </div>
                                @endif
                                <input type="file" name="store_logo_file" class="form-control form-control-sm"
                                    accept="image/png,image/jpeg,image/svg+xml">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-medium">Favicon (Opsional)</label>
                                @if(app(\App\Services\SettingService::class)->get('store_favicon'))
                                    <div class="mb-2">
                                        <img src="{{ asset(app(\App\Services\SettingService::class)->get('store_favicon')) }}"
                                            alt="Favicon" style="max-height: 24px; border-radius: 4px;">
                                    </div>
                                @endif
                                <input type="file" name="store_favicon_file" class="form-control form-control-sm"
                                    accept="image/png,image/x-icon">
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Kontak Ekstra & Jam Operasional -->
            <div class="col-lg-6">
                <div class="card" style="border:none;border-radius:12px">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-clock me-2"></i>Kontak Ekstra & Jam Operasional</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Jam Operasional (Contoh: Senin - Sabtu (08:00 - 17:00 WIB))</label>
                            <input type="text" name="operational_hours" class="form-control"
                                value="{{ $settings['general']['operational_hours'] ?? 'Senin - Sabtu (08:00 - 17:00 WIB)' }}">
                            <input type="hidden" name="_group[operational_hours]" value="general">
                            <input type="hidden" name="_type[operational_hours]" value="string">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-medium">Instagram URL</label>
                                <input type="url" name="social_instagram" class="form-control"
                                    placeholder="https://instagram.com/..."
                                    value="{{ $settings['general']['social_instagram'] ?? '' }}">
                                <input type="hidden" name="_group[social_instagram]" value="general">
                                <input type="hidden" name="_type[social_instagram]" value="string">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-medium">Facebook URL</label>
                                <input type="url" name="social_facebook" class="form-control"
                                    placeholder="https://facebook.com/..."
                                    value="{{ $settings['general']['social_facebook'] ?? '' }}">
                                <input type="hidden" name="_group[social_facebook]" value="general">
                                <input type="hidden" name="_type[social_facebook]" value="string">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-medium">Nomor WhatsApp Ext</label>
                                <input type="text" name="social_whatsapp" class="form-control" placeholder="6281..."
                                    value="{{ $settings['general']['social_whatsapp'] ?? '' }}">
                                <input type="hidden" name="_group[social_whatsapp]" value="general">
                                <input type="hidden" name="_type[social_whatsapp]" value="string">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label small fw-medium">Email Ext</label>
                                <input type="email" name="social_email" class="form-control" placeholder="email@toko.com"
                                    value="{{ $settings['general']['social_email'] ?? '' }}">
                                <input type="hidden" name="_group[social_email]" value="general">
                                <input type="hidden" name="_type[social_email]" value="string">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div> <!-- Close row -->
        </div> <!-- Close tab-pane -->

        <!-- TAB: TOKO & MARKETPLACE -->
            <div class="tab-pane fade" id="toko" role="tabpanel">
                <div class="row g-4">
                    <!-- Store Settings -->
                    <div class="col-lg-6">
                        <div class="card" style="border:none;border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="bi bi-gear me-2"></i>Pengaturan Toko</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Batas Waktu Bayar (jam)</label>
                                    <input type="number" name="order_expiry_hours" class="form-control"
                                        value="{{ $settings['store']['order_expiry_hours'] ?? 24 }}">
                                    <input type="hidden" name="_group[order_expiry_hours]" value="store">
                                    <input type="hidden" name="_type[order_expiry_hours]" value="int">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Alert Stok Minimum</label>
                                    <input type="number" name="min_stock_alert" class="form-control"
                                        value="{{ $settings['store']['min_stock_alert'] ?? 5 }}">
                                    <input type="hidden" name="_group[min_stock_alert]" value="store">
                                    <input type="hidden" name="_type[min_stock_alert]" value="int">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Metode Login</label>
                                    <select name="login_method" class="form-select">
                                        <option value="password" {{ ($settings['store']['login_method'] ?? '') === 'password' ? 'selected' : '' }}>Password</option>
                                        <option value="otp" {{ ($settings['store']['login_method'] ?? '') === 'otp' ? 'selected' : '' }}>OTP</option>
                                        <option value="both" {{ ($settings['store']['login_method'] ?? '') === 'both' ? 'selected' : '' }}>Keduanya</option>
                                    </select>
                                    <input type="hidden" name="_group[login_method]" value="store">
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input type="hidden" name="guest_checkout" value="false">
                                    <input type="checkbox" name="guest_checkout" value="true" class="form-check-input" {{ ($settings['store']['guest_checkout'] ?? 'true') === 'true' ? 'checked' : '' }}>
                                    <label class="form-check-label">Guest Checkout</label>
                                    <input type="hidden" name="_group[guest_checkout]" value="store">
                                    <input type="hidden" name="_type[guest_checkout]" value="bool">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- DK-DevStore Configuration -->
                    <div class="col-lg-6">
                        <div class="card" style="border:none;border-radius:12px">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3"><i class="bi bi-box-seam me-2"></i>DK-DevStore</h6>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">DK-DevStore API URL</label>
                            <input type="url" name="devstore_url" class="form-control"
                                value="{{ $settings['general']['devstore_url'] ?? 'https://devstore.dapurkode.com' }}">
                            <input type="hidden" name="_group[devstore_url]" value="general">
                            <input type="hidden" name="_type[devstore_url]" value="string">
                            <small class="text-muted">Biarkan default kecuali Anda menggunakan custom DK-DevStore API.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">License Key</label>
                            <input type="password" name="devstore_license_key" class="form-control"
                                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['general']['devstore_license_key'] ?? '') }}">
                            <input type="hidden" name="_group[devstore_license_key]" value="general">
                            <input type="hidden" name="_type[devstore_license_key]" value="string">
                            <small class="text-muted">Diperlukan untuk install modul atau tema berbayar langsung dari DK-DevStore.</small>
                        </div>
                    </div>
                </div>

            </div>
            </div> <!-- Close row -->
        </div> <!-- Close tab-pane -->

        <!-- TAB: PENGIRIMAN & PEMBAYARAN -->
            <div class="tab-pane fade" id="transaksi" role="tabpanel">
                <div class="row g-4">
                    @php
                        $activeProvider = $settings['shipping']['active_courier_provider'] ?? 'rajaongkir';
                        $kiriminAjaActive = \Nwidart\Modules\Facades\Module::has('KiriminAja') && \Nwidart\Modules\Facades\Module::isEnabled('KiriminAja');
                        // Tampilkan switcher hanya jika ada provider tambahan selain RajaOngkir (default).
                        $showProviderSwitcher = $kiriminAjaActive;
                    @endphp

                    @if($showProviderSwitcher)
                    <!-- Provider Switcher -->
                    <div class="col-12">
                        <div class="card" style="border:none;border-radius:12px;background:#f8f9ff">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div>
                                        <h6 class="fw-semibold mb-0"><i class="bi bi-arrow-repeat me-2"></i>Provider Pengiriman Aktif</h6>
                                        <small class="text-muted">Pilih layanan kurir yang dipakai untuk hitung ongkir di checkout.</small>
                                    </div>
                                    <div class="ms-auto" style="min-width:240px">
                                        <select name="active_courier_provider" class="form-select form-select-sm">
                                            <option value="rajaongkir" {{ $activeProvider === 'rajaongkir' ? 'selected' : '' }}>RajaOngkir (Komerce)</option>
                                            @if($kiriminAjaActive)
                                                <option value="kiriminaja" {{ $activeProvider === 'kiriminaja' ? 'selected' : '' }}>KiriminAja</option>
                                            @endif
                                        </select>
                                        <input type="hidden" name="_group[active_courier_provider]" value="shipping">
                                        <input type="hidden" name="_type[active_courier_provider]" value="string">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                        {{-- Pastikan setting tetap ter-submit walau switcher disembunyikan --}}
                        <input type="hidden" name="active_courier_provider" value="rajaongkir">
                        <input type="hidden" name="_group[active_courier_provider]" value="shipping">
                        <input type="hidden" name="_type[active_courier_provider]" value="string">
                    @endif

                    <!-- Shipping / RajaOngkir -->
                    <div class="col-lg-6">
                        <div class="card" style="border:none;border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="bi bi-truck me-2"></i>Pengiriman (RajaOngkir)</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Tipe Akun RajaOngkir <span class="text-danger">*</span></label>
                                    <select name="rajaongkir_type" class="form-select">
                                        <option value="komerce" {{ ($settings['shipping']['rajaongkir_type'] ?? 'komerce') === 'komerce' ? 'selected' : '' }}>Komerce (rajaongkir.komerce.id)</option>
                                        <option value="starter" {{ ($settings['shipping']['rajaongkir_type'] ?? '') === 'starter' ? 'selected' : '' }}>Starter (api.rajaongkir.com/starter)</option>
                                        <option value="basic" {{ ($settings['shipping']['rajaongkir_type'] ?? '') === 'basic' ? 'selected' : '' }}>Basic (api.rajaongkir.com/basic)</option>
                                        <option value="pro" {{ ($settings['shipping']['rajaongkir_type'] ?? '') === 'pro' ? 'selected' : '' }}>Pro (pro.rajaongkir.com/api)</option>
                                    </select>
                                    <input type="hidden" name="_group[rajaongkir_type]" value="shipping">
                                    <input type="hidden" name="_type[rajaongkir_type]" value="string">
                                    <small class="text-muted d-block mt-1">Pilih layanan API RajaOngkir yang Anda gunakan. <strong class="text-danger">Penting: Jika tipe diubah, Anda wajib memilih ulang Kota Asal (Origin).</strong></small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">API Key RajaOngkir <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="rajaongkir_api_key" class="form-control"
                                        value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['shipping']['rajaongkir_api_key'] ?? '') }}"
                                        placeholder="Masukkan API key RajaOngkir">
                                    <input type="hidden" name="_group[rajaongkir_api_key]" value="shipping">
                                    <input type="hidden" name="_type[rajaongkir_api_key]" value="string">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Kota Asal (Origin)</label>
                                    <select name="origin_city_id" class="form-select select2-destination" data-placeholder="Ketik nama kota/kecamatan...">
                                        @if(!empty($settings['shipping']['origin_city_id']) && !empty($settings['shipping']['origin_city_name']))
                                            <option value="{{ $settings['shipping']['origin_city_id'] }}" selected="selected">
                                                {{ $settings['shipping']['origin_city_name'] }}
                                            </option>
                                        @elseif(!empty($settings['shipping']['origin_city_id']))
                                            <option value="{{ $settings['shipping']['origin_city_id'] }}" selected="selected">
                                                ID: {{ $settings['shipping']['origin_city_id'] }} (Ketik untuk mengubah)
                                            </option>
                                        @endif
                                    </select>
                                    <input type="hidden" name="origin_city_name" id="origin_city_name" value="{{ $settings['shipping']['origin_city_name'] ?? '' }}">
                                    <input type="hidden" name="_group[origin_city_id]" value="shipping">
                                    <input type="hidden" name="_type[origin_city_id]" value="string">
                                    <input type="hidden" name="_group[origin_city_name]" value="shipping">
                                    <input type="hidden" name="_type[origin_city_name]" value="string">
                                    <small class="text-muted">Pilih kota asal pengiriman Anda untuk penghitungan ongkos kirim.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Kurir Aktif</label>
                                    @php
                                        $enabledCouriers = json_decode($settings['shipping']['enabled_couriers'] ?? '["jne","pos","tiki"]', true) ?: [];
                                    @endphp
                                    @foreach(['jne' => 'JNE', 'pos' => 'POS Indonesia', 'tiki' => 'TIKI', 'rpx' => 'RPX', 'jnt' => 'J&T', 'sicepat' => 'SiCepat', 'anteraja' => 'AnterAja'] as $code => $name)
                                        <div class="form-check">
                                            <input type="checkbox" name="enabled_couriers_list[]" value="{{ $code }}"
                                                class="form-check-input" {{ in_array($code, $enabledCouriers) ? 'checked' : '' }}>
                                            <label class="form-check-label small">{{ $name }}</label>
                                        </div>
                                    @endforeach
                                    <input type="hidden" name="_group[enabled_couriers]" value="shipping">
                                    <input type="hidden" name="_type[enabled_couriers]" value="json">
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($kiriminAjaActive)
                    <!-- Shipping / KiriminAja -->
                    <div class="col-lg-6">
                        <div class="card" style="border:none;border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="bi bi-box-seam me-2"></i>Pengiriman (KiriminAja Mitra)</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">API Key KiriminAja <span class="text-danger">*</span></label>
                                    <input type="text" name="kiriminaja_api_key" class="form-control"
                                        value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['shipping']['kiriminaja_api_key'] ?? '') }}"
                                        placeholder="Masukkan API Key dari Dashboard KiriminAja">
                                    <input type="hidden" name="_group[kiriminaja_api_key]" value="shipping">
                                    <input type="hidden" name="_type[kiriminaja_api_key]" value="string">
                                    <small class="text-muted">Dapatkan di <a href="https://developer.kiriminaja.com/integration" target="_blank">developer.kiriminaja.com/integration</a></small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Mode</label>
                                    @php $kaMode = $settings['shipping']['kiriminaja_mode'] ?? 'staging'; @endphp
                                    <select name="kiriminaja_mode" class="form-select">
                                        <option value="staging" {{ $kaMode === 'staging' ? 'selected' : '' }}>Sandbox / Staging (tdev.kiriminaja.com)</option>
                                        <option value="production" {{ $kaMode === 'production' ? 'selected' : '' }}>Production (client.kiriminaja.com)</option>
                                    </select>
                                    <input type="hidden" name="_group[kiriminaja_mode]" value="shipping">
                                    <input type="hidden" name="_type[kiriminaja_mode]" value="string">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Kecamatan Asal (Origin)</label>
                                    <select name="kiriminaja_origin_id" class="form-select select2-kiriminaja-destination" data-placeholder="Ketik kecamatan/kota...">
                                        @if(!empty($settings['shipping']['kiriminaja_origin_id']) && !empty($settings['shipping']['kiriminaja_origin_name']))
                                            <option value="{{ $settings['shipping']['kiriminaja_origin_id'] }}" selected>
                                                {{ $settings['shipping']['kiriminaja_origin_name'] }}
                                            </option>
                                        @elseif(!empty($settings['shipping']['kiriminaja_origin_id']))
                                            <option value="{{ $settings['shipping']['kiriminaja_origin_id'] }}" selected>
                                                ID: {{ $settings['shipping']['kiriminaja_origin_id'] }} (Ketik untuk mengubah)
                                            </option>
                                        @endif
                                    </select>
                                    <input type="hidden" name="kiriminaja_origin_name" id="kiriminaja_origin_name" value="{{ $settings['shipping']['kiriminaja_origin_name'] ?? '' }}">
                                    <input type="hidden" name="_group[kiriminaja_origin_id]" value="shipping">
                                    <input type="hidden" name="_type[kiriminaja_origin_id]" value="string">
                                    <input type="hidden" name="_group[kiriminaja_origin_name]" value="shipping">
                                    <input type="hidden" name="_type[kiriminaja_origin_name]" value="string">
                                    <small class="text-muted">Cari berdasarkan nama kecamatan, kota, atau kode pos.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Kurir Aktif</label>
                                    @php
                                        $kaCouriers = json_decode($settings['shipping']['kiriminaja_couriers'] ?? '["jne","jnt","sicepat","anteraja","ide"]', true) ?: [];
                                    @endphp
                                    @foreach(['jne' => 'JNE', 'jnt' => 'J&T', 'sicepat' => 'SiCepat', 'anteraja' => 'AnterAja', 'ide' => 'ID Express', 'sap' => 'SAP', 'pos' => 'POS Indonesia', 'ninja' => 'Ninja Xpress', 'wahana' => 'Wahana'] as $code => $name)
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" name="kiriminaja_couriers_list[]" value="{{ $code }}"
                                                class="form-check-input" {{ in_array($code, $kaCouriers) ? 'checked' : '' }}>
                                            <label class="form-check-label small">{{ $name }}</label>
                                        </div>
                                    @endforeach
                                    <input type="hidden" name="_group[kiriminaja_couriers]" value="shipping">
                                    <input type="hidden" name="_type[kiriminaja_couriers]" value="json">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-medium">Callback URL (opsional)</label>
                                    <input type="url" name="kiriminaja_callback_url" class="form-control form-control-sm"
                                        value="{{ $settings['shipping']['kiriminaja_callback_url'] ?? url('webhook/kiriminaja') }}"
                                        placeholder="https://domain-anda.com/webhook/kiriminaja">
                                    <input type="hidden" name="_group[kiriminaja_callback_url]" value="shipping">
                                    <input type="hidden" name="_type[kiriminaja_callback_url]" value="string">
                                    <small class="text-muted">Daftarkan URL ini di dashboard KiriminAja untuk update status pengiriman otomatis.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Payment -->
                    <div class="col-lg-6">
                        <div class="card" style="border:none;border-radius:12px">
                            <div class="card-body">
                                <h6 class="fw-semibold mb-3"><i class="bi bi-credit-card me-2"></i>Pembayaran</h6>
                                <div class="mb-3">
                                    <label class="form-label small fw-medium">Metode Pembayaran Aktif</label>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" name="payment_method_manual"
                                            value="1" id="pmManual" {{ ($settings['payment']['payment_method_manual'] ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pmManual">Transfer Manual (Bank)</label>
                                        <input type="hidden" name="_group[payment_method_manual]" value="payment">
                                        <input type="hidden" name="_type[payment_method_manual]" value="string">
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" role="switch" name="payment_method_cod"
                                            value="1" id="pmCod" {{ ($settings['payment']['payment_method_cod'] ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pmCod">Cash on Delivery (COD)</label>
                                        <input type="hidden" name="_group[payment_method_cod]" value="payment">
                                        <input type="hidden" name="_type[payment_method_cod]" value="string">
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" name="payment_method_gateway"
                                            value="1" id="pmGateway" {{ ($settings['payment']['payment_method_gateway'] ?? '0') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pmGateway">Payment Gateway (Otomatis)</label>
                                        <input type="hidden" name="_group[payment_method_gateway]" value="payment">
                                        <input type="hidden" name="_type[payment_method_gateway]" value="string">
                                        <small class="d-block text-muted" style="font-size:0.7rem">Membutuhkan modul Payment Gateway yang aktif di JDO-Store</small>
                                    </div>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" name="payment_method_wallet"
                                            value="1" id="pmWallet" {{ ($settings['payment']['payment_method_wallet'] ?? '1') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pmWallet">Saldo / Wallet Pelanggan</label>
                                        <input type="hidden" name="_group[payment_method_wallet]" value="payment">
                                        <input type="hidden" name="_type[payment_method_wallet]" value="string">
                                        <small class="d-block text-muted" style="font-size:0.7rem">Pelanggan login dapat membayar memakai saldo wallet (langsung lunas).</small>
                                    </div>
                                </div>
                                <hr>
                                <div class="mb-3" x-data="bankAccountsComponent()">
                                    <label class="form-label small fw-medium">Rekening Bank (Untuk Transfer Manual)</label>
                                    <input type="hidden" name="bank_accounts" :value="JSON.stringify(accounts)">
                                    <input type="hidden" name="_group[bank_accounts]" value="payment">
                                    <input type="hidden" name="_type[bank_accounts]" value="json">

                                    <div class="table-responsive border rounded mb-2">
                                        <table class="table table-sm table-borderless mb-0">
                                            <thead class="bg-light text-muted" style="font-size:0.75rem">
                                                <tr>
                                                    <th>Bank</th>
                                                    <th>No. Rekening</th>
                                                    <th>Atas Nama</th>
                                                    <th style="width: 45px"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="(acc, index) in accounts" :key="index">
                                                    <tr class="align-middle border-top">
                                                        <td>
                                                            <input type="text" x-model="acc.bank" class="form-control form-control-sm" placeholder="Contoh: BCA">
                                                        </td>
                                                        <td>
                                                            <input type="text" x-model="acc.account_number" class="form-control form-control-sm" placeholder="123456789">
                                                        </td>
                                                        <td>
                                                            <input type="text" x-model="acc.account_name" class="form-control form-control-sm" placeholder="Nama Pemilik">
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" @click="removeAccount(index)" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                </template>
                                                <tr class="border-top" x-show="accounts.length === 0">
                                                    <td colspan="4" class="text-center text-muted small py-3">Belum ada data rekening</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <button type="button" @click="addAccount()" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus me-1"></i>Tambah Rekening</button>

                                    <!-- Alpine Init Script -->
                                    <script>
                                        document.addEventListener('alpine:init', () => {
                                            Alpine.data('bankAccountsComponent', () => ({
                                                accounts: [],
                                                init() {
                                                    try {
                                                        let data = {!! empty($settings['payment']['bank_accounts']) ? '[]' : $settings['payment']['bank_accounts'] !!};
                                                        this.accounts = Array.isArray(data) ? data : [];
                                                    } catch (e) {
                                                        this.accounts = [];
                                                    }
                                                },
                                                addAccount() {
                                                    this.accounts.push({ bank: '', account_number: '', account_name: '' });
                                                },
                                                removeAccount(index) {
                                                    this.accounts.splice(index, 1);
                                                }
                                            }));
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>

                <!-- Dynamic Payment Gateway Module Settings (hanya muncul saat modul aktif) -->
                @if(\Nwidart\Modules\Facades\Module::has('XenditPayment') && \Nwidart\Modules\Facades\Module::isEnabled('XenditPayment'))
                    <div class="col-lg-6">
                        @include('xenditpayment::admin.settings-partial')
                    </div>
                @endif

                @if(\Nwidart\Modules\Facades\Module::has('IpaymuPayment') && \Nwidart\Modules\Facades\Module::isEnabled('IpaymuPayment'))
                    <div class="col-lg-6">
                        @include('ipaymupayment::admin.settings-partial')
                    </div>
                @endif

                @if(\Nwidart\Modules\Facades\Module::has('MidtransPayment') && \Nwidart\Modules\Facades\Module::isEnabled('MidtransPayment'))
                    <div class="col-lg-6">
                        @include('midtranspayment::admin.settings-partial')
                    </div>
                @endif

                @if(\Nwidart\Modules\Facades\Module::has('DuitkuPayment') && \Nwidart\Modules\Facades\Module::isEnabled('DuitkuPayment'))
                    <div class="col-lg-6">
                        @include('duitkupayment::admin.settings-partial')
                    </div>
                @endif
                </div>
            </div>

            <!-- TAB: API & EKSTRA -->
            <div class="tab-pane fade" id="ekstra" role="tabpanel">
                <div class="row g-4">

            <!-- WhatsApp -->
                <div class="col-lg-6">
                    <div class="card" style="border:none;border-radius:12px">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-whatsapp me-2"></i>WhatsApp API Gateway</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">WAGW Domain</label>
                                <input type="text" name="wagw_domain" id="wagw_domain" class="form-control"
                                    value="{{ $demoMode ? 'hidden_in_demo_mode' : ($settings['whatsapp']['wagw_domain'] ?? '') }}"
                                    placeholder="http://127.0.0.1:3000" {{ $demoMode ? 'readonly' : '' }}>
                                <input type="hidden" name="_group[wagw_domain]" value="whatsapp">
                                <input type="hidden" name="_type[wagw_domain]" value="string">
                                <small class="text-muted">URL dari server custom WhatsApp Gateway (contoh: http://127.0.0.1:3000)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Nomor Session WAGW (Sender)</label>
                                <input type="text" name="wagw_nomer" id="wagw_nomer" class="form-control"
                                    value="{{ $demoMode ? 'hidden_in_demo_mode' : ($settings['whatsapp']['wagw_nomer'] ?? '') }}"
                                    placeholder="628xxx" {{ $demoMode ? 'readonly' : '' }}>
                                <input type="hidden" name="_group[wagw_nomer]" value="whatsapp">
                                <input type="hidden" name="_type[wagw_nomer]" value="string">
                                <small class="text-muted">Nomor WhatsApp yg digunakan untuk sender. Tulis dgn 628x.</small>
                            </div>
                            @if(!$demoMode)
                            <div class="mb-3">
                                @if(!empty($settings['whatsapp']['wagw_domain']) && !empty($settings['whatsapp']['wagw_nomer']))
                                    <div class="d-flex gap-2 mt-2">
                                        <button type="button" class="btn btn-outline-success btn-sm flex-fill" onclick="cekWA()">
                                            <i class="bi bi-qr-code-scan me-1"></i> Scan
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#testWAModal">
                                            <i class="bi bi-send me-1"></i> Tes Kirim
                                        </button>
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 mb-0 mt-2" style="font-size: 0.8rem">
                                        Simpan pengaturan WAGW Domain dan Nomor terlebih dahulu untuk memunculkan tombol Scan QR.
                                    </div>
                                @endif
                            </div>
                            @else
                            <div class="alert alert-info py-2 mb-0 mt-2" style="font-size: 0.8rem">
                                <i class="bi bi-info-circle me-1"></i> Pengaturan WhatsApp dinonaktifkan dalam Mode Demo.
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Email / SMTP Settings -->
                <div class="col-lg-6">
                    <div class="card" style="border:none;border-radius:12px">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-envelope me-2"></i>Email (SMTP) <span class="badge bg-secondary fs-7">Queue</span></h6>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-medium">SMTP Host</label>
                                    <input type="text" name="mail_host" class="form-control" value="{{ $settings['email']['mail_host'] ?? '' }}" placeholder="smtp.gmail.com">
                                    <input type="hidden" name="_group[mail_host]" value="email">
                                    <input type="hidden" name="_type[mail_host]" value="string">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-medium">SMTP Port</label>
                                    <input type="number" name="mail_port" class="form-control" value="{{ $settings['email']['mail_port'] ?? '587' }}" placeholder="587">
                                    <input type="hidden" name="_group[mail_port]" value="email">
                                    <input type="hidden" name="_type[mail_port]" value="string">
                                </div>
                                <div class="col-12 mb-2">
                                    <label class="form-label small fw-medium">SMTP Username (Email)</label>
                                    <input type="email" name="mail_username" class="form-control" value="{{ $settings['email']['mail_username'] ?? '' }}" placeholder="email@gmail.com">
                                    <input type="hidden" name="_group[mail_username]" value="email">
                                    <input type="hidden" name="_type[mail_username]" value="string">
                                </div>
                                <div class="col-12 mb-2">
                                    <label class="form-label small fw-medium">SMTP Password</label>
                                    <input type="password" name="mail_password" class="form-control" value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['email']['mail_password'] ?? '') }}" {{ config('app.demo_mode') ? 'readonly' : '' }}>
                                    <input type="hidden" name="_group[mail_password]" value="email">
                                    <input type="hidden" name="_type[mail_password]" value="string">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-medium">Enkripsi (TLS/SSL)</label>
                                    <input type="text" name="mail_encryption" class="form-control" value="{{ $settings['email']['mail_encryption'] ?? 'tls' }}" placeholder="tls">
                                    <input type="hidden" name="_group[mail_encryption]" value="email">
                                    <input type="hidden" name="_type[mail_encryption]" value="string">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small fw-medium">Pengirim (Dari)</label>
                                    <input type="text" name="mail_from_address" class="form-control" value="{{ $settings['email']['mail_from_address'] ?? '' }}" placeholder="no-reply@toko.com">
                                    <input type="hidden" name="_group[mail_from_address]" value="email">
                                    <input type="hidden" name="_type[mail_from_address]" value="string">
                                </div>
                                <div class="col-12 mt-3">
                                    <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Setelah menyimpan, sistem akan menggunakan SMTP ini bersama Queueing untuk Notifikasi.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Settings -->
                <div class="col-lg-12">
                    <div class="card" style="border:none;border-radius:12px">
                        <div class="card-body">
                            <h6 class="fw-semibold mb-3"><i class="bi bi-robot me-2"></i>Kecerdasan Buatan (AI)</h6>
                            <div class="alert alert-info border-0"
                                style="background-color: #e0f2fe; color: #0369a1; border-radius: 8px;">
                                <h6 class="fw-bold mb-1"><i class="bi bi-stars me-1"></i> Gemini AI Terintegrasi</h6>
                                <p class="mb-0 small">Aktifkan fitur cerdas seperti <strong>Auto-Generate Deskripsi
                                        Produk</strong> dan <strong>Smart Dashboard Insights</strong> dengan menghubungkan kunci
                                    API Gemini Anda secara gratis.</p>
                            </div>

                            <div class="row g-4 mt-1">
                                <div class="col-md-5">
                                    <div class="p-3 border rounded" style="background-color: #f8fafc;">
                                        <h6 class="small fw-bold mb-2">Cara Mendapatkan API Key:</h6>
                                        <ol class="small text-muted mb-0 ps-3">
                                            <li class="mb-1">Buka <a href="https://aistudio.google.com/app/apikey"
                                                    target="_blank" class="fw-semibold text-decoration-none">Google AI Studio <i
                                                        class="bi bi-box-arrow-up-right ms-1"
                                                        style="font-size: 0.7rem;"></i></a></li>
                                            <li class="mb-1">Login dengan akun Google Anda.</li>
                                            <li class="mb-1">Klik tombol biru <strong>"Create API key"</strong>.</li>
                                            <li>Salin kunci yang muncul dan tempelkan di kotak sebelah kanan.</li>
                                        </ol>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="mb-3">
                                        <label class="form-label small fw-medium">Google Gemini API Key</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="bi bi-key text-muted"></i></span>
                                            <input type="password" name="gemini_api_key" class="form-control"
                                                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['general']['gemini_api_key'] ?? '') }}"
                                                placeholder="AIzaSyAxxxxxxxxxx" style="border-left: none;">
                                        </div>
                                        <input type="hidden" name="_group[gemini_api_key]" value="general">
                                        <input type="hidden" name="_type[gemini_api_key]" value="string">
                                        <small class="text-muted mt-1 d-block">Kunci ini disimpan dengan aman dan hanya digunakan
                                            untuk fitur AI aplikasi Anda.</small>
                                    </div>
                                    <div class="mb-0">
                                        <label class="form-label small fw-medium">Model Gemini</label>
                                        <select name="gemini_model" class="form-select">
                                            <option value="gemini-3.1-pro" {{ ($settings['ai']['gemini_model'] ?? '') === 'gemini-3.1-pro' ? 'selected' : '' }}>Gemini 3.1 Pro (Terbaru & Tercerdas)
                                            </option>
                                            <option value="gemini-2.5-flash" {{ ($settings['ai']['gemini_model'] ?? '') === 'gemini-2.5-flash' ? 'selected' : '' }}>Gemini 2.5 Flash (Sangat Cepat - Versi
                                                2.5)</option>
                                            <option value="gemini-1.5-flash" {{ ($settings['ai']['gemini_model'] ?? 'gemini-1.5-flash') === 'gemini-1.5-flash' ? 'selected' : '' }}>Gemini 1.5 Flash
                                                (Standar Cepat)</option>
                                            <option value="gemini-1.5-pro" {{ ($settings['ai']['gemini_model'] ?? '') === 'gemini-1.5-pro' ? 'selected' : '' }}>Gemini 1.5 Pro (Cerdas)</option>
                                            <option value="gemini-1.0-pro" {{ ($settings['ai']['gemini_model'] ?? '') === 'gemini-1.0-pro' ? 'selected' : '' }}>Gemini 1.0 Pro (Stabil)</option>
                                            <option value="gemini-pro" {{ ($settings['ai']['gemini_model'] ?? '') === 'gemini-pro' ? 'selected' : '' }}>Gemini Pro (Classic)</option>
                                        </select>
                                        <input type="hidden" name="_group[gemini_model]" value="ai">
                                        <input type="hidden" name="_type[gemini_model]" value="string">
                                        <small class="text-muted mt-1 d-block">Pilih model yang sesuai dengan kebutuhan kuota dan
                                            akurasi Anda.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div> <!-- End row ekstra -->
            </div> <!-- End tab-pane ekstra -->
        </div> <!-- End tab-content -->

        @if(!$demoMode)
        <div class="mt-4">
            <button type="submit" class="btn btn-primary px-5" style="border-radius:10px">
                <i class="bi bi-check-lg me-1"></i> Simpan Pengaturan
            </button>
        </div>
        @endif
    </form>

    <!-- Test WA Modal -->
    <div class="modal fade" id="testWAModal" tabindex="-1" aria-labelledby="testWAModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="testWAModalLabel">Tes Kirim Pesan WhatsApp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Nomor Tujuan</label>
                        <input type="text" id="test_wa_phone" class="form-control" placeholder="628xxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Pesan</label>
                        <textarea id="test_wa_message" class="form-control" rows="3">Halo, ini adalah pesan tes dari sistem JadiOrder.</textarea>
                    </div>
                    <div id="test_wa_response" class="mt-3 d-none">
                        <label class="form-label small fw-bold">Server Response:</label>
                        <pre class="bg-light p-2 rounded border small" style="max-height: 200px; overflow-y: auto;"></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btnTestWA" onclick="runTestWA()">Kirim Tes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scan WA Modal -->
    <div class="modal fade" id="scanModal" tabindex="-1" aria-labelledby="scanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title" id="scanModalLabel"><i class="bi bi-whatsapp text-success me-2"></i>Status WhatsApp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body areascanqr">
                    <!-- Socket Content will be injected here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('.select2-destination').select2({
            theme: 'bootstrap-5',
            ajax: {
                url: "{{ url('api/shipping/destinations') }}",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term // search parameter
                    };
                },
                processResults: function(data) {
                    if (data && data.error) {
                        return { results: [{ id: '', text: data.message || 'Gagal memuat data / Cek API Key', disabled: true }] };
                    }
                    return {
                        results: $.map(data.data || [], function(item) {
                            return {
                                text: item.label || item.name || item.city_name || item.text || 'Unknown',
                                id: item.id || item.city_id
                            }
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });

        // Set name to hidden input on select
        $('.select2-destination').on('select2:select', function(e) {
            var data = e.params.data;
            $('#origin_city_name').val(data.text);
        });

        // KiriminAja destination search (kecamatan)
        $('.select2-kiriminaja-destination').select2({
            theme: 'bootstrap-5',
            ajax: {
                url: "{{ url('api/kiriminaja/destinations') }}",
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return { search: params.term };
                },
                processResults: function(data) {
                    if (data && data.error) {
                        return { results: [{ id: '', text: data.message || 'Gagal memuat data', disabled: true }] };
                    }
                    return {
                        results: $.map(data.data || [], function(item) {
                            return { id: item.id, text: item.label };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 3,
            placeholder: 'Ketik nama kecamatan/kota...'
        });

        $('.select2-kiriminaja-destination').on('select2:select', function(e) {
            $('#kiriminaja_origin_name').val(e.params.data.text);
        });
    });

    @if(!$demoMode && !empty($settings['whatsapp']['wagw_domain']) && !empty($settings['whatsapp']['wagw_nomer']))
    var socket = io("{{ $settings['whatsapp']['wagw_domain'] }}", {
        transports: ['polling', 'flashsocket']
    });

    function cekWA(){
        var scanModal = new bootstrap.Modal(document.getElementById('scanModal'));
        scanModal.show();
        var nomor = "{{ $settings['whatsapp']['wagw_nomer'] }}";
        
        $('.areascanqr').html(`
            <div class="p-3">
                <div class="info-detik-${nomor} mb-2"></div>
                <div id="cardimg-${nomor}" class="text-center">
                    <div class="spinner-border text-primary my-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div><br/>
                    <span class="text-muted">Menghubungkan ke Gateway...</span>
                </div>
                <p id="info-${nomor}" class="info-${nomor}"></p>
                <div class="log mt-3"></div>
                <div class="text-center pt-3 border-top mt-3">
                    <button class="btn btn-danger btn-sm px-4" onclick="logoutqr('${nomor}')">
                        <i class="bi bi-power"></i> Keluar (Logout)
                    </button>
                </div>
            </div>
        `);

        socket.emit('create-session', {
            id: nomor
        });
    }

    function logoutqr(nomor) {
        Swal.fire({
            text: "koneksi ke Whatsapp di Handphone akan di hapus, dan setelah dihapus silahkan logout juga (Linked Devices) di Handphone Anda",
            title: "Yakin memutuskan koneksi?",
            icon: "warning",
            showCancelButton: true,
            cancelButtonColor: "#ff646d",
            confirmButtonText: "Ya, Putuskan",
            cancelButtonText: "Batal"
        }).then((vals) => {
            if (vals.isConfirmed) {
                socket.emit('logout', {
                    id: nomor
                });
                
                $(`#cardimg-${nomor}`).html(`<span class="text-warning">Memutuskan koneksi...</span>`);
                setTimeout(() => {
                    cekWA(); // reload session
                }, 2000);
            }
        });
    }
    
    socket.on('message', function(msg) {
        $('.log').html(`<li class="small text-muted">` + msg.text + `</li>`);
    });

    socket.on('qr', function(src) {
        console.log("QR received for", src.id);
        $(`#cardimg-${src.id}`).html(`<img src="` + src.src + `" class="img-fluid rounded border p-2" alt="QR Code" style="height:250px; width:250px;">`);
        $(`.info-detik-${src.id}`).html(`<p class='text-center mb-0'>Waktu scan anda adalah <strong class="text-primary fs-5">13</strong> detik</p>`);
        var count = 0;
        var interval = setInterval(function() {
            count++;
            var counts = 13 - count;
            $(`.info-${src.id}`).html(`<p class='text-center mb-0'>Sisa waktu scan <strong class="text-danger fs-5">${counts}</strong> detik lagi</p>`);
            if (count >= 13) {
                $(`.info-${src.id}`).html("");
                $(`#cardimg-${src.id}`).html(`
                    <div class="alert alert-warning mb-0 text-center">
                        <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
                        Silakan tutup jendela ini, lalu klik tombol Scan lagi untuk scan ulang.
                    </div>
                `);
                clearInterval(interval);
            }
        }, 1000);
    });

    socket.on('authenticated', function(src) {
        const nomors = src.data.id;
        const nomor = nomors.replace(/\D/g, '');
        $(`#cardimg-${src.id}`).html(`
            <div class='text-center p-3 text-success bg-light rounded text-dark'>
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <div class='mb-2 mt-3 fw-bold fs-5'>TERHUBUNG</div>
                <div class='mb-1 small text-muted'>Nama: <span class="text-dark fw-medium">${src.data.name}</span></div>
                <div class='mb-1 small text-muted'>Nomor WA: <span class="text-dark fw-medium">${nomor}</span></div>
            </div>
        `);
        $(`.info-${src.id}`).html("");
        $(`.info-detik-${src.id}`).html("");
    });

    socket.on('isdelete', function(src) {
        $(`#cardimg-${src.id}`).html(`<div class="alert alert-danger">` + src.text + `</div>`);
    });

    socket.on('close', function(src) {
        console.log("Closed:", src);
        $(`#cardimg-${src.id}`).html(`<div class="alert alert-danger text-center mt-3"><i class="bi bi-x-circle d-block fs-3 mb-2"></i>` + src.text + `</div>`);
    });
    @endif

    function runTestWA() {
        const phone = $('#test_wa_phone').val();
        const message = $('#test_wa_message').val();
        const btn = $('#btnTestWA');
        const resDiv = $('#test_wa_response');
        
        if(!phone || !message) {
            alert('Harap isi nomor dan pesan!');
            return;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...');
        resDiv.addClass('d-none');

        $.ajax({
            url: "{{ route('admin.settings.wa_test') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                phone: phone,
                message: message
            },
            success: function(res) {
                resDiv.removeClass('d-none');
                resDiv.find('pre').text(JSON.stringify(res, null, 2));
                btn.prop('disabled', false).text('Kirim Tes');
            },
            error: function(xhr) {
                resDiv.removeClass('d-none');
                resDiv.find('pre').text(xhr.responseText || 'Error occurred');
                btn.prop('disabled', false).text('Kirim Tes');
            }
        });
    }
</script>
@endsection