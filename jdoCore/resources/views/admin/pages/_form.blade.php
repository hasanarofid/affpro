<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card" style="border:none;border-radius:12px">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Judul Halaman <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $page->title ?? '') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Kategori Tampilan</label>
                    <select name="category" class="form-select @error('category') is-invalid @enderror">
                        <option value="lainnya" {{ old('category', $page->category ?? 'lainnya') == 'lainnya' ? 'selected' : '' }}>Lainnya (Tidak tampil di Footer)</option>
                        <option value="footer_navigasi" {{ old('category', $page->category ?? '') == 'footer_navigasi' ? 'selected' : '' }}>Tampil di Footer (Kolom Navigasi)</option>
                    </select>
                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Konten <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-sm btn-outline-info" id="btn-generate-ai" data-type="page" style="border-radius:6px; font-size:0.75rem">
                            <i class="bi bi-stars me-1"></i> Generate Konten AI
                        </button>
                    </div>
                    <textarea name="content" id="editor-content" class="form-control tinymce-editor @error('content') is-invalid @enderror" rows="15" required>{{ old('content', $page->content ?? '') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive"
                        {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isActive">Aktif</label>
                </div>
                <button type="submit" class="btn btn-primary px-4" style="border-radius:10px">
                    <i class="bi bi-check-lg me-1"></i> {{ isset($page) ? 'Simpan' : 'Buat Halaman' }}
                </button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary ms-2" style="border-radius:10px">Batal</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#btn-generate-ai').click(function() {
        let type = $(this).data('type');
        
        Swal.fire({
            title: 'Generate dengan AI',
            input: 'textarea',
            inputLabel: 'Tuliskan perintah/topik (contoh: "Buatkan halaman Tentang Kami yang profesional")',
            inputPlaceholder: 'Tuliskan perintah Anda di sini...',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-magic me-1"></i> Generate',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: (prompt) => {
                if (!prompt) {
                    Swal.showValidationMessage('Perintah tidak boleh kosong');
                    return false;
                }
                
                return $.ajax({
                    url: '{{ route("admin.ai.generate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        prompt: prompt,
                        type: type
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
                let descField = $('#editor-content');
                if (typeof tinymce !== 'undefined' && tinymce.get('editor-content')) {
                    tinymce.get('editor-content').setContent(result.value.content);
                } else {
                    descField.val(result.value.content);
                }
                
                let notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });
                notyf.success('Konten berhasil dibuat oleh AI!');
            }
        });
    });
});
</script>
@endpush
