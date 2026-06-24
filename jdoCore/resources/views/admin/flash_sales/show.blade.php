@extends('admin.layouts.app')
@section('title', 'Kelola Produk Flash Sale')
@section('page-title', 'Produk Flash Sale')

@section('content')
    <div class="row g-4">
        <!-- Info Event -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                            <i class="fas fa-bolt fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $flashSale->title }}</h5>
                            <span class="badge bg-{{ $flashSale->is_active ? 'success' : 'secondary' }}">
                                {{ $flashSale->is_active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                    <div class="text-muted small mb-2">
                        <i class="far fa-clock me-2"></i>Mulai: <b
                            class="text-dark">{{ $flashSale->start_time->translatedFormat('d M Y, H:i') }}</b>
                    </div>
                    <div class="text-muted small">
                        <i class="far fa-flag me-2"></i>Selesai: <b
                            class="text-dark">{{ $flashSale->end_time->translatedFormat('d M Y, H:i') }}</b>
                    </div>
                </div>
            </div>

            <!-- Add Product Form -->
            <div class="card border-0 shadow-sm mt-4" style="border-radius:12px;overflow:hidden">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Produk</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.flash-sales.products.add', $flashSale) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small">Pilih Produk (Tersedia)</label>
                            <select name="product_id" class="form-select @error('product_id') is-invalid @enderror"
                                required>
                                <option value="">-- Pilih --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }} (Rp{{ number_format($product->base_price, 0, ',', '.') }})
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Harga Flash Sale (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                <input type="number" name="discount_price" class="form-control border-start-0 ps-0"
                                    min="0" step="1" inputmode="numeric"
                                    placeholder="0" value="{{ old('discount_price') }}" required>
                            </div>
                            @error('discount_price')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small">Alokasi Stok Flash Sale</label>
                            <input type="number" name="stock" class="form-control" placeholder="0" min="1"
                                value="{{ old('stock', 10) }}" required>
                            @error('stock')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100" style="border-radius:10px">
                            <i class="fas fa-check me-2"></i>Tambahkan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Registered Products -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex align-items-center">
                    <h6 class="fw-bold mb-0">Daftar Produk Terdaftar</h6>
                    <span class="badge bg-light text-primary ms-auto">{{ $flashSale->products->count() }} Produk</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Produk</th>
                                    <th>Harga Asli</th>
                                    <th>Harga FS</th>
                                    <th>Stok Alokasi</th>
                                    <th>Terjual</th>
                                    <th class="text-center pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($flashSale->products as $fsProduct)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-3"
                                                    style="width:40px;height:40px">
                                                    @if($fsProduct->product->primaryImage)
                                                        <img src="{{ asset($fsProduct->product->primaryImage->path) }}" alt=""
                                                            style="width:100%;height:100%;object-fit:cover;border-radius:4px">
                                                    @else
                                                        <i class="fas fa-box text-muted"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="d-block fw-medium text-dark text-truncate"
                                                        style="max-width: 150px;" title="{{ $fsProduct->product->name }}">
                                                        {{ $fsProduct->product->name }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted text-decoration-line-through small">
                                                Rp{{ number_format($fsProduct->product->base_price, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-danger">
                                                Rp{{ number_format($fsProduct->discount_price, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>{{ $fsProduct->stock }}</td>
                                        <td>{{ $fsProduct->sold }}</td>
                                        <td class="text-center pe-4">
                                            <form
                                                action="{{ route('admin.flash-sales.products.remove', [$flashSale, $fsProduct]) }}"
                                                method="POST" class="d-inline" id="remove-product-{{ $fsProduct->id }}">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                                    title="Hapus dari event"
                                                    onclick="confirmDelete('remove-product-{{ $fsProduct->id }}')">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-box-open fa-3x mb-3 opacity-25 d-block"></i>
                                            Belum ada produk yang didaftarkan untuk event ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection