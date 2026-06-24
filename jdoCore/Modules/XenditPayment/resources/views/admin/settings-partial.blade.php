{{-- Xendit Payment Gateway Settings --}}
{{-- This partial is included in admin settings when the XenditPayment module is active --}}
<div class="card" style="border:none;border-radius:12px">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Xendit Payment Gateway</h6>
            <span class="badge bg-primary ms-2 small">Module</span>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Mode</label>
            <select name="xendit_mode" class="form-select">
                <option value="sandbox" {{ ($settings['payment']['xendit_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                <option value="live" {{ ($settings['payment']['xendit_mode'] ?? '') === 'live' ? 'selected' : '' }}>Live
                    (Produksi)</option>
            </select>
            <input type="hidden" name="_group[xendit_mode]" value="payment">
            <input type="hidden" name="_type[xendit_mode]" value="string">
            <small class="text-muted">Gunakan mode Sandbox untuk testing. Gunakan API key sesuai mode yang
                dipilih.</small>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Secret API Key <span class="text-danger">*</span></label>
            <input type="password" name="xendit_secret_key" class="form-control"
                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['payment']['xendit_secret_key'] ?? '') }}"
                placeholder="xnd_development_... atau xnd_production_...">
            <input type="hidden" name="_group[xendit_secret_key]" value="payment">
            <input type="hidden" name="_type[xendit_secret_key]" value="string">
            <small class="text-muted">Dapatkan di <a href="https://dashboard.xendit.co/settings/developers#api-keys"
                    target="_blank" class="fw-semibold">Xendit Dashboard > API Keys</a></small>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Callback Verification Token <span
                    class="text-danger">*</span></label>
            <input type="password" name="xendit_callback_token" class="form-control"
                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['payment']['xendit_callback_token'] ?? '') }}"
                placeholder="Token verifikasi callback Xendit">
            <input type="hidden" name="_group[xendit_callback_token]" value="payment">
            <input type="hidden" name="_type[xendit_callback_token]" value="string">
            <small class="text-muted">Dapatkan di <a href="https://dashboard.xendit.co/settings/developers#callbacks"
                    target="_blank" class="fw-semibold">Xendit Dashboard > Callbacks</a>. Set webhook URL ke:
                <code>{{ url('/api/payment/xendit/callback') }}</code></small>
        </div>

        <div class="alert alert-success border-0 small py-2 mb-0" style="border-radius:10px; background:#dcfce7; color:#166534;">
            <i class="bi bi-check-circle me-1"></i>
            <strong>Channel otomatis:</strong> Semua channel pembayaran yang aktif di dashboard Xendit akan otomatis ditampilkan di halaman Invoice. Tidak perlu setting channel di sini.
        </div>
    </div>
</div>