<div class="row g-4">
    <div class="col-lg-8">
        <div class="card" style="border:none;border-radius:12px">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Judul Artikel <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $post->title ?? '') }}" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">Konten <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-sm btn-outline-info" id="btn-generate-ai" data-type="blog" style="border-radius:6px; font-size:0.75rem">
                            <i class="bi bi-stars me-1"></i> Generate Konten AI
                        </button>
                    </div>
                    <textarea name="content" id="editor-content" class="form-control tinymce-editor @error('content') is-invalid @enderror" rows="12" required>{{ old('content', $post->content ?? '') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card" style="border:none;border-radius:12px">
            <div class="card-body">
                <h6 class="fw-semibold mb-3">Pengaturan</h6>
                <div class="mb-3">
                    <label class="form-label">Gambar Cover</label>
                    @if(isset($post) && $post->featured_image)
                    <div class="mb-2">
                        <img src="{{ asset($post->featured_image) }}" class="rounded" style="width:100%;max-height:150px;object-fit:cover">
                    </div>
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $post->meta_title ?? '') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Meta Description</label>
                    <textarea name="meta_description" class="form-control" rows="2">{{ old('meta_description', $post->meta_description ?? '') }}</textarea>
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="isPub"
                        {{ old('is_published', $post->is_published ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="isPub">Published</label>
                </div>
                <button type="submit" class="btn btn-primary w-100" style="border-radius:10px">
                    <i class="bi bi-check-lg me-1"></i> {{ isset($post) ? 'Simpan' : 'Publikasikan' }}
                </button>
                <a href="{{ route('admin.blog.index') }}" class="btn btn-outline-secondary w-100 mt-2" style="border-radius:10px">Batal</a>
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
            inputLabel: 'Tuliskan perintah/topik (contoh: "Tulis artikel 3 paragraf tentang gamis lebaran")',
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
