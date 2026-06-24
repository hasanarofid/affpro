{{-- iPaymu Payment Gateway Settings --}}
<div class="card" style="border:none;border-radius:12px">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-credit-card-2-front me-2"></i>iPaymu Payment Gateway</h6>
            <span class="badge bg-warning text-dark ms-2 small">Module</span>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Mode</label>
            <select name="ipaymu_mode" class="form-select">
                <option value="sandbox" {{ ($settings['payment']['ipaymu_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                <option value="production" {{ ($settings['payment']['ipaymu_mode'] ?? '') === 'production' ? 'selected' : '' }}>Production (Live)</option>
            </select>
            <input type="hidden" name="_group[ipaymu_mode]" value="payment">
            <input type="hidden" name="_type[ipaymu_mode]" value="string">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">VA <span class="text-danger">*</span></label>
            <input type="text" name="ipaymu_va" class="form-control"
                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['payment']['ipaymu_va'] ?? '') }}"
                placeholder="Virtual Account iPaymu">
            <input type="hidden" name="_group[ipaymu_va]" value="payment">
            <input type="hidden" name="_type[ipaymu_va]" value="string">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">API Key <span class="text-danger">*</span></label>
            <input type="password" name="ipaymu_api_key" class="form-control"
                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['payment']['ipaymu_api_key'] ?? '') }}"
                placeholder="API Key iPaymu">
            <input type="hidden" name="_group[ipaymu_api_key]" value="payment">
            <input type="hidden" name="_type[ipaymu_api_key]" value="string">
            <small class="text-muted">Pastikan VA dan API Key sesuai mode sandbox/production.</small>
        </div>

        <div class="alert alert-info border-0 small py-2 mb-3" style="border-radius:10px; background:#e0f2fe; color:#0369a1;">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Notify URL:</strong>
            <code class="d-inline-block mt-1 px-2 py-1 bg-white rounded" style="font-size:0.8rem; color:#0369a1;">{{ url('/api/payment/ipaymu/callback') }}</code>
            <div class="mt-2">Daftarkan domain callback dan return di dashboard iPaymu.</div>
        </div>

        <div class="alert alert-success border-0 small py-2 mb-0" style="border-radius:10px; background:#dcfce7; color:#166534;">
            <i class="bi bi-check-circle me-1"></i>
            <strong>Channel otomatis:</strong> Semua channel pembayaran yang aktif di dashboard iPaymu akan otomatis ditampilkan. Tidak perlu setting channel di sini.
        </div>
    </div>
</div>