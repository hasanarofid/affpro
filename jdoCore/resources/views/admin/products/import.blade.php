@extends('admin.layouts.app')
@section('title', 'Import Produk dari BigSeller')
@section('page-title', 'Import BigSeller')

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h6 class="mb-0 fw-bold">Upload File Excel</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.products.import.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Pilih File Excel (.xlsx)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required>
                            <small class="text-muted">Pastikan file berasal dari Export BigSeller (mendukung gambar dan variasi).</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kategori Default</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Pilih Kategori untuk produk impor</option>
                                @foreach(\App\Models\Category::all() as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Semua produk yang diimpor akan dimasukkan ke kategori ini.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Mulai Import
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-light ms-2">Batal</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-info border-0 shadow-sm">
                <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle me-1"></i> Informasi Import</h6>
                <p class="mb-0">Format file yang didukung adalah format Export bawaan dari BigSeller.</p>
                <ul class="mt-2 mb-0">
                    <li>Kolom Harga akan diambil dari kolom <strong>Diskon</strong>.</li>
                    <li>Gambar akan disimpan berupa URL (tidak diunduh ke server agar tidak timeout).</li>
                    <li>Otomatis membuat variasi (Warna, Ukuran, dll) jika terdapat data variasi pada baris produk.</li>
                    <li>Produk dengan <strong>Nama Produk</strong> yang sama tidak akan dimasukkan ganda (di-skip jika sudah ada).</li>
                </ul>
            </div>
        </div>
    </div>
@endsection
