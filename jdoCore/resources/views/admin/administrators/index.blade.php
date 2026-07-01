@extends('admin.layouts.app')
@section('title', 'Kelola Administrator')
@section('page-title', 'Kelola Administrator')

@section('content')
    <div class="card border-0 shadow-sm" x-data="adminAccountHandler()">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Daftar Admin & Superadmin</h6>
            @if(!$demoMode)
                <button class="btn btn-primary btn-sm" @click="resetForm(); bootstrapModal.show();" style="border-radius:8px">
                    <i class="bi bi-person-plus me-1"></i> Tambah Admin
                </button>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-hover mb-0 align-middle w-100" id="adminsTable">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th style="width: 100px">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div class="modal fade" id="adminModal" tabindex="-1" aria-hidden="true" x-ref="modal">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius:16px">
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" x-text="isEdit ? 'Edit Admin' : 'Tambah Admin'"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                            @click="resetForm()"></button>
                    </div>
                    <form
                        :action="isEdit ? `{{ url('admin/administrators') }}/${currentId}` : '{{ route('admin.administrators.store') }}'"
                        method="POST">
                        @csrf
                        <template x-if="isEdit">
                            @method('PUT')
                        </template>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Nama Lengkap</label>
                                <input type="text" name="name" x-model="formData.name" class="form-control" required
                                    style="border-radius:8px">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Email</label>
                                <input type="email" name="email" x-model="formData.email" class="form-control" required
                                    style="border-radius:8px">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Telepon</label>
                                <input type="text" name="phone" x-model="formData.phone" class="form-control"
                                    placeholder="6281..." style="border-radius:8px">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Role</label>
                                <select name="role" x-model="formData.role" class="form-select" required
                                    style="border-radius:8px">
                                    <option value="admin">Admin</option>
                                    <option value="superadmin">Superadmin</option>
                                </select>
                                <small class="text-muted" style="font-size:0.7rem">Superadmin dapat mengubah pengaturan
                                    sistem.</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium"
                                    x-text="isEdit ? 'Password (Kosongkan jika tidak diubah)' : 'Password'"></label>
                                <input type="password" name="password" class="form-control" :required="!isEdit"
                                    minlength="6" style="border-radius:8px">
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                style="border-radius:8px">Batal</button>
                            <button type="submit" class="btn btn-primary px-4" style="border-radius:8px"
                                x-text="isEdit ? 'Perbarui' : 'Simpan'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function adminAccountHandler() {
            return {
                isEdit: false,
                currentId: null,
                formData: { name: '', email: '', phone: '', role: 'admin' },
                bootstrapModal: null,
                init() {
                    this.bootstrapModal = new bootstrap.Modal(this.$refs.modal);
                    window.editAdmin = (id) => this.fetchAndEdit(id);

                    $('#adminsTable').DataTable({
                stateSave: true,
                        processing: true,
                        serverSide: true,
                        responsive: true,
                        ajax: "{{ route('admin.administrators.index') }}",
                        columns: [
                            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 text-muted small' },
                            { data: 'name_val', name: 'name' },
                            { data: 'email', name: 'email', className: 'text-muted' },
                            { data: 'phone', name: 'phone', className: 'text-muted' },
                            { data: 'role_badge', name: 'roles.name', orderable: false, searchable: false },
                            { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
                            { data: 'action', name: 'action', orderable: false, searchable: false }
                        ],
                        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
                    });
                },
                fetchAndEdit(id) {
                    fetch(`{{ url('admin/administrators') }}/${id}`)
                        .then(res => res.json())
                        .then(data => {
                            this.isEdit = true;
                            this.currentId = id;
                            this.formData = {
                                name: data.name,
                                email: data.email,
                                phone: data.phone || '',
                                role: data.role || 'admin'
                            };
                            this.bootstrapModal.show();
                        });
                },
                resetForm() {
                    this.isEdit = false;
                    this.currentId = null;
                    this.formData = { name: '', email: '', phone: '', role: 'admin' };
                }
            }
        }
    </script>
    <style>
        .btn-light-primary {
            background: #e0e7ff;
            color: #4338ca;
            border: none;
        }

        .btn-light-danger {
            background: #fee2e2;
            color: #b91c1c;
            border: none;
        }

        .btn-light-primary:hover {
            background: #c7d2fe;
            color: #3730a3;
        }

        .btn-light-danger:hover {
            background: #fecaca;
            color: #991b1b;
        }
    </style>
@endsection