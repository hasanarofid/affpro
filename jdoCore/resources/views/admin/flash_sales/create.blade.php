@extends('admin.layouts.app')
@section('title', 'Buat Flash Sale Baru')
@section('page-title', 'Tambah Flash Sale')

@section('content')
    <div class="card border-0 shadow-sm" style="border-radius:12px;overflow:hidden">
        <div class="card-body p-4">
            <form action="{{ route('admin.flash-sales.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label">Judul Event <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="Contoh: Flash Sale Ramadhan 2026"
                            value="{{ old('title') }}" required>
                        @error('title')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="start_time" class="form-control" value="{{ old('start_time') }}"
                            required>
                        @error('start_time')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="end_time" class="form-control" value="{{ old('end_time') }}"
                            required>
                        @error('end_time')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top">
                        <div class="form-check form-switch cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input flex-shrink-0" type="checkbox" name="is_active" id="isActive"
                                value="1" style="width:2.5em; height:1.2em" checked>
                            <label class="form-check-label ps-2 mt-1" for="isActive">Aktifkan Flash Sale</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-light px-4 me-2">Batal</a>
                    <button type="submit" class="btn btn-primary px-4" style="border-radius:10px">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection