@extends('admin.layouts.app')
@section('title', __('admin.menu.categories'))
@section('page-title', __('admin.menu.categories'))

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
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal"
            style="border-radius:10px">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </button>
    </div>

    <div class="card table-card border-0 shadow-sm">
        <div class="card-header border-bottom py-3 px-4 bg-transparent d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold">Daftar Kategori</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="categories-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" width="5%">No</th>
                            <th>Nama</th>
                            <th>Parent</th>
                            <th>Produk</th>
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

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0" style="border-radius:12px">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="add-category-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent Kategori</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- Tidak ada (Root) --</option>
                                @foreach($parentCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (FontAwesome)</label>
                            <input type="text" name="icon" class="form-control" placeholder="fa-folder">
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
                        <button type="submit" class="btn btn-primary w-100" style="border-radius:10px">
                            <i class="bi bi-plus-lg me-1"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content border-0" style="border-radius:12px">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-category-form">
                        @csrf @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent Kategori</label>
                            <select name="parent_id" id="edit_parent_id" class="form-select">
                                <option value="">-- Tidak ada (Root) --</option>
                                @foreach($parentCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Icon (FontAwesome)</label>
                            <input type="text" name="icon" id="edit_icon" class="form-control" placeholder="fa-folder">
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
        let table;
        $(document).ready(function () {
            table = $('#categories-table').DataTable({
                stateSave: true,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: "{{ route('admin.categories.index') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 text-muted small' },
                    { data: 'name', name: 'name' },
                    { data: 'parent', name: 'parent.name' },
                    { data: 'products_count', name: 'products_count', searchable: false },
                    { data: 'status', name: 'is_active', orderable: false, searchable: false },
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
            $('#add-category-form').on('submit', function (e) {
                e.preventDefault();
                let form = $(this);
                let btn = form.find('button[type="submit"]');
                let origHtml = btn.html();
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Menyimpan...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.categories.store') }}",
                    type: 'POST',
                    data: form.serialize(),
                    success: function (res) {
                        if (res.success) {
                            $('#addCategoryModal').modal('hide');
                            form.trigger('reset');
                            table.ajax.reload(null, false);
                            new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } }).success(res.message);
                            setTimeout(() => window.location.reload(), 1000); // optional refresh for parent select
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
            $('#edit-category-form').on('submit', function (e) {
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
                            $('#editCategoryModal').modal('hide');
                            table.ajax.reload(null, false);
                            new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } }).success(res.message);
                            setTimeout(() => window.location.reload(), 1000); // refresh parent select
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

        function editCategory(id, name, parentId, icon, sortOrder, isActive) {
            $('#edit_name').val(name);
            $('#edit_parent_id').val(parentId);
            $('#edit_icon').val(icon);
            $('#edit_sort_order').val(sortOrder);
            $('#edit_is_active').prop('checked', isActive == 1);
            $('#edit-category-form').attr('action', `/admin/categories/${id}`);
            $('#editCategoryModal').modal('show');
        }

        function toggleStatus(id, el) {
            let isChecked = $(el).prop('checked') ? 1 : 0;
            $.ajax({
                url: `/admin/categories/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    // required for validation
                    name: $(el).closest('tr').find('span.fw-medium').text(),
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

        function deleteCategory(id) {
            Swal.fire({
                title: 'Hapus Kategori?',
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
                        url: `/admin/categories/${id}`,
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