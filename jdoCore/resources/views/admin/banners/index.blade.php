@extends('admin.layouts.app')
@section('title', __('admin.menu.banners'))
@section('page-title', __('admin.menu.banners'))

@section('content')
    <div class="d-flex justify-content-end mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal" style="border-radius:10px">
            <i class="bi bi-plus-lg me-1"></i> Tambah Banner
        </button>
    </div>

    <div class="row g-3 mb-4">
        @forelse($banners as $banner)
            <div class="col-lg-4 col-md-6">
                <div class="card" style="border:none;border-radius:12px;overflow:hidden">
                    <img src="{{ asset($banner->image) }}" class="card-img-top" style="height:160px;object-fit:cover">
                    <div class="card-body py-2 d-flex align-items-center">
                        <div>
                            <div class="fw-medium small">{{ $banner->title ?: 'Banner' }}</div>
                            <span class="badge bg-{{ $banner->is_active ? 'success' : 'secondary' }}"
                                style="font-size:.65rem">{{ $banner->is_active ? 'Aktif' : 'Draft' }}</span>
                            <span class="badge bg-primary ms-1" style="font-size:.65rem"><i class="bi bi-sort-numeric-down"></i>
                                Urutan: {{ $banner->sort_order }}</span>
                        </div>
                        <div class="d-flex ms-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary me-2"
                                onclick="editBanner({{ $banner->id }}, '{{ addslashes($banner->title) }}', '{{ addslashes($banner->url) }}', {{ $banner->sort_order }}, {{ $banner->is_active }})"
                                style="border-radius:6px"><i class="bi bi-pencil"></i></button>

                            <form id="delete-banner-{{ $banner->id }}" action="{{ route('admin.banners.destroy', $banner) }}"
                                method="POST">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete('delete-banner-{{ $banner->id }}')"
                                    class="btn btn-sm btn-outline-danger" style="border-radius:6px"><i
                                        class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center text-muted py-5">{{ __('general.no_data') }}</div>
            </div>
        @endforelse
    </div>

    <!-- Add Banner Modal -->
    <div class="modal fade" id="addBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0" style="border-radius:12px">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">Tambah Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="add-banner-form" action="{{ route('admin.banners.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Gambar <span class="text-danger">*</span></label>
                            <input type="file" name="image" class="form-control" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" name="title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="url" name="url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" class="form-control" value="0">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                            <label class="form-check-label">Aktif</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="border-radius:10px"><i
                                class="bi bi-plus-lg me-1"></i> Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Banner Modal -->
    <div class="modal fade" id="editBannerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0" style="border-radius:12px">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">Edit Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-banner-form" method="POST">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Judul</label>
                            <input type="text" name="title" id="edit_title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL</label>
                            <input type="url" name="url" id="edit_url" class="form-control" placeholder="https://...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control">
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1" class="form-check-input">
                            <label class="form-check-label">Aktif</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="border-radius:10px">Simpan
                            Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            $('#add-banner-form').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let submitBtn = form.find('button[type="submit"]');
                let originalText = submitBtn.html();

                submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            let notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
                            notyf.success(response.message);
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    },
                    error: function (xhr) {
                        submitBtn.html(originalText).prop('disabled', false);
                        let errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let errorMsg = Object.values(errors).map(e => e.join(' ')).join('<br>');
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                html: errorMsg
                            });
                        } else {
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        }
                    }
                });
            });

            $('#edit-banner-form').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let submitBtn = form.find('button[type="submit"]');
                let originalText = submitBtn.html();

                submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.success) {
                            let notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
                            notyf.success(response.message);
                            setTimeout(() => window.location.reload(), 1000);
                        }
                    },
                    error: function (xhr) {
                        submitBtn.html(originalText).prop('disabled', false);
                        let errors = xhr.responseJSON?.errors;
                        if (errors) {
                            let errorMsg = Object.values(errors).map(e => e.join(' ')).join('<br>');
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                html: errorMsg
                            });
                        } else {
                            Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                        }
                    }
                });
            });

            // AJAX Delete (Optional, but good to have since we are doing AJAX)
            $(document).on('submit', 'form[id^="delete-banner-"]', function (e) {
                e.preventDefault();
                let form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        if (response.success) {
                            form.closest('.col-md-6').fadeOut(300, function () { $(this).remove(); });
                            let notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
                            notyf.success(response.message);
                        }
                    }
                });
            });
        });

        function editBanner(id, title, url, sortOrder, isActive) {
            let modal = new bootstrap.Modal(document.getElementById('editBannerModal'));
            $('#edit_title').val(title);
            $('#edit_url').val(url);
            $('#edit_sort_order').val(sortOrder);
            $('#edit_is_active').prop('checked', isActive == 1);

            $('#edit-banner-form').attr('action', '/admin/banners/' + id);

            modal.show();
        }

        // Override confirmDelete to trigger form submit event so AJAX handler kicks in
        function confirmDelete(formId) {
            Swal.fire({
                title: '{{ __("general.confirm_delete") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ __("general.delete") }}',
                cancelButtonText: '{{ __("general.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#' + formId).trigger('submit');
                }
            });
        }
    </script>
@endpush