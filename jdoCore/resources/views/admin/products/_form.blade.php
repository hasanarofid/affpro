<style>
    /* Variant editor (admin product form) */
    .variant-type-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 20px;
        background: #fff;
    }
    .variant-values-row {
        min-height: 38px;
        padding-top: 2px;
    }
    .variant-chip {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 6px 5px 12px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        transition: border-color .15s ease, background .15s ease;
    }
    .variant-chip:focus-within {
        border-color: var(--bs-primary, #4f46e5);
        background: #eef2ff;
    }
    .variant-chip-input {
        border: 0;
        outline: 0;
        background: transparent;
        font-size: .85rem;
        font-weight: 500;
        min-width: 50px;
        max-width: 160px;
        padding: 2px 4px;
        color: #0f172a;
    }
    .variant-chip-remove {
        border: 0;
        background: transparent;
        color: #94a3b8;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s ease, color .15s ease;
    }
    .variant-chip-remove:hover {
        background: #fee2e2;
        color: #b91c1c;
    }
    .variant-chip-add {
        background: #16a34a;
        color: #fff;
        border: 0;
        border-radius: 999px;
        padding: 7px 16px;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s ease;
    }
    .variant-chip-add:hover { background: #15803d; }

    /* Combo table */
    .variant-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
    }
    .variant-combo-table { font-size: .88rem; }
    .variant-combo-table thead th {
        background: #f8fafc;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        font-weight: 600;
        padding: 14px 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    .variant-combo-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .variant-combo-table tbody tr:last-child td { border-bottom: 0; }
    .variant-combo-table tbody tr:hover { background: #f8fafc; }
    .variant-combo-table .form-control-sm {
        border-radius: 8px;
        padding: 6px 10px;
        font-size: .85rem;
    }
    .variant-thumb {
        width: 36px;
        height: 36px;
        object-fit: cover;
        border-radius: 6px;
        flex-shrink: 0;
    }
</style>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card" style="border:none;border-radius:12px">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Informasi Produk</h6>
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $product->name ?? '') }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">SKU <span class="text-muted">(opsional)</span></label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                            value="{{ old('sku', $product->sku ?? '') }}" placeholder="Contoh: SKU-001" maxlength="64">
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted" style="font-size:0.7rem">Kode unik produk untuk inventory.</small>
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Deskripsi</label>
                        <button type="button" class="btn btn-sm btn-outline-info" id="btn-generate-desc" style="border-radius:6px; font-size:0.75rem">
                            <i class="bi bi-stars me-1"></i> Generate dengan AI
                        </button>
                    </div>
                    <textarea name="description" id="description" class="form-control tinymce-editor" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Harga Dasar <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="base_price" class="form-control @error('base_price') is-invalid @enderror"
                                value="{{ old('base_price', $product->base_price ?? '') }}" required min="0">
                        </div>
                        @error('base_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Harga Diskon/Promo</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="discount_price" class="form-control @error('discount_price') is-invalid @enderror"
                                value="{{ old('discount_price', $product->discount_price ?? '') }}" min="0" placeholder="Opsional">
                        </div>
                        @error('discount_price') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Berat (gram) <span class="text-danger">*</span></label>
                        <input type="number" name="weight" class="form-control" value="{{ old('weight', $product->weight ?? 0) }}" required min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Stok <span class="text-danger">*</span></label>
                        <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock ?? 0) }}" required min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Min. Order <span class="text-danger">*</span></label>
                        <input type="number" name="min_order" class="form-control" value="{{ old('min_order', $product->min_order ?? 1) }}" required min="1">
                    </div>
                </div>
            </div>
        </div>

        <!-- Images -->
        <div class="card mt-3" style="border:none;border-radius:12px">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-image me-1"></i> Gambar Produk</h6>
                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-improve-image" data-type="image_enhancement" style="border-radius:6px; font-size:0.75rem">
                        <i class="bi bi-stars me-1"></i> Minta Saran Edit AI
                    </button>
                </div>
                @if(isset($product) && $product->images->isNotEmpty())
                <div class="d-flex gap-2 mb-3 flex-wrap">
                    @foreach($product->images as $img)
                    <div class="border rounded position-relative" style="width:80px;height:80px">
                        <img src="{{ asset($img->path) }}" class="rounded" style="width:100%;height:100%;object-fit:cover">
                        @if($img->is_primary) <span class="badge bg-primary position-absolute" style="top:2px;left:2px;font-size:.55rem">Utama</span> @endif
                    </div>
                    @endforeach
                </div>
                @endif
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                <div class="text-muted small mt-1">Upload beberapa gambar sekaligus. Gambar pertama = foto utama.</div>
            </div>
        </div>

        {{-- Variant + Wholesale dipindah ke bawah agar full-width (col-12) --}}
    </div>

    <div class="col-lg-4">
        <div class="card" style="border:none;border-radius:12px">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Kategori & Merek</h6>
                <div class="mb-3">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Merek</label>
                    <select name="brand_id" class="form-select">
                        <option value="">-- Tanpa Merek --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card mt-3" style="border:none;border-radius:12px" x-data="{ type: '{{ old('type', $product->type ?? 'physical') }}' }">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Pengaturan & Digitals</h6>
                <div class="mb-3">
                    <label class="form-label">Tipe Produk</label>
                    <select name="type" class="form-select" x-model="type">
                        <option value="physical">Fisik (Barang Dikirim)</option>
                        <option value="digital">Digital (Virtual/File)</option>
                    </select>
                </div>

                <div x-show="type === 'digital'" style="display:none;" x-transition>
                    <div class="p-3 bg-light rounded border mb-3">
                        <h6 class="fs-6 fw-semibold mb-2">Konten Digital</h6>
                        <div class="mb-2">
                            <label class="form-label small">Teks / Info Akses (Opsional)</label>
                            <textarea name="digital_info_text" class="form-control" rows="3" placeholder="Masukkan link download, lisensi, atau catatan akses...">{{ old('digital_info_text', $product->digital_info_text ?? '') }}</textarea>
                            <small class="text-muted">Dibagikan ke pembeli otomatis setelah tagihan dibayar.</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small">File Digital (Opsional)</label>
                            @if(isset($product) && $product->digital_file_path)
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <span class="badge bg-success">File Tersedia</span>
                                    <label class="form-check-label small ms-2"><input type="checkbox" name="remove_digital_file" value="1" class="form-check-input me-1">Hapus File</label>
                                </div>
                            @endif
                            <input type="file" name="digital_file" class="form-control form-control-sm">
                            <small class="text-muted">Max 10MB.</small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alert Stok Minimum</label>
                    <input type="number" name="min_stock_alert" class="form-control" value="{{ old('min_stock_alert', $product->min_stock_alert ?? 5) }}" min="0">
                </div>
                <div class="form-check form-switch mb-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                        {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActive">Aktif</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="isFeatured"
                        {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isFeatured">Produk Unggulan</label>
                </div>
                <button type="submit" class="btn btn-primary w-100" style="border-radius:10px">
                    <i class="bi bi-check-lg me-1"></i> {{ isset($product) ? 'Simpan Perubahan' : 'Simpan Produk' }}
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100 mt-2" style="border-radius:10px">
                    Batal
                </a>
            </div>
        </div>
    </div>

    {{-- Full-width: Variants --}}
    <div class="col-12">
        <div class="card" style="border:none;border-radius:12px" x-data="variantEditor()">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-layers me-1"></i> Varian Produk</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius:8px" @click="addType()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Tipe Varian
                    </button>
                </div>

                <template x-if="types.length === 0">
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-layers fs-3 d-block mb-2 opacity-50"></i>
                        <div class="small">Belum ada varian. Klik <strong>Tambah Tipe Varian</strong> jika produk memiliki variasi (misal: Warna, Ukuran).</div>
                    </div>
                </template>

                <template x-for="(type, ti) in types" :key="ti">
                    <div class="variant-type-card mb-3">
                        <div class="row g-3 align-items-start">
                            <div class="col-lg-3 col-md-4">
                                <label class="form-label small fw-medium text-muted">Nama Tipe</label>
                                <input type="text" x-model="type.name" class="form-control form-control-sm"
                                    placeholder="Contoh: Warna / Ukuran">
                                <input type="hidden" :name="'variant_types['+ti+'][name]'" :value="type.name">
                                <input type="hidden" :name="'variant_types['+ti+'][id]'" :value="type.id || ''">
                                <p class="text-muted small mt-2 mb-0" style="font-size:0.7rem; line-height:1.4">
                                    Tambahkan pilihan sub varian di kolom kanan. Maksimal 10 sub varian.
                                </p>
                            </div>
                            <div class="col-lg-9 col-md-8">
                                <label class="form-label small fw-medium text-muted">Pilihan Sub Varian</label>
                                <div class="d-flex flex-wrap gap-2 align-items-center variant-values-row">
                                    <template x-for="(val, vi) in type.values" :key="vi">
                                        <div class="variant-chip">
                                            <input type="text" x-model="val.value" class="variant-chip-input"
                                                placeholder="nilai" :size="Math.max(val.value.length, 4)">
                                            <input type="hidden" :name="'variant_types['+ti+'][values]['+vi+'][value]'" :value="val.value">
                                            <input type="hidden" :name="'variant_types['+ti+'][values]['+vi+'][id]'" :value="val.id || ''">
                                            <button type="button" class="variant-chip-remove" @click="type.values.splice(vi,1)" title="Hapus nilai">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" class="variant-chip-add" @click="type.values.push({value:'',id:''})">
                                        <i class="bi bi-plus me-1"></i>tambah
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light text-danger ms-auto" @click="removeType(ti)" title="Hapus tipe varian">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="types.length > 0 && hasValues()">
                    <div>
                        <button type="button" class="btn btn-outline-primary mb-3" style="border-radius:10px" @click="generateCombinations()" title="Regenerate kombinasi (otomatis tergenerate saat ada perubahan).">
                            <i class="bi bi-arrow-repeat me-1"></i> Regenerate Kombinasi
                        </button>

                        <template x-if="combinations.length > 0">
                            <div class="table-responsive variant-table-wrap">
                                <table class="table align-middle variant-combo-table mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3" style="width:140px">Varian</th>
                                            <th style="width:180px">Sub Varian</th>
                                            <th style="width:160px">SKU</th>
                                            <th style="width:160px">Harga (Rp)</th>
                                            <th style="width:120px">Stok</th>
                                            <th>Gambar</th>
                                            <th class="pe-3 text-end" style="width:60px">&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(combo, ci) in combinations" :key="ci">
                                            <tr>
                                                <td class="ps-3">
                                                    <span class="text-muted small text-uppercase fw-semibold" x-text="comboTypeNames(combo)"></span>
                                                </td>
                                                <td>
                                                    <span class="fw-medium" x-text="combo.label"></span>
                                                </td>
                                                <td>
                                                    <input type="text" x-model="combo.sku" class="form-control form-control-sm" placeholder="SKU">
                                                    <input type="hidden" :name="'variants['+ci+'][sku]'" :value="combo.sku">
                                                    <input type="hidden" :name="'variants['+ci+'][label]'" :value="combo.label">
                                                    <input type="hidden" :name="'variants['+ci+'][values]'" :value="JSON.stringify(combo.values)">
                                                    <input type="hidden" :name="'variants['+ci+'][id]'" :value="combo.id || ''">
                                                </td>
                                                <td>
                                                    <input type="number" x-model="combo.price" class="form-control form-control-sm" min="0" step="1" inputmode="numeric" placeholder="0">
                                                    <input type="hidden" :name="'variants['+ci+'][price]'" :value="combo.price">
                                                </td>
                                                <td>
                                                    <input type="number" x-model="combo.stock" class="form-control form-control-sm" min="0" placeholder="0">
                                                    <input type="hidden" :name="'variants['+ci+'][stock]'" :value="combo.stock">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <template x-if="combo.image_path">
                                                            <img :src="'/' + combo.image_path" class="border rounded variant-thumb" alt="">
                                                        </template>
                                                        <input type="file" :name="'variant_images['+ci+']'" accept="image/*" class="form-control form-control-sm flex-grow-1">
                                                    </div>
                                                </td>
                                                <td class="pe-3 text-end">
                                                    <button type="button" class="btn btn-sm btn-light text-danger" @click="combinations.splice(ci,1)" title="Hapus kombinasi">
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Full-width: Wholesale --}}
    <div class="col-12">
        <div class="card" style="border:none;border-radius:12px" x-data="wholesaleEditor()">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-tags me-1"></i> Harga Grosir</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" style="border-radius:8px" @click="addTier()">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Tier
                    </button>
                </div>
                <template x-if="tiers.length === 0">
                    <div class="text-muted small text-center py-2">Belum ada harga grosir. Opsional.</div>
                </template>
                <template x-for="(tier, i) in tiers" :key="i">
                    <div class="row g-2 mb-2 align-items-center">
                        <div class="col-md-4 col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Min</span>
                                <input type="number" x-model="tier.min_qty" class="form-control" min="2" placeholder="2">
                                <input type="hidden" :name="'wholesale['+i+'][min_qty]'" :value="tier.min_qty">
                                <input type="hidden" :name="'wholesale['+i+'][id]'" :value="tier.id || ''">
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="number" x-model="tier.price" class="form-control" min="0" step="1" inputmode="numeric" placeholder="Harga per pcs">
                                <input type="hidden" :name="'wholesale['+i+'][price]'" :value="tier.price">
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <button type="button" class="btn btn-sm btn-outline-danger" @click="tiers.splice(i,1)">
                                <i class="bi bi-trash3 me-1"></i>Hapus
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@php
    $typesData = [];
    $combinationsData = [];
    if (isset($product)) {
        $typesData = $product->variantTypes->map(function($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'values' => $t->values->map(function($v) {
                    return ['id' => $v->id, 'value' => $v->value];
                })->toArray()
            ];
        })->toArray();
        $combinationsData = $product->variants->map(function($v) {
            return [
                'id' => $v->id,
                'label' => $v->label,
                'sku' => $v->sku,
                'price' => $v->price,
                'stock' => $v->stock,
                'image_path' => $v->image_path,
                'values' => $v->values->pluck('value')->toArray()
            ];
        })->toArray();
    }
