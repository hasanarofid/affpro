@extends('admin.layouts.app')
@section('title', 'Popup Promo')
@section('page-title', 'Popup Promo')

@section('content')
<div class="d-flex flex-wrap gap-3 align-items-center justify-content-between mb-4">
    <div>
        <h6 class="fw-bold mb-1">Kelola Popup Promo</h6>
        <p class="text-muted small mb-0">Tampilkan banner promosi di halaman beranda storefront. Bisa link ke produk, halaman, atau URL custom.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary" style="border-radius:10px" data-bs-toggle="modal" data-bs-target="#popupModal" id="btnAddPopup">
            <i class="bi bi-plus-lg me-1"></i> Tambah Popup
        </button>
    </div>
</div>

<div class="card table-card border-0 shadow-sm">
    <div class="card-header border-bottom py-3 px-4 bg-transparent">
        <h6 class="mb-0 fw-bold">Daftar Popup</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive p-3">
            <table class="table table-hover mb-0 align-middle w-100" id="popupsTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width:50px">#</th>
                        <th style="width:90px">Gambar</th>
                        <th>Judul & Link</th>
                        <th style="width:200px">Jadwal</th>
                        <th style="width:100px">Status</th>
                        <th class="pe-4 text-end" style="width:110px">Aksi</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

