{{-- Duitku Payment Gateway Settings --}}
{{-- This partial is included in admin settings when the DuitkuPayment module is active --}}
<div class="card" style="border:none;border-radius:12px">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-wallet me-2"></i>Duitku Payment Gateway</h6>
            <span class="badge bg-info ms-2 small text-white">Module</span>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Mode</label>
            <select name="duitku_mode" class="form-select">
                <option value="sandbox" {{ ($settings['payment']['duitku_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                <option value="production" {{ ($settings['payment']['duitku_mode'] ?? '') === 'production' ? 'selected' : '' }}>Production (Live)</option>
            </select>
            <input type="hidden" name="_group[duitku_mode]" value="payment">
            <input type="hidden" name="_type[duitku_mode]" value="string">
            <small class="text-muted">Sandbox untuk testing, Production untuk transaksi nyata.</small>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Merchant Code <span class="text-danger">*</span></label>
            <input type="text" name="duitku_merchant_code" class="form-control"
                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['payment']['duitku_merchant_code'] ?? '') }}"
                placeholder="D0xxx">
            <input type="hidden" name="_group[duitku_merchant_code]" value="payment">
            <input type="hidden" name="_type[duitku_merchant_code]" value="string">
            <small class="text-muted">Dapatkan di <a href="https://dashboard.duitku.com" target="_blank"
                    class="fw-semibold">Dashboard Duitku > Proyek</a></small>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Merchant Key / API Key <span class="text-danger">*</span></label>
            <input type="password" name="duitku_merchant_key" class="form-control"
                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['payment']['duitku_merchant_key'] ?? '') }}"
                placeholder="Merchant Key dari dashboard Duitku">
            <input type="hidden" name="_group[duitku_merchant_key]" value="payment">
            <input type="hidden" name="_type[duitku_merchant_key]" value="string">
            <small class="text-muted">Dapatkan di Dashboard Duitku > Proyek > API Keys.</small>
        </div>

        <div class="alert alert-info border-0 small py-2 mb-3"
            style="border-radius:10px; background:#e0f2fe; color:#0369a1;">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Callback URL:</strong> Set URL berikut di
            <a href="https://dashboard.duitku.com" target="_blank" class="fw-bold text-decoration-none">Dashboard Duitku
                > Proyek > Callback</a>:<br>
            <code class="d-inline-block mt-1 px-2 py-1 bg-white rounded"
                style="font-size:0.8rem; color:#0369a1;">{{ url('/api/payment/duitku/callback') }}</code>
        </div>

        <div class="alert alert-success border-0 small py-2 mb-0" style="border-radius:10px; background:#dcfce7; color:#166534;">
            <i class="bi bi-check-circle me-1"></i>
            <strong>Channel otomatis:</strong> Semua channel pembayaran yang aktif di dashboard Duitku akan otomatis ditampilkan di halaman Pop. Tidak perlu setting channel di sini.
        </div>
    </div>
</div>