@endphp

@push('scripts')
<script>
function variantEditor() {
    return {
        types: @json($typesData),
        combinations: @json($combinationsData),
        _autoGenTimer: null,
        init() {
            // Auto-generate combinations whenever types or their values change.
            this.$watch('types', () => this._scheduleAutoGen(), { deep: true });
        },
        _scheduleAutoGen() {
            clearTimeout(this._autoGenTimer);
            this._autoGenTimer = setTimeout(() => {
                if (this.types.length > 0 && this.hasValues()) {
                    this.generateCombinations();
                } else {
                    this.combinations = [];
                }
            }, 300);
        },
        addType() { this.types.push({ name: '', values: [{ value: '', id: '' }], id: '' }); },
        removeType(i) { this.types.splice(i, 1); this.combinations = []; },
        hasValues() { return this.types.length > 0 && this.types.every(t => t.values.some(v => v.value.trim() !== '')); },
        comboTypeNames(combo) {
            const names = this.types.map(t => (t.name || '').trim()).filter(Boolean);
            return names.join(' / ');
        },
        generateCombinations() {
            const filtered = this.types.map(t => ({
                name: t.name,
                values: t.values.filter(v => v.value.trim() !== '')
            })).filter(t => t.values.length > 0);
            if (filtered.length === 0) return;

            const basePrice = document.querySelector('[name=base_price]')?.value || 0;
            const cartesian = (arr) => arr.reduce((a, b) => a.flatMap(x => b.map(y => [...x, y])), [[]]);
            const combos = cartesian(filtered.map(t => t.values.map(v => v.value)));

            // Preserve existing combos if label matches
            const existing = {};
            this.combinations.forEach(c => { existing[c.label] = c; });

            this.combinations = combos.map(vals => {
                const label = vals.join(' / ');
                const prev = existing[label];
                return {
                    label, values: vals,
                    sku: prev?.sku || '',
                    price: prev?.price || basePrice,
                    stock: prev?.stock || 0,
                    image_path: prev?.image_path || null,
                    id: prev?.id || ''
                };
            });
        }
    };
}