{{-- Modal Add/Edit --}}
<div class="modal fade" id="popupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:14px">
            <form id="popupForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="popup_id" id="popup_id" value="">

                <div class="modal-header">
                    <h5 class="modal-title" id="popupModalTitle"><i class="bi bi-megaphone me-2"></i>Tambah Popup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="popupErrorAlert"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" maxlength="255" required>
                        <small class="text-muted">Untuk identifikasi internal saja, tidak ditampilkan ke pembeli.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-medium">Gambar Popup <span class="text-danger" id="imageRequired">*</span></label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG/PNG/WebP, max 5 MB. Rasio rekomendasi 1:1 atau 4:5 (vertikal).</small>
                        <div class="mt-2 d-none" id="imagePreviewWrap">
                            <img id="imagePreview" src="" class="rounded border" style="max-height:160px;object-fit:contain">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Tipe Link</label>
                            <select name="link_type" id="link_type" class="form-select">
                                <option value="none">Tanpa Link</option>
                                <option value="product">Produk (slug)</option>
                                <option value="page">Halaman Statis (slug)</option>
                                <option value="url">URL Custom</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Target Link</label>
                            <input type="text" name="link_target" id="link_target" class="form-control" placeholder="—" disabled>
                            <small class="text-muted" id="link_target_hint">Pilih tipe link untuk mengaktifkan.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Label Tombol (opsional)</label>
                            <input type="text" name="button_label" id="button_label" class="form-control" maxlength="100" placeholder="Lihat Promo">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Urutan Tampil</label>
                            <input type="number" name="sort_order" id="sort_order" class="form-control" value="0" min="0">
                            <small class="text-muted">Angka kecil tampil duluan jika ada beberapa popup aktif.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Mulai Tampil</label>
                            <input type="datetime-local" name="start_at" id="start_at" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Berakhir</label>
                            <input type="datetime-local" name="end_at" id="end_at" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-medium">Delay Tampil (detik)</label>
                            <input type="number" name="display_delay" id="display_delay" class="form-control" value="0" min="0" max="60">
                            <small class="text-muted">0 = langsung muncul saat halaman terbuka.</small>
                        </div>
                        <div class="col-md-6 d-flex align-items-center pt-md-4">
                            <div class="form-check form-switch me-3">
                                <input class="form-check-input" type="checkbox" name="show_once_per_session" id="show_once_per_session" value="1" checked>
                                <label class="form-check-label small" for="show_once_per_session">Tampil sekali per sesi</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                <label class="form-check-label small" for="is_active">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitPopup">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="submitSpinner"></span>
                        <span id="submitLabel">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function () {
    const csrf = '{{ csrf_token() }}';
    const indexUrl = "{{ route('admin.promo-popups.index') }}";
    const storeUrl = "{{ route('admin.promo-popups.store') }}";

    const table = $('#popupsTable').DataTable({
                stateSave: true,
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        ajax: { url: indexUrl },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'ps-4 text-muted small' },
            { data: 'image', orderable: false, searchable: false },
            { data: 'title_info', name: 'title' },
            { data: 'schedule', name: 'start_at', orderable: false, searchable: false },
            { data: 'is_active', name: 'is_active', orderable: false },
            { data: 'action', orderable: false, searchable: false, className: 'pe-4' },
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
    });

    // Helpers
    function resetForm() {
        $('#popupForm')[0].reset();
        $('#popup_id').val('');
        $('#formMethod').val('POST');
        $('#imagePreviewWrap').addClass('d-none');
        $('#imagePreview').attr('src', '');
        $('#popupErrorAlert').addClass('d-none').empty();
        $('#imageRequired').show();
        $('#image').attr('required', true);
        $('#submitLabel').text('Simpan');
        toggleLinkTarget();
    }

    function toggleLinkTarget() {
        const type = $('#link_type').val();
        const $tgt = $('#link_target');
        const $hint = $('#link_target_hint');
        if (type === 'none') {
            $tgt.prop('disabled', true).val('');
            $hint.text('Pilih tipe link untuk mengaktifkan.');
        } else {
            $tgt.prop('disabled', false);
            $hint.text({
                product: 'Masukkan slug produk, contoh: kaos-polos-merah',
                page: 'Masukkan slug halaman, contoh: tentang-kami',
                url: 'Masukkan URL absolut, contoh: https://contoh.com/promo'
            }[type] || '');
        }
    }

    $('#link_type').on('change', toggleLinkTarget);

    // Image preview
    $('#image').on('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = ev => {
            $('#imagePreview').attr('src', ev.target.result);
            $('#imagePreviewWrap').removeClass('d-none');
        };
        reader.readAsDataURL(file);
    });

    // Open Add modal
    $('#btnAddPopup').on('click', function () {
        resetForm();
        $('#popupModalTitle').html('<i class="bi bi-megaphone me-2"></i>Tambah Popup');
    });

    // Open Edit modal
    $(document).on('click', '.btn-edit-popup', function () {
        const id = $(this).data('id');
        resetForm();
        $('#popupModalTitle').html('<i class="bi bi-pencil-square me-2"></i>Edit Popup');
        $.getJSON(`{{ url('admin/promo-popups') }}/${id}`, function (res) {
            if (!res.success) return;
            const d = res.data;
            $('#popup_id').val(d.id);
            $('#formMethod').val('PUT');
            $('#title').val(d.title);
            $('#link_type').val(d.link_type).trigger('change');
            $('#link_target').val(d.link_target || '');
            $('#button_label').val(d.button_label || '');
            $('#start_at').val(d.start_at || '');
            $('#end_at').val(d.end_at || '');
            $('#display_delay').val(d.display_delay ?? 0);
            $('#sort_order').val(d.sort_order ?? 0);
            $('#show_once_per_session').prop('checked', !!d.show_once_per_session);
            $('#is_active').prop('checked', !!d.is_active);
            $('#imagePreview').attr('src', d.image_url);
            $('#imagePreviewWrap').removeClass('d-none');
            $('#imageRequired').hide();
            $('#image').attr('required', false);
            $('#submitLabel').text('Perbarui');
            new bootstrap.Modal('#popupModal').show();
        }).fail(() => {
            alert('Gagal memuat data popup.');
        });
    });

    // Submit form (store / update)
    $('#popupForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#popup_id').val();
        const method = $('#formMethod').val();
        const url = id ? `{{ url('admin/promo-popups') }}/${id}` : storeUrl;

        const fd = new FormData(this);
        // Force boolean checkboxes when unchecked
        if (!$('#is_active').is(':checked')) fd.set('is_active', '0');
        if (!$('#show_once_per_session').is(':checked')) fd.set('show_once_per_session', '0');

        $('#popupErrorAlert').addClass('d-none').empty();
        $('#submitSpinner').removeClass('d-none');
        $('#btnSubmitPopup').prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override': method, 'Accept': 'application/json' },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('popupModal')).hide();
                table.ajax.reload(null, false);
                showToast(res.message || 'Berhasil', 'success');
            },
            error: function (xhr) {
                let msg = 'Terjadi kesalahan';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    msg = '<ul class="mb-0">' + Object.values(xhr.responseJSON.errors).flat().map(m => `<li>${m}</li>`).join('') + '</ul>';
                } else if (xhr.responseJSON?.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#popupErrorAlert').html(msg).removeClass('d-none');
            },
            complete: function () {
                $('#submitSpinner').addClass('d-none');
                $('#btnSubmitPopup').prop('disabled', false);
            }
        });
    });

    // Delete
    $(document).on('click', '.btn-delete-popup', function () {
        const id = $(this).data('id');
        const title = $(this).data('title');
        if (!confirm(`Hapus popup "${title}"?`)) return;

        $.ajax({
            url: `{{ url('admin/promo-popups') }}/${id}`,
            method: 'POST',
            data: { _method: 'DELETE', _token: csrf },
            headers: { 'Accept': 'application/json' },
            success: function (res) {
                table.ajax.reload(null, false);
                showToast(res.message || 'Popup dihapus', 'success');
            },
            error: function () {
                showToast('Gagal menghapus popup', 'danger');
            }
        });
    });

    function showToast(message, type) {
        const id = 'toast-' + Date.now();
        const html = `
        <div id="${id}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>`;
        let host = document.getElementById('toastHost');
        if (!host) {
            host = document.createElement('div');
            host.id = 'toastHost';
            host.className = 'toast-container position-fixed top-0 end-0 p-3';
            host.style.zIndex = 1080;
            document.body.appendChild(host);
        }
        host.insertAdjacentHTML('beforeend', html);
        const toast = new bootstrap.Toast(document.getElementById(id), { delay: 3500 });
        toast.show();
    }
});
</script>
@endsection
