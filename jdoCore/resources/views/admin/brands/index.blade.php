@extends('admin.layouts.app')
@section('title', __('admin.menu.brands'))
@section('page-title', __('admin.menu.brands'))

@section('styles')
    <style>
        .table-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
        table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
            background-color: var(--primary);
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-end mb-4">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal" style="border-radius:10px">
            <i class="bi bi-plus-lg me-1"></i> Tambah Merek
        </button>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Daftar Merek</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="brands-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="5%">No</th>
                            <th width="10%">Logo</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th width="15%" class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0" style="border-radius:12px">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">Tambah Merek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="add-brand-form" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug (Opsional)</label>
                            <input type="text" name="slug" class="form-control" placeholder="Biarkan kosong untuk otomatis">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <div class="form-text">Format: JPG, PNG, JPG, Rekomendasi 1:1.</div>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                            <label class="form-check-label">Aktif</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="border-radius:10px">
                            <i class="bi bi-plus-lg me-1"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Brand Modal -->
    <div class="modal fade" id="editBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0" style="border-radius:12px">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">Edit Merek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-brand-form">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="edit_slug" class="form-control">
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
        let table;
        $(document).ready(function () {
            table = $('#brands-table').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.brands.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 text-muted small' },
                    { 
                        data: 'logo', 
                        name: 'logo', 
                        orderable: false, 
                        searchable: false,
                        render: function(data) {
                            if(!data) return '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px"><i class="bi bi-image text-muted small"></i></div>';
                            return `<img src="/${data}" class="rounded shadow-sm" style="width:40px;height:40px;object-fit:cover" />`;
                        }
                    },
                    { data: 'name', name: 'name' },
                    { data: 'slug', name: 'slug', className: 'small text-muted' },
                    { data: 'is_active', name: 'is_active', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center pe-4' },
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                },
                drawCallback: function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-sm');
                }
            });

            // Handler form Tambah
            $('#add-brand-form').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                let origHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.brands.store') }}",
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.success) {
                            $('#addBrandModal').modal('hide');
                            form.trigger('reset');
                            table.ajax.reload(null, false);
                            new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } }).success(res.message);
                        }
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function () {
                        btn.html(origHtml).prop('disabled', false);
                    }
                });
            });

            // Handler form Edit
            $('#edit-brand-form').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                let url = form.attr('action');
                let origHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function (res) {
                        if (res.success) {
                            $('#editBrandModal').modal('hide');
                            table.ajax.reload(null, false);
                            new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } }).success(res.message);
                        }
                    },
                    error: function (xhr) {
                        let msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                        Swal.fire('Error', msg, 'error');
                    },
                    complete: function () {
                        btn.html(origHtml).prop('disabled', false);
                    }
                });
            });
        });

        function editBrand(id, name, slug, isActive) {
            $('#edit_name').val(name);
            $('#edit_slug').val(slug);
            $('#edit_is_active').prop('checked', isActive == 1);
            $('#edit-brand-form').attr('action', `/admin/brands/${id}`);
            $('#editBrandModal').modal('show');
        }

        function toggleStatus(id, el) {
            let isChecked = $(el).prop('checked') ? 1 : 0;
            $.ajax({
                url: `/admin/brands/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    name: $(el).closest('tr').find('td:eq(2)').text(), // Name is now the 3rd column (index 2) after adding Logo
                    is_active: isChecked
                },
                success: function (res) {
                    if (res.success) {
                        new Notyf({ duration: 2000, position: { x: 'right', y: 'top' } }).success('Status diperbarui.');
                    }
                },
                error: function () {
                    $(el).prop('checked', !isChecked); // Revert UI
                    Swal.fire('Error', 'Gagal memperbarui status.', 'error');
                }
            });
        }

        function deleteBrand(id) {
            Swal.fire({
                title: 'Hapus Merek?',
                text: "Data ini tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/brands/${id}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function (res) {
                            if (res.success) {
                                table.ajax.reload(null, false);
                                new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } }).success(res.message);
                            }
                        },
                        error: function () {
                            Swal.fire('Error', 'Gagal menghapus data.', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush