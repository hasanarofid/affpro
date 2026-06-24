@extends('theme::layouts.app')
@section('title', 'Pembayaran Pesanan')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="mb-4">
                    <a href="{{ route('orders.track', $order->order_number) }}"
                        class="text-decoration-none text-muted small hover-primary d-inline-flex align-items-center">
                        <i class="bi bi-arrow-left me-2"></i> Kembali ke Pesanan
                    </a>
                </div>
                <!-- Header Section -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3"
                        style="width: 80px; height: 80px;">
                        <i class="bi bi-wallet2 fs-1 text-primary"></i>
                    </div>
                    <h3 class="fw-bold text-dark">Langkah Pembayaran</h3>
                    <p class="text-muted">Selesaikan pembayaran Anda untuk memproses pesanan</p>
                </div>

                <!-- Order Total Card -->
                <div class="card border-0 shadow-sm mb-4 overflow-hidden"
                    style="border-radius: 20px; background: linear-gradient(135deg, #ffffff 0%, #f9fbfd 100%);">
                    <div class="card-body p-4 text-center">
                        <span class="text-muted small text-uppercase fw-bold tracking-wider">Total yang Harus Dibayar</span>
                        <h2 class="fw-bold mt-2 mb-0" style="color: var(--primary); font-size: 2.5rem;">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </h2>
                        <div class="mt-3 d-inline-block px-3 py-1 bg-white border rounded-pill shadow-sm small">
                            <span class="text-muted">Order ID: </span>
                            <span class="fw-bold text-dark">#{{ $order->order_number }}</span>
                        </div>
                    </div>
                </div>

                <!-- Bank Accounts Card -->
                @if(count($bankAccounts) > 0)
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <div class="bg-light rounded-circle p-2 me-2">
                                    <i class="bi bi-bank2 text-primary"></i>
                                </div>
                                <h6 class="fw-bold mb-0">Tujuan Transfer</h6>
                            </div>

                            <div class="row g-3">
                                @foreach($bankAccounts as $bank)
                                    <div class="col-12">
                                        <div class="p-3 border rounded-3 bg-light bg-opacity-50 position-relative group-hover">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <div class="fw-bold text-uppercase small text-primary mb-1">
                                                        {{ $bank['bank'] ?? 'BANK' }}
                                                    </div>
                                                    <div class="fs-4 fw-bold text-dark family-monospace"
                                                        id="acc-{{ $loop->index }}">{{ $bank['account_number'] ?? '' }}</div>
                                                    <div class="text-muted small mt-1">a.n. <span
                                                            class="fw-semibold text-dark">{{ $bank['account_name'] ?? '' }}</span>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-copy-custom shadow-sm border"
                                                    onclick="copyText('acc-{{ $loop->index }}', this)"
                                                    style="border-radius: 8px; font-weight: 600;">
                                                    <i class="bi bi-files"></i> Salin
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div
                                class="mt-4 p-3 rounded-3 bg-warning bg-opacity-10 border border-warning border-opacity-20 d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-3 fs-4"></i>
                                <div class="small">
                                    Lakukan transfer tepat hingga <strong>3 digit terakhir</strong> jika ada (kode unik) untuk
                                    verifikasi otomatis.
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @php
                    $lastPayment = $order->payments->last();
                @endphp

                <!-- Existing Proof if any -->
                @if($lastPayment && $lastPayment->proof_image)
                    <div class="card border-0 shadow-sm mb-4"
                        style="border-radius: 20px; border: 2px dashed #10b981 !important; background: #f0fdf4;">
                        <div class="card-body p-4 text-center">
                            <div class="small fw-bold text-success mb-3">
                                <i class="bi bi-check-circle-fill me-1"></i> BUKTI TRANSFER TERUPLOAD
                            </div>
                            <img src="{{ asset($lastPayment->proof_image) }}" class="img-fluid rounded shadow-sm mb-2"
                                style="max-height: 250px; border: 3px solid white;">
                            <div class="mt-2 text-muted small lh-sm">
                                Pesanan Anda sedang menunggu verifikasi admin.<br>
                                <span class="fw-bold">Ingin ganti bukti?</span> Silakan upload ulang di bawah.
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Upload Card -->
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-light rounded-circle p-2 me-2">
                                <i class="bi bi-cloud-arrow-up text-primary"></i>
                            </div>
                            <h6 class="fw-bold mb-0">Konfirmasi Pembayaran</h6>
                        </div>

                        <form action="{{ route('orders.uploadPayment', $order->order_number) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="upload-container mb-4 text-center p-5 border-2 border-dashed rounded-4 bg-light position-relative"
                                style="cursor: pointer; border-style: dashed !important;"
                                onclick="document.getElementById('proof-input').click()">
                                <input type="file" name="proof" id="proof-input" class="d-none" accept="image/*" required
                                    onchange="previewImage(this)">

                                <div id="upload-placeholder">
                                    <div class="mb-3 text-primary" style="font-size: 3rem;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Pilih Bukti Transfer</h6>
                                    <p class="text-muted small mb-0">Klik untuk upload foto (Format JPG, PNG, Max 2MB)</p>
                                </div>

                                <div id="image-preview" class="d-none">
                                    <img src="" class="img-fluid rounded shadow-sm mb-3" style="max-height: 200px;">
                                    <div class="text-primary small fw-bold">Ketuk untuk ganti foto</div>
                                </div>
                            </div>

                            @error('proof')
                                <div class="alert alert-danger py-2 small mb-4" style="border-radius: 10px;">{{ $message }}
                                </div>
                            @enderror

                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm"
                                style="border-radius: 12px; font-size: 1.1rem;">
                                <i class="bi bi-check2-circle me-2"></i> Konfirmasi Sekarang
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('orders.track', $order->order_number) }}"
                        class="text-decoration-none text-muted small hover-primary">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Status Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .tracking-wider {
                letter-spacing: 0.05em;
            }

            .family-monospace {
                font-family: 'JetBrains Mono', 'Courier New', monospace;
            }

            .btn-primary {
                background: linear-gradient(45deg, var(--primary), #10b981);
                border: none;
            }

            .btn-primary:hover {
                opacity: 0.9;
                transform: translateY(-1px);
            }

            .upload-container {
                transition: all 0.3s ease;
            }

            .upload-container:hover {
                background-color: #f1f5f9 !important;
                border-color: var(--primary) !important;
            }

            .hover-primary:hover {
                color: var(--primary) !important;
            }

            .btn-copy-custom {
                transition: all 0.2s ease;
                color: var(--primary) !important;
                background-color: white !important;
                border-color: #e9ecef !important;
            }

            .btn-copy-custom:hover {
                background-color: #f8f9fa !important;
                border-color: var(--primary) !important;
                transform: translateY(-1px);
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            function copyText(elementId, btn) {
                const text = document.getElementById(elementId).innerText.trim();

                navigator.clipboard.writeText(text).then(() => {
                    const originalContent = btn.innerHTML;

                    // Set success state with primary color
                    btn.innerHTML = '<i class="bi bi-check-all"></i> Tersalin!';
                    btn.style.color = '#10b981'; // Using a nice success green but custom
                    btn.style.borderColor = '#10b981';

                    setTimeout(() => {
                        btn.innerHTML = originalContent;
                        btn.style.color = 'var(--primary)';
                        btn.style.borderColor = '';
                    }, 2000);
                });
            }

            function previewImage(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const placeholder = document.getElementById('upload-placeholder');
                        const previewDiv = document.getElementById('image-preview');
                        const previewImg = previewDiv.querySelector('img');

                        placeholder.classList.add('d-none');
                        previewDiv.classList.remove('d-none');
                        previewImg.src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>
    @endpush
@endsection