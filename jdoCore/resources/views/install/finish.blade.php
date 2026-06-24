@extends('install.layout')
@section('title', 'Konfirmasi & Install')

@section('steps')
    <div class="installer-steps">
        <div class="step-item completed">
            <div class="step-num"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Cek Sistem</span>
        </div>
        <div class="step-connector completed"></div>
        <div class="step-item completed">
            <div class="step-num"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Lisensi</span>
        </div>
        <div class="step-connector completed"></div>
        <div class="step-item completed">
            <div class="step-num"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Database</span>
        </div>
        <div class="step-connector completed"></div>
        <div class="step-item completed">
            <div class="step-num"><i class="bi bi-check-lg"></i></div>
            <span class="step-label">Toko</span>
        </div>
        <div class="step-connector completed"></div>
        <div class="step-item active">
            <div class="step-num">5</div>
            <span class="step-label">Install</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="installer-card">
        <div class="installer-card-header">
            <h3><i class="bi bi-check2-all me-2"></i>Konfirmasi & Mulai Instalasi</h3>
            <p>Periksa kembali konfigurasi Anda sebelum memulai instalasi.</p>
        </div>
        <div class="installer-card-body">

            {{-- Summary --}}
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="p-3 rounded-4 bg-light border h-100">
                        <h6 class="fw-bold small text-uppercase text-muted mb-3"><i class="bi bi-key me-1"></i> Lisensi</h6>
                        <div class="mb-1">
                            <span class="text-muted small">Key:</span>
                            <span class="fw-bold" style="font-family: 'Courier New', monospace;">{{ substr($license, 0, 8) }}****</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-4 bg-light border h-100">
                        <h6 class="fw-bold small text-uppercase text-muted mb-3"><i class="bi bi-database me-1"></i> Database</h6>
                        <div class="mb-1">
                            <span class="text-muted small">Host:</span>
                            <span class="fw-semibold">{{ $db['host'] }}:{{ $db['port'] }}</span>
                        </div>
                        <div class="mb-1">
                            <span class="text-muted small">Database:</span>
                            <span class="fw-semibold">{{ $db['database'] }}</span>
                        </div>
                        <div>
                            <span class="text-muted small">User:</span>
                            <span class="fw-semibold">{{ $db['username'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-4 bg-light border h-100">
                        <h6 class="fw-bold small text-uppercase text-muted mb-3"><i class="bi bi-shop me-1"></i> Toko</h6>
                        <div class="mb-1">
                            <span class="text-muted small">Nama:</span>
                            <span class="fw-semibold">{{ $store['store_name'] }}</span>
                        </div>
                        <div>
                            <span class="text-muted small">URL:</span>
                            <span class="fw-semibold">{{ $store['store_url'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 rounded-4 bg-light border h-100">
                        <h6 class="fw-bold small text-uppercase text-muted mb-3"><i class="bi bi-person-badge me-1"></i> Admin</h6>
                        <div class="mb-1">
                            <span class="text-muted small">Nama:</span>
                            <span class="fw-semibold">{{ $store['admin_name'] }}</span>
                        </div>
                        <div>
                            <span class="text-muted small">Email:</span>
                            <span class="fw-semibold">{{ $store['admin_email'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert installer-alert" style="background: #FFF7ED; color: #9A3412;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Perhatian:</strong> Proses instalasi akan membuat tabel database, mengimpor data awal, dan
                menghasilkan file konfigurasi. Pastikan database yang digunakan <strong>kosong</strong>.
                Proses ini mungkin memakan waktu beberapa detik.
            </div>

            {{-- Progress Area (hidden initially) --}}
            <div id="installProgress" style="display: none;">
                <div class="install-progress-bar">
                    <div class="bar" id="progressBar" style="width: 0%"></div>
                </div>
                <div id="installLog" class="p-3 rounded-4 bg-dark text-success mb-4"
                    style="font-family: 'Courier New', monospace; font-size: 0.8rem; max-height: 250px; overflow-y: auto; line-height: 1.8;">
                    <span class="text-muted">⏳ Menunggu perintah instalasi...</span>
                </div>
            </div>

            <div class="d-flex justify-content-between" id="installActions">
                <a href="/install/store" class="btn btn-outline-installer">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
                <button type="button" class="btn btn-installer btn-lg" id="btnInstall" onclick="startInstall()">
                    <i class="bi bi-play-circle me-1"></i> Mulai Instalasi
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function startInstall() {
            const btn = document.getElementById('btnInstall');
            const progress = document.getElementById('installProgress');
            const progressBar = document.getElementById('progressBar');
            const log = document.getElementById('installLog');

            // Disable button
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Menginstall...';

            // Show progress
            progress.style.display = 'block';

            // Simulate progress steps
            const steps = [
                { pct: 10, text: '📝 Membuat file konfigurasi (.env)...' },
                { pct: 20, text: '🔑 Generating Application Key...' },
                { pct: 35, text: '🗃️ Menjalankan migrasi database...' },
                { pct: 55, text: '🌱 Mengimpor data awal (seeder)...' },
                { pct: 70, text: '👤 Membuat akun administrator...' },
                { pct: 80, text: '⚙️ Mengatur konfigurasi toko...' },
                { pct: 90, text: '🔗 Membuat storage link...' },
                { pct: 95, text: '🧹 Membersihkan cache...' },
            ];

            log.innerHTML = '';
            let stepIdx = 0;

            function showNextStep() {
                if (stepIdx < steps.length) {
                    const s = steps[stepIdx];
                    progressBar.style.width = s.pct + '%';
                    log.innerHTML += s.text + '\n';
                    log.scrollTop = log.scrollHeight;
                    stepIdx++;
                    setTimeout(showNextStep, 600);
                }
            }

            showNextStep();

            // Actual AJAX call
            fetch('/install/run', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    progressBar.style.width = '100%';
                    log.innerHTML += '\n✅ <strong style="color:#4ade80;">Instalasi berhasil!</strong> Mengarahkan...\n';
                    log.scrollTop = log.scrollHeight;

                    setTimeout(() => {
                        window.location.href = '/install/success';
                    }, 2000);
                } else {
                    progressBar.style.width = '100%';
                    progressBar.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                    log.innerHTML += '\n❌ <strong style="color:#ef4444;">Gagal:</strong> ' + data.message + '\n';
                    log.scrollTop = log.scrollHeight;

                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi';
                }
            })
            .catch(err => {
                progressBar.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
                log.innerHTML += '\n❌ <strong style="color:#ef4444;">Error koneksi:</strong> ' + err.message + '\n';
                log.scrollTop = log.scrollHeight;

                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Coba Lagi';
            });
        }
    </script>
@endsection
