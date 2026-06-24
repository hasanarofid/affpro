@extends('install.layout')
@section('title', 'Selamat Datang')

@section('steps')
    <div class="installer-steps">
        <div class="step-item active">
            <div class="step-num">1</div>
            <span class="step-label">Cek Sistem</span>
        </div>
        <div class="step-connector"></div>
        <div class="step-item">
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
            <h3><i class="bi bi-rocket-takeoff me-2"></i>Selamat Datang di Jadiorder!</h3>
            <p>Mari periksa apakah server Anda sudah siap untuk menjalankan aplikasi.</p>
        </div>
        <div class="installer-card-body">

            {{-- Server Requirements --}}
            <h6 class="fw-bold mb-3"><i class="bi bi-cpu me-2"></i>Kebutuhan Server</h6>
            @foreach($requirements as $req)
                <div class="req-row">
                    <div>
                        <span class="req-name">{{ $req['name'] }}</span>
                        <span class="req-current ms-2">({{ $req['current'] }})</span>
                    </div>
                    @if($req['status'])
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    @else
                        <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                    @endif
                </div>
            @endforeach

            <hr class="my-4">

            {{-- Directory Permissions --}}
            <h6 class="fw-bold mb-3"><i class="bi bi-folder-check me-2"></i>Izin Direktori</h6>
            @foreach($permissions as $perm)
                <div class="req-row">
                    <span class="req-name" style="font-family: 'Courier New', monospace; font-size: 0.82rem;">{{ $perm['path'] }}</span>
                    @if($perm['writable'])
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2" style="border-radius:8px; font-weight:600;">Writable</span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2" style="border-radius:8px; font-weight:600;">Not Writable</span>
                    @endif
                </div>
            @endforeach

            <hr class="my-4">

            <div class="d-flex justify-content-end">
                @if($allPassed && $allPermissions)
                    <a href="/install/license" class="btn btn-installer">
                        Lanjutkan <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                @else
                    <div class="text-danger small fw-semibold mt-2 me-auto">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Perbaiki persyaratan yang belum terpenuhi sebelum melanjutkan.
                    </div>
                    <a href="/install" class="btn btn-outline-installer">
                        <i class="bi bi-arrow-clockwise me-1"></i> Cek Ulang
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
