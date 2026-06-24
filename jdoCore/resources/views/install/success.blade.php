@extends('install.layout')
@section('title', 'Instalasi Berhasil!')

@section('styles')
    <style>
        @keyframes confettiFall {
            0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
            100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
        }
        .confetti {
            position: fixed;
            top: -10px;
            z-index: 0;
            pointer-events: none;
        }
        .confetti span {
            position: absolute;
            display: block;
            width: 8px;
            height: 8px;
            border-radius: 2px;
            animation: confettiFall linear forwards;
        }
        .success-icon-ring {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 20px 50px rgba(16, 185, 129, 0.3);
            position: relative;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }
        .success-icon-ring::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            border: 3px dashed rgba(16, 185, 129, 0.3);
            animation: spinRotate 15s linear infinite;
        }
        @keyframes spinRotate {
            100% { transform: rotate(360deg); }
        }
        @keyframes popIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
@endsection

@section('content')
    {{-- Confetti --}}
    <div class="confetti" id="confettiContainer"></div>

    <div class="installer-card">
        <div class="installer-card-body text-center py-5">
            <div class="mb-4">
                <div class="success-icon-ring mx-auto">
                    <i class="bi bi-check-lg text-white" style="font-size: 3.5rem;"></i>
                </div>
            </div>
            <h3 class="fw-bolder mb-2" style="font-size: 1.8rem;">Instalasi Berhasil! 🎉</h3>
            <p class="text-muted mb-4" style="max-width: 450px; margin: 0 auto; line-height: 1.6;">
                Aplikasi <strong>JadiOrder</strong> Anda telah berhasil diinstall dan siap digunakan.<br>
                Silakan login ke panel admin untuk mulai mengelola toko Anda.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
                <a href="/admin" class="btn btn-installer btn-lg px-5">
                    <i class="bi bi-speedometer2 me-2"></i> Buka Panel Admin
                </a>
                <a href="/" class="btn btn-outline-installer btn-lg px-4">
                    <i class="bi bi-shop me-2"></i> Lihat Toko
                </a>
            </div>

            <div class="mt-5 p-4 rounded-4 bg-light border mx-auto" style="max-width: 500px; border-style: dashed !important;">
                <div class="text-uppercase small fw-bolder text-muted mb-2" style="letter-spacing: 1.5px; font-size: 0.7rem;">Tips Selanjutnya</div>
                <ul class="list-unstyled text-start text-muted small mb-0" style="line-height: 2;">
                    <li><i class="bi bi-check2 text-success me-2"></i>Atur <strong>Pengaturan Toko</strong> lengkap di menu Settings</li>
                    <li><i class="bi bi-check2 text-success me-2"></i>Upload <strong>produk pertama</strong> Anda</li>
                    <li><i class="bi bi-check2 text-success me-2"></i>Konfigurasi <strong>Payment Gateway</strong> agar bisa terima pembayaran</li>
                    <li><i class="bi bi-check2 text-success me-2"></i>Pasang <strong>Tema & Modul</strong> dari DK-DevStore</li>
                    <li><i class="bi bi-check2 text-success me-2"></i>Hubungkan <strong>WhatsApp API</strong> untuk notifikasi otomatis</li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Simple confetti effect
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.getElementById('confettiContainer');
            const colors = ['#4F46E5', '#7C3AED', '#10B981', '#F59E0B', '#EF4444', '#EC4899', '#06B6D4'];

            for (let i = 0; i < 60; i++) {
                const span = document.createElement('span');
                span.style.left = Math.random() * 100 + 'vw';
                span.style.background = colors[Math.floor(Math.random() * colors.length)];
                span.style.animationDuration = (Math.random() * 2 + 2) + 's';
                span.style.animationDelay = Math.random() * 3 + 's';
                span.style.width = (Math.random() * 6 + 4) + 'px';
                span.style.height = (Math.random() * 6 + 4) + 'px';
                container.appendChild(span);
            }
        });
    </script>
@endsection