function wholesaleEditor() {
    return {
        tiers: @json(isset($product) ? $product->wholesalePrices->map(fn($w) => [
            'id' => $w->id, 'min_qty' => $w->min_qty, 'price' => $w->price
        ])->toArray() : []),
        addTier() { this.tiers.push({ min_qty: '', price: '', id: '' }); }
    };
}
</script>
</script>

<script>
$(document).ready(function() {
    $('#btn-generate-desc').click(function() {
        let btn = $(this);
        let name = $('input[name="name"]').val();
        let catId = $('select[name="category_id"]').val();

        if (!name || !catId) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Kurang',
                text: 'Mohon isi Nama Produk dan pilih Kategori terlebih dahulu sebelum men-generate deskripsi.'
            });
            return;
        }

        let originalHtml = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Berpikir...').prop('disabled', true);

        $.ajax({
            url: '{{ route("admin.products.ai_description") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
                category_id: catId
            },
            success: function(res) {
                if (res.success) {
                    // Update TinyMCE editor if available
                    if (typeof tinymce !== 'undefined' && tinymce.get('description')) {
                        tinymce.get('description').setContent(res.description);
                    } else {
                        $('#description').val(res.description);
                    }
                    
                    let notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
                    notyf.success('Deskripsi berhasil dibuat oleh AI!');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Terjadi kesalahan sistem.';
                Swal.fire('Gagal', msg, 'error');
            },
            complete: function() {
                btn.html(originalHtml).prop('disabled', false);
            }
        });
    });

    $('#btn-improve-image').click(function() {
        let name = $('input[name="name"]').val();
        if (!name) {
            Swal.fire('Data Kurang', 'Masukkan Nama Produk di atas terlebih dahulu.', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Generate Prompt AI Image',
            input: 'textarea',
            inputLabel: 'Jelaskan produk Anda & konsep visual yang diinginkan (misal: "Sepatu olahraga merah di atas batu dengan cipratan air")',
            inputPlaceholder: 'Konsep foto impian Anda...',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-magic me-1"></i> Buat Prompt',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: (prompt) => {
                if (!prompt) {
                    Swal.showValidationMessage('Prompt konsep harus diisi');
                    return false;
                }
                
                return $.ajax({
                    url: '{{ route("admin.ai.generate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        prompt: `Produk: ${name}. Konsep: ${prompt}`,
                        type: 'image_enhancement'
                    }
                }).catch(error => {
                    Swal.showValidationMessage(
                        `Gagal: ${error.responseJSON?.message || 'Terjadi kesalahan sistem'}`
                    );
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed && result.value.success) {
                Swal.fire({
                    title: 'Prompt Tersedia',
                    html: `
                        <p class="text-muted small text-start mb-2">Salin teks bahasa Inggris di bawah ini dan tempel (paste) di <strong>Nano Banana AI</strong> atau Midjourney untuk menghasilkan foto produk estetik:</p>
                        <div class="position-relative">
                            <textarea id="ai-prompt-result" class="form-control mb-3" rows="6" readonly style="font-family: monospace; font-size: 0.85rem; background:#f8f9fa;">${result.value.content}</textarea>
                            <button onclick="navigator.clipboard.writeText(document.getElementById('ai-prompt-result').value); const notyf = new Notyf(); notyf.success('Prompt disalin!');" class="btn btn-sm btn-dark position-absolute" style="bottom: 25px; right: 10px;">
                                <i class="bi bi-clipboard me-1"></i> Copy Prompt
                            </button>
                        </div>
                    `,
                    icon: 'success',
                    width: '600px',
                    showConfirmButton: true,
                    confirmButtonText: 'Tutup'
                });
            }
        });
    });
});
</script>
@endpush
