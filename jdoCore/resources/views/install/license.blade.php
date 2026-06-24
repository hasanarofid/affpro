@extends('install.layout')
@section('title', 'Validasi Lisensi')

@section('steps')
    <div class="installer-steps">
        <div class="step-item completed">
            <div class="step-num"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Cek Sistem</span>
        </div>
        <div class="step-connector completed"></div>
        <div class="step-item active">
            <div class="step-num">2</div>
            <span class="step-label">Lisensi</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-item">
            <div class="step-num">3</div>
            <span class="step-label">Database</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-item">
            <div class="step-num">4</div>
            <span class="step-label">Toko</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-item">
            <div class="step-num">5</div>
            <span class="step-label">Install</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="installer-card">
        <div class="installer-card-header">
            <h3><i class="bi bi-key me-2"></i>Validasi Lisensi</h3>
            <p>Masukkan License Key yang Anda dapatkan setelah pembelian di DK-DevStore.</p>
        </div>
        <div class="installer-card-body">
            <div class="alert installer-alert alert-warning mb-4">
                <i class="bi bi-shield-lock me-2"></i>
                <strong>Keamanan:</strong> License Key akan divalidasi ke server resmi DapurKode. Pastikan
                server Anda terhubung ke internet.
            </div>

            <form action="/install/license" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label">License Key <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                        <input type="text" name="license_key" class="form-control"
                            value="{{ old('license_key') }}" placeholder="XXXX-XXXX-XXXX-XXXX" required
                            style="font-family: 'Courier New', monospace; letter-spacing: 1px;">
                    </div>
                    <small class="text-muted mt-1 d-block">
                        Dapatkan License Key di <a href="https://store.dapurkode.com"
                            target="_blank" class="text-decoration-none fw-semibold">store.dapurkode.com</a>
                    </small>
                </div>

                <div class="mb-4">
                    <label class="form-label">URL DevStore <small class="text-muted fw-normal">(opsional)</small></label>
                    <input type="url" name="devstore_url" class="form-control"
                        value="{{ old('devstore_url', 'https://store.dapurkode.com') }}"
                        placeholder="https://store.dapurkode.com">
                    <small class="text-muted mt-1 d-block">
                        Biarkan default kecuali Anda diberikan URL khusus.
                    </small>
                </div>

                <div class="p-3 rounded-4 bg-light border mb-4" style="border-style: dashed !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-shrink-0">
                            <i class="bi bi-globe2 fs-3 text-primary"></i>
                        </div>
                        <div>
                            <div class="fw-bold small">Domain Terdeteksi</div>
                            <code class="text-primary">{{ request()->getHost() }}</code>
                            <div class="text-muted" style="font-size: 0.75rem;">Domain ini akan didaftarkan
                                pada lisensi Anda.</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/install" class="btn btn-outline-installer">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-installer">
                        Validasi & Lanjut <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
