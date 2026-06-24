{{-- Midtrans Payment Gateway Settings --}}
{{-- This partial is included in admin settings when the MidtransPayment module is active --}}
<div class="card" style="border:none;border-radius:12px">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <h6 class="fw-semibold mb-0"><i class="bi bi-credit-card me-2"></i>Midtrans Payment Gateway</h6>
            <span class="badge bg-success ms-2 small">Module</span>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Mode</label>
            <select name="midtrans_mode" class="form-select">
                <option value="sandbox" {{ ($settings['payment']['midtrans_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                <option value="production" {{ ($settings['payment']['midtrans_mode'] ?? '') === 'production' ? 'selected' : '' }}>Production (Live)</option>
            </select>
            <input type="hidden" name="_group[midtrans_mode]" value="payment">
            <input type="hidden" name="_type[midtrans_mode]" value="string">
            <small class="text-muted">Sandbox untuk testing, Production untuk transaksi nyata.</small>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Server Key <span class="text-danger">*</span></label>
            <input type="password" name="midtrans_server_key" class="form-control"
                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['payment']['midtrans_server_key'] ?? '') }}"
                placeholder="SB-Mid-server-... atau Mid-server-...">
            <input type="hidden" name="_group[midtrans_server_key]" value="payment">
            <input type="hidden" name="_type[midtrans_server_key]" value="string">
            <small class="text-muted">Dapatkan di <a href="https://dashboard.midtrans.com/settings/config_info"
                    target="_blank" class="fw-semibold">Midtrans Dashboard > Settings > Access Keys</a></small>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Client Key <span class="text-danger">*</span></label>
            <input type="password" name="midtrans_client_key" class="form-control"
                value="{{ config('app.demo_mode') ? 'hidden_in_demo_mode' : ($settings['payment']['midtrans_client_key'] ?? '') }}"
                placeholder="SB-Mid-client-... atau Mid-client-...">
            <input type="hidden" name="_group[midtrans_client_key]" value="payment">
            <input type="hidden" name="_type[midtrans_client_key]" value="string">
            <small class="text-muted">Digunakan untuk Snap.js di frontend. Dapatkan di dashboard yang sama.</small>
        </div>

        <div class="alert alert-info border-0 small py-2 mb-3"
            style="border-radius:10px; background:#e0f2fe; color:#0369a1;">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Notification URL:</strong> Set URL berikut di
            <a href="https://dashboard.midtrans.com/settings/vtweb_configuration" target="_blank"
                class="fw-bold text-decoration-none">Dashboard Midtrans > Settings > Payment</a>:<br>
            <code class="d-inline-block mt-1 px-2 py-1 bg-white rounded"
                style="font-size:0.8rem; color:#0369a1;">{{ url('/api/payment/midtrans/callback') }}</code>
        </div>

        <div class="alert alert-success border-0 small py-2 mb-0" style="border-radius:10px; background:#dcfce7; color:#166534;">
            <i class="bi bi-check-circle me-1"></i>
            <strong>Channel otomatis:</strong> Semua channel pembayaran yang aktif di dashboard Midtrans akan otomatis ditampilkan di halaman Snap. Tidak perlu setting channel di sini.
        </div>
    </div>
</div>