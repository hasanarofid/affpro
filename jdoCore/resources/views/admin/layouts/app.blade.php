<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ app(\App\Services\SettingService::class)->get('store_name') ?? 'Jadiorder' }}
    </title>

    @php
        $favicon = app(\App\Services\SettingService::class)->get('store_favicon');
    @endphp
    @if($favicon)
        <link rel="icon" type="image/png" href="{{ asset($favicon) }}">
    @else
        <link rel="icon" type="image/svg+xml"
            href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>⚡</text></svg>">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS (CDN for shared hosting) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Notyf -->
    <link href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" rel="stylesheet">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --primary:
                {{ app(\App\Services\SettingService::class)->primaryColor() }}
            ;
            --secondary:
                {{ app(\App\Services\SettingService::class)->secondaryColor() }}
            ;
        }
    </style>

    @yield('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-app.css?v=' . time()) }}">
</head>

<body>
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="brand ps-4 pe-3 position-relative">
            @php
                $logo = app(\App\Services\SettingService::class)->get('store_logo');
            @endphp
            @if($logo)
                <img src="{{ asset($logo) }}" alt="Logo"
                    style="max-height: 38px; margin-right: 12px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            @else
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-2 shadow-sm"
                        style="width: 32px; height: 32px;">
                        <i class="bi bi-hexagon-fill fs-6"></i>
                    </div>
                    <span class="brand-text">
                        {{ app(\App\Services\SettingService::class)->storeName() }}
                    </span>
                </div>
            @endif
            <button class="btn btn-sm btn-light d-lg-none position-absolute" onclick="toggleSidebar()" style="right: 15px; top: 50%; transform: translateY(-50%); z-index: 10; padding: 0.25rem 0.5rem;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="py-3">
            <div class="nav-section">{{ __('admin.menu.products') }}</div>
            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> {{ __('general.dashboard') }}
            </a>
            <a href="{{ route('admin.products.index') }}"
                class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> {{ __('admin.menu.products') }}
            </a>
            <a href="{{ route('admin.categories.index') }}"
                class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-grid"></i> {{ __('admin.menu.categories') }}
            </a>
            <a href="{{ route('admin.brands.index') }}"
                class="nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
                <i class="bi bi-tag"></i> {{ __('admin.menu.brands') }}
            </a>

            <div class="nav-section">{{ __('admin.menu.orders') }}</div>
            <a href="{{ route('admin.orders.index') }}"
                class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> {{ __('admin.menu.orders') }}
            </a>
            <a href="{{ route('admin.pos.index') }}"
                class="nav-link {{ request()->routeIs('admin.pos.*') ? 'active' : '' }}">
                <i class="bi bi-shop-window"></i> POS / Kasir
            </a>
            <a href="{{ route('admin.vouchers.index') }}"
                class="nav-link {{ request()->routeIs('admin.vouchers.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-perforated"></i> Voucher
            </a>
            <a href="{{ route('admin.flash-sales.index') }}"
                class="nav-link {{ request()->routeIs('admin.flash-sales.*') ? 'active' : '' }}">
                <i class="fas fa-bolt fw-bold"></i> Flash Sale
            </a>

            <div class="nav-section">📊 Marketing</div>
            <a href="{{ route('admin.marketing.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.marketing.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer"></i> Dashboard
            </a>
            <a href="{{ route('admin.marketing.visitors') }}"
                class="nav-link {{ request()->routeIs('admin.marketing.visitors') ? 'active' : '' }}">
                <i class="bi bi-eye"></i> Pengunjung
            </a>
            <a href="{{ route('admin.marketing.traffic_sources') }}"
                class="nav-link {{ request()->routeIs('admin.marketing.traffic_sources') ? 'active' : '' }}">
                <i class="bi bi-globe2"></i> Sumber Traffic
            </a>
            <a href="{{ route('admin.marketing.abandoned_carts') }}"
                class="nav-link {{ request()->routeIs('admin.marketing.abandoned_carts') ? 'active' : '' }}">
                <i class="bi bi-cart-x"></i> Abandon Cart
            </a>
            <a href="{{ route('admin.marketing.customer_insights') }}"
                class="nav-link {{ request()->routeIs('admin.marketing.customer_insights') ? 'active' : '' }}">
                <i class="bi bi-person-check"></i> Insight User
            </a>
            <a href="{{ route('admin.marketing.product_analytics') }}"
                class="nav-link {{ request()->routeIs('admin.marketing.product_analytics') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line"></i> Analitik Produk
            </a>

            <div class="nav-section">Laporan</div>
            <a href="{{ route('admin.reports.transactions') }}"
                class="nav-link {{ request()->routeIs('admin.reports.transactions') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Transaksi All
            </a>
            <a href="{{ route('admin.reports.users') }}"
                class="nav-link {{ request()->routeIs('admin.reports.users') ? 'active' : '' }}">
                <i class="bi bi-person-lines-fill"></i> Transaksi User
            </a>
            <a href="{{ route('admin.reports.profit_loss') }}"
                class="nav-link {{ request()->routeIs('admin.reports.profit_loss') ? 'active' : '' }}">
                <i class="bi bi-graph-up"></i> Laba Rugi
            </a>
            <a href="{{ route('admin.reports.products') }}"
                class="nav-link {{ request()->routeIs('admin.reports.products') ? 'active' : '' }}">
                <i class="bi bi-cart-check"></i> Penjualan Produk
            </a>

            <div class="nav-section">Pelanggan</div>
            <a href="{{ route('admin.chat.index') }}"
                class="nav-link {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i> Pesan
                @php
                    $unreadChatCount = \App\Models\Message::whereHas('conversation')
                        ->where('sender_id', '!=', auth()->id())
                        ->where('is_read', false)
                        ->count();
                @endphp
                @if($unreadChatCount > 0)
                    <span class="badge bg-danger ms-auto rounded-pill">{{ $unreadChatCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.users.index') }}"
                class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Data Pelanggan
            </a>
            <a href="{{ route('admin.wallets.requests') }}"
                class="nav-link {{ request()->routeIs('admin.wallets.requests') ? 'active' : '' }}">
                <i class="bi bi-receipt"></i> Transaksi Saldo
            </a>
            <a href="{{ route('admin.wallets.index') }}"
                class="nav-link {{ request()->routeIs('admin.wallets.index') || request()->routeIs('admin.wallets.show') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> Saldo Pelanggan
            </a>

            <div class="nav-section">Konten</div>
            <a href="{{ route('admin.banners.index') }}"
                class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                <i class="bi bi-image"></i> {{ __('admin.menu.banners') }}
            </a>
            <a href="{{ route('admin.promo-popups.index') }}"
                class="nav-link {{ request()->routeIs('admin.promo-popups.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone"></i> Popup Promo
            </a>
            <a href="{{ route('admin.blog.index') }}"
                class="nav-link {{ request()->routeIs('admin.blog.*') ? 'active' : '' }}">
                <i class="bi bi-journal-text"></i> Blog
            </a>
            <a href="{{ route('admin.pages.index') }}"
                class="nav-link {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Halaman
            </a>

            <div class="nav-section">Sistem</div>
            @if(auth()->user()->hasRole('superadmin'))
                <a href="{{ route('admin.settings.index') }}"
                    class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> {{ __('admin.menu.settings') }}
                </a>
                <a href="{{ route('admin.administrators.index') }}"
                    class="nav-link {{ request()->routeIs('admin.administrators.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-lock"></i> Kelola Admin
                </a>
            @endif
            <a href="{{ route('admin.themes.index') }}"
                class="nav-link {{ request()->routeIs('admin.themes.*') ? 'active' : '' }}">
                <i class="bi bi-palette2"></i> Tema
            </a>
            <a href="{{ route('admin.modules.index') }}"
                class="nav-link {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}">
                <i class="bi bi-puzzle"></i> Modul
            </a>
            <a href="{{ route('admin.devstore.index') }}"
                class="nav-link {{ request()->routeIs('admin.devstore.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i> DK-DevStore
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <span class="toggle-btn me-3 d-lg-none"
                onclick="toggleSidebar()">
                <i class="bi bi-list"></i>
            </span>
            <h6 class="mb-0 fw-semibold text-muted">@yield('page-title', 'Dashboard')</h6>
            <div class="ms-auto d-flex align-items-center gap-3">
                <button type="button" class="btn btn-sm btn-light border-0 d-flex align-items-center gap-2"
                    id="theme-toggle" aria-label="Toggle theme"
                    style="background: transparent; font-size: 0.9rem; font-weight: 500; color: var(--text-muted); padding: 5px 10px;">
                    <i class="bi bi-moon-stars-fill" id="theme-icon"></i>
                    <span id="theme-text">Mode Gelap</span>
                </button>
                <a href="{{ route('home') }}" class="text-muted" target="_blank" title="Lihat Toko">
                    <i class="bi bi-box-arrow-up-right"></i>
                </a>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> {{ Auth::user()->name ?? 'Admin' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if(!$demoMode)
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal"
                                    data-bs-target="#profileModal">
                                    <i class="bi bi-person me-2"></i>Edit Profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" data-bs-toggle="modal"
                                    data-bs-target="#passwordModal">
                                    <i class="bi bi-shield-lock me-2"></i>Ubah Password
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                        @endif
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i>{{ __('general.logout') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 px-3">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($demoMode ?? false)
                <div class="alert alert-warning d-flex align-items-center mb-4"
                    style="border-radius:12px; border: 1px solid rgba(255,193,7,0.3); background: rgba(255,193,7,0.08);">
                    <i class="bi bi-shield-exclamation fs-4 me-3 text-warning"></i>
                    <div>
                        <strong>Mode Demo Aktif</strong>
                        <div class="small text-muted mt-1">Beberapa fitur seperti pengaturan, kelola admin, upload tema &
                            modul dibatasi dalam mode demo.</div>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="profileForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="profileErrors"></div>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" id="user-name" class="form-control" required
                                value="{{ Auth::check() ? Auth::user()->name : '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="user-email" class="form-control" required
                                value="{{ Auth::check() ? Auth::user()->email : '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="phone" id="user-phone" class="form-control"
                                value="{{ Auth::check() ? Auth::user()->phone : '' }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveProfile">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="passwordForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="passwordErrors"></div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" required
                                minlength="8">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSavePassword">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Notyf -->
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3/dist/cdn.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- TinyMCE WYSIWYG Editor (self-hosted, no API key needed) -->
    <script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof tinymce !== 'undefined' && document.querySelectorAll('.tinymce-editor').length > 0) {
                tinymce.init({
                    selector: '.tinymce-editor',
                    height: 400,
                    menubar: 'file edit view insert format tools table',
                    plugins: [
                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                        'insertdatetime', 'media', 'table', 'help', 'wordcount'
                    ],
                    toolbar: 'undo redo | blocks | bold italic forecolor backcolor | ' +
                        'alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist outdent indent | link image media | ' +
                        'removeformat | code fullscreen | help',
                    content_style: 'body { font-family: "Plus Jakarta Sans", sans-serif; font-size: 14px; }',
                    branding: false,
                    promotion: false,
                    skin: 'oxide',
                    relative_urls: false,
                    remove_script_host: false,
                    convert_urls: true,
                    images_upload_url: '{{ route("admin.upload.image") }}',
                    images_upload_handler: function (blobInfo, progress) {
                        return new Promise(function (resolve, reject) {
                            let formData = new FormData();
                            formData.append('file', blobInfo.blob(), blobInfo.filename());
                            formData.append('_token', window.csrfToken);

                            $.ajax({
                                url: '{{ route("admin.upload.image") }}',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function (res) {
                                    resolve(res.location);
                                },
                                error: function (xhr) {
                                    reject('Upload gagal: ' + (xhr.responseJSON?.message || 'Error'));
                                }
                            });
                        });
                    },
                    setup: function (editor) {
                        editor.on('change', function () {
                            editor.save(); // Sync to textarea
                        });
                    }
                });
            }
        });
    </script>

    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
            const overlay = document.getElementById('sidebarOverlay');
            if (overlay) overlay.classList.toggle('show');
        }

        // Global CSRF token for AJAX
        window.csrfToken = '{{ csrf_token() }}';

        // SweetAlert2 confirm delete
        function confirmDelete(formId) {
            Swal.fire({
                title: '{{ __("general.confirm_delete") }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ __("general.delete") }}',
                cancelButtonText: '{{ __("general.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById(formId).submit();
            });
        }

        // SweetAlert2 general confirm submit
        function swalConfirmSubmit(formId, title, text, icon) {
            Swal.fire({
                title: title || 'Konfirmasi',
                text: text || 'Apakah Anda yakin?',
                icon: icon || 'question',
                showCancelButton: true,
                confirmButtonColor: icon === 'warning' ? '#dc3545' : '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById(formId).submit();
            });
        }

        // Theme Toggle Logic
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const themeText = document.getElementById('theme-text');
        const bodyTag = document.documentElement;

        // Load preference (Default is light)
        const storedTheme = localStorage.getItem('admin_theme');
        if (storedTheme === 'dark') {
            bodyTag.setAttribute('data-theme', 'dark');
            themeIcon.classList.replace('bi-moon-stars-fill', 'bi-brightness-high-fill');
            if (themeText) themeText.textContent = 'Mode Terang';
        }

        themeToggle?.addEventListener('click', () => {
            let currentTheme = bodyTag.getAttribute('data-theme');
            if (currentTheme === 'dark') {
                bodyTag.removeAttribute('data-theme');
                localStorage.setItem('admin_theme', 'light');
                themeIcon.classList.replace('bi-brightness-high-fill', 'bi-moon-stars-fill');
                if (themeText) themeText.textContent = 'Mode Gelap';
            } else {
                bodyTag.setAttribute('data-theme', 'dark');
                localStorage.setItem('admin_theme', 'dark');
                themeIcon.classList.replace('bi-moon-stars-fill', 'bi-brightness-high-fill');
                if (themeText) themeText.textContent = 'Mode Terang';
            }
        });

        $(document).ready(function () {
            const notyf = new Notyf({ duration: 3000, position: { x: 'right', y: 'top' } });

            $('#profileForm').on('submit', function (e) {
                e.preventDefault();
                $('#btnSaveProfile').prop('disabled', true).text('Menyimpan...');
                $('#profileErrors').addClass('d-none').html('');

                $.ajax({
                    url: '{{ route('admin.profile.update') }}',
                    method: 'PUT',
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': window.csrfToken },
                    success: function (res) {
                        if (res.success) {
                            notyf.success(res.message);
                            $('#profileModal').modal('hide');
                            location.reload(); // reload to reflect new name in navbar
                        }
                    },
                    error: function (xhr) {
                        $('#btnSaveProfile').prop('disabled', false).text('Simpan');
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let html = '<ul class="mb-0">';
                            for (let key in errors) html += '<li>' + errors[key][0] + '</li>';
                            html += '</ul>';
                            $('#profileErrors').removeClass('d-none').html(html);
                        } else {
                            notyf.error('Terjadi kesalahan sistem.');
                        }
                    }
                });
            });

            $('#passwordForm').on('submit', function (e) {
                e.preventDefault();
                $('#btnSavePassword').prop('disabled', true).text('Menyimpan...');
                $('#passwordErrors').addClass('d-none').html('');

                $.ajax({
                    url: '{{ route('admin.password.update') }}',
                    method: 'PUT',
                    data: $(this).serialize(),
                    headers: { 'X-CSRF-TOKEN': window.csrfToken },
                    success: function (res) {
                        if (res.success) {
                            notyf.success(res.message);
                            $('#passwordModal').modal('hide');
                            $('#passwordForm')[0].reset();
                            $('#btnSavePassword').prop('disabled', false).text('Simpan');
                        }
                    },
                    error: function (xhr) {
                        $('#btnSavePassword').prop('disabled', false).text('Simpan');
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let html = '<ul class="mb-0">';
                            for (let key in errors) html += '<li>' + errors[key][0] + '</li>';
                            html += '</ul>';
                            $('#passwordErrors').removeClass('d-none').html(html);
                        } else {
                            notyf.error('Terjadi kesalahan sistem.');
                        }
                    }
                });
            });
        });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>