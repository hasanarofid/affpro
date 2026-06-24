@php
    $settings = app(\App\Services\SettingService::class);
    $primaryColor = $settings->primaryColor();
    $secondaryColor = $settings->secondaryColor();
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login OTP — {{ $settings->storeName() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    0%,
                    {{ $secondaryColor }}
                    50%, #0f172a 100%);
        }

        .otp-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }

        .form-control {
            border-radius: 10px;
            padding: .65rem 1rem;
        }

        .form-control:focus {
            border-color:
                {{ $primaryColor }}
            ;
            box-shadow: 0 0 0 3px
                {{ $primaryColor }}
                33;
        }

        .btn-otp {
            background: linear-gradient(135deg,
                    {{ $primaryColor }}
                    ,
                    {{ $secondaryColor }}
                );
            border: none;
            border-radius: 12px;
            padding: .7rem;
            font-weight: 600;
            transition: transform .15s;
        }

        .btn-otp:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px
                {{ $primaryColor }}
                55;
        }

        .otp-input {
            letter-spacing: 8px;
            font-size: 1.5rem;
            text-align: center;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="otp-card" x-data="otpLogin()">
        <div class="text-center mb-4">
            <h4 class="fw-bold" style="color:{{ $primaryColor }}">
                <i class="bi bi-whatsapp"></i> Login via OTP
            </h4>
            <p class="text-muted small mb-0">Masuk menggunakan nomor WhatsApp</p>
        </div>

        <div class="alert py-2 small" style="border-radius:10px" x-show="message"
            :class="success ? 'alert-success' : 'alert-danger'" x-text="message" x-cloak></div>

        <!-- Step 1: Phone -->
        <div x-show="step === 1">
            <div class="mb-3">
                <label class="form-label small fw-medium">Nomor WhatsApp</label>
                <input type="tel" x-model="phone" class="form-control" placeholder="08xxxxxxxxxx" maxlength="15"
                    @keyup.enter="sendOtp()">
            </div>
            <button class="btn btn-otp btn-primary w-100" @click="sendOtp()" :disabled="loading">
                <span x-show="!loading"><i class="bi bi-send me-1"></i> Kirim Kode OTP</span>
                <span x-show="loading"><span class="spinner-border spinner-border-sm me-1"></span> Mengirim...</span>
            </button>
        </div>

        <!-- Step 2: Verify OTP -->
        <div x-show="step === 2" x-cloak>
            <p class="text-muted small text-center mb-3">Kode OTP telah dikirim ke <strong x-text="phone"></strong></p>
            <div class="mb-3">
                <input type="text" x-model="code" class="form-control otp-input" placeholder="______" maxlength="6"
                    @keyup.enter="verifyOtp()">
            </div>
            <button class="btn btn-otp btn-primary w-100 mb-2" @click="verifyOtp()" :disabled="loading">
                <span x-show="!loading"><i class="bi bi-check-lg me-1"></i> Verifikasi</span>
                <span x-show="loading"><span class="spinner-border spinner-border-sm me-1"></span>
                    Memverifikasi...</span>
            </button>
            <div class="text-center">
                <button class="btn btn-sm btn-link text-muted" @click="step=1; message=''" :disabled="countdown > 0">
                    <span x-show="countdown > 0">Kirim ulang (<span x-text="countdown"></span>s)</span>
                    <span x-show="countdown <= 0">Kirim ulang kode</span>
                </button>
            </div>
        </div>

        <div class="text-center mt-3">
            <small class="text-muted">Atau <a href="{{ route('login') }}"
                    style="color:{{ $primaryColor }};font-weight:600;text-decoration:none">masuk dengan
                    password</a></small>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <script>
        function otpLogin() {
            return {
                step: 1, phone: '', code: '', loading: false,
                message: '', success: false, countdown: 0,
                async sendOtp() {
                    if (!this.phone || this.phone.length < 10) { this.message = 'Masukkan nomor telepon yang valid'; this.success = false; return; }
                    this.loading = true; this.message = '';
                    try {
                        const res = await fetch('{{ route("otp.send") }}', {
                            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ phone: this.phone })
                        });
                        const data = await res.json();
                        if (data.success) {
                            this.step = 2; this.message = data.message; this.success = true;
                            if (data.debug_code) this.message += ' [DEV: ' + data.debug_code + ']';
                            this.startCountdown();
                        } else { this.message = data.message || 'Gagal mengirim OTP'; this.success = false; }
                    } catch (e) { this.message = 'Terjadi kesalahan'; this.success = false; }
                    this.loading = false;
                },
                async verifyOtp() {
                    if (!this.code || this.code.length < 6) { this.message = 'Masukkan 6 digit kode OTP'; this.success = false; return; }
                    this.loading = true; this.message = '';
                    try {
                        const res = await fetch('{{ route("otp.verify") }}', {
                            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({ phone: this.phone, code: this.code })
                        });
                        const data = await res.json();
                        if (data.success) { this.message = data.message; this.success = true; window.location.href = data.redirect; }
                        else { this.message = data.message || 'Kode OTP salah'; this.success = false; }
                    } catch (e) { this.message = 'Terjadi kesalahan'; this.success = false; }
                    this.loading = false;
                },
                startCountdown() {
                    this.countdown = 60;
                    const timer = setInterval(() => { this.countdown--; if (this.countdown <= 0) clearInterval(timer); }, 1000);
                }
            }
        }
    </script>
</body>

</html>