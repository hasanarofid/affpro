<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card" style="border:none;border-radius:12px">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Detail Voucher</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Kode Voucher <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                            value="{{ old('code', $voucher->code ?? '') }}" required style="text-transform:uppercase">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipe <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="fixed" {{ old('type', $voucher->type ?? '') === 'fixed' ? 'selected' : '' }}>Nominal (Rp)</option>
                            <option value="percent" {{ old('type', $voucher->type ?? '') === 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Nilai <span class="text-danger">*</span></label>
                        <input type="number" name="value" class="form-control"
                            value="{{ old('value', isset($voucher) ? (int) $voucher->value : '') }}"
                            required min="0" step="1" inputmode="numeric">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Berlaku Untuk <span class="text-danger">*</span></label>
                        @php $currentScope = old('scope', $voucher->scope ?? 'all'); @endphp
                        <div class="d-flex flex-wrap gap-2">
                            <label class="flex-grow-1 border rounded p-3" style="cursor:pointer; border-radius:10px!important; min-width:200px" :class="''">
                                <input type="radio" name="scope" value="all" class="form-check-input me-2" {{ $currentScope === 'all' ? 'checked' : '' }}>
                                <strong>Semua (Default)</strong>
                                <div class="text-muted small mt-1">Potongan dihitung dari subtotal produk.</div>
                            </label>
                            <label class="flex-grow-1 border rounded p-3" style="cursor:pointer; border-radius:10px!important; min-width:200px">
                                <input type="radio" name="scope" value="products" class="form-check-input me-2" {{ $currentScope === 'products' ? 'checked' : '' }}>
                                <strong>Potongan Harga Produk</strong>
                                <div class="text-muted small mt-1">Mengurangi subtotal produk di keranjang.</div>
                            </label>
                            <label class="flex-grow-1 border rounded p-3" style="cursor:pointer; border-radius:10px!important; min-width:200px">
                                <input type="radio" name="scope" value="shipping" class="form-check-input me-2" {{ $currentScope === 'shipping' ? 'checked' : '' }}>
                                <strong>Potongan Ongkir</strong>
                                <div class="text-muted small mt-1">Mengurangi biaya kirim setelah kurir dipilih.</div>
                            </label>
                        </div>
                        @error('scope')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label">Min. Belanja (Rp)</label>
                        <input type="number" name="min_purchase" class="form-control"
                            value="{{ old('min_purchase', isset($voucher) && $voucher->min_purchase ? (int) $voucher->min_purchase : '') }}"
                            min="0" step="1" inputmode="numeric">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maks. Diskon (Rp)</label>
                        <input type="number" name="max_discount" class="form-control"
                            value="{{ old('max_discount', isset($voucher) && $voucher->max_discount ? (int) $voucher->max_discount : '') }}"
                            min="0" step="1" inputmode="numeric">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Maks. Penggunaan</label>
                        <input type="number" name="max_usage" class="form-control" value="{{ old('max_usage', $voucher->max_usage ?? '') }}" min="0" placeholder="Kosongkan = unlimited">
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Mulai Berlaku</label>
                        <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at', isset($voucher) && $voucher->starts_at ? $voucher->starts_at->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Berakhir</label>
                        <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', isset($voucher) && $voucher->expires_at ? $voucher->expires_at->format('Y-m-d') : '') }}">
                    </div>
                </div>
                <div class="form-check form-switch mt-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                        {{ old('is_active', $voucher->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActive">Aktif</label>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4" style="border-radius:10px">
                        <i class="bi bi-check-lg me-1"></i> {{ isset($voucher) ? 'Simpan Perubahan' : 'Buat Voucher' }}
                    </button>
                    <a href="{{ route('admin.vouchers.index') }}" class="btn btn-outline-secondary ms-2" style="border-radius:10px">Batal</a>
                </div>
            </div>
        </div>
    </div>
</div>
