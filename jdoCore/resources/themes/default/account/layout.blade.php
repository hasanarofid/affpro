@extends('theme::layouts.app')

@section('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --sidebar-bg: #ffffff;
            --sidebar-hover: #f1f5f9;
            --sidebar-active-bg: rgba(var(--primary-rgb, 79, 70, 229), 0.1);
            --sidebar-text: #64748b;
            --sidebar-active-text: var(--primary);
        }

        .account-sidebar-card {
            border: none;
            border-radius: 24px;
            background: #ffffff;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .sidebar-header {
            padding: 2.5rem 1.5rem 1.5rem;
            background: linear-gradient(to bottom, #f8fafc, #ffffff);
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .avatar-box {
            width: 85px;
            height: 85px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            color: white;
            font-size: 2.2rem;
            font-weight: 800;
            box-shadow: 0 12px 24px -6px rgba(var(--primary-rgb, 79, 70, 229), 0.4);
            border: 4px solid #fff;
        }

        .sidebar-menu {
            padding: 1.25rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-menu-item {
            display: flex;
            align-items: center;
            padding: 0.9rem 1.25rem;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.92rem;
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
        }

        .sidebar-menu-item i {
            font-size: 1.3rem;
            margin-right: 14px;
            color: #94a3b8;
            transition: all 0.3s;
            width: 24px;
            text-align: center;
        }

        .sidebar-menu-item:hover {
            background-color: #f8fafc;
            color: var(--primary);
            padding-left: 1.5rem;
        }

        .sidebar-menu-item:hover i {
            color: var(--primary);
        }

        .sidebar-menu-item.active {
            background-color: rgba(var(--primary-rgb, 79, 70, 229), 0.08);
            color: var(--primary);
            border: 1px solid rgba(var(--primary-rgb, 79, 70, 229), 0.1);
        }

        .sidebar-menu-item.active i {
            color: var(--primary);
        }

        .sidebar-footer {
            padding: 0 1.25rem 1.5rem;
        }

        .logout-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0.9rem;
            background: #fef2f2;
            color: #ef4444;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.85rem;
            transition: all 0.2s;
            border: 1px solid #fee2e2;
            width: 100%;
        }

        .logout-link:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
            box-shadow: 0 8px 16px -4px rgba(239, 68, 68, 0.25);
        }

        /* DataTable Premium look */
        .account-main-card {
            border-radius: 24px;
            border: none;
            box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }



        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 1rem;
        }

        table.dataTable thead th {
            background: #f8fafc;
            border-bottom: 2px solid #f1f5f9 !important;
            padding: 14px 20px !important;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }

        table.dataTable td {
            padding: 14px 20px !important;
            border-bottom: 1px solid #f8fafc !important;
        }
    </style>
@endsection

@section('content')
    <div class="container py-5">
        <div class="row g-4">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="account-sidebar-card sticky-top" style="top: 100px; z-index: 10;">
                    <!-- Header -->
                    <div class="sidebar-header">
                        <div class="avatar-box">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <h5 class="fw-bold mb-1 text-dark">{{ auth()->user()->name }}</h5>
                        <p class="text-muted small mb-0">{{ auth()->user()->phone ?? auth()->user()->email }}</p>
                    </div>

                    <!-- Menu -->
                    <div class="sidebar-menu">
                        <a href="{{ route('account.profile') }}"
                            class="sidebar-menu-item {{ request()->routeIs('account.profile') ? 'active' : '' }}">
                            <i class="bi bi-grid-fill"></i> Dashboard Akun
                        </a>
                        <a href="{{ route('account.orders') }}"
                            class="sidebar-menu-item {{ request()->routeIs('account.orders') ? 'active' : '' }}">
                            <i class="bi bi-bag-check-fill"></i> Transaksi Saya
                        </a>
                        <a href="{{ route('account.addresses') }}"
                            class="sidebar-menu-item {{ request()->routeIs('account.addresses') ? 'active' : '' }}">
                            <i class="bi bi-geo-alt-fill"></i> Daftar Alamat
                        </a>
                        <a href="{{ route('account.wallet') }}"
                            class="sidebar-menu-item {{ request()->routeIs('account.wallet') ? 'active' : '' }}">
                            <i class="bi bi-wallet2"></i> Saldo Akun
                        </a>
                        <a href="{{ route('account.banks') }}"
                            class="sidebar-menu-item {{ request()->routeIs('account.banks') ? 'active' : '' }}">
                            <i class="bi bi-bank"></i> Rekening Bank
                        </a>
                        <a href="{{ route('account.affiliate') }}"
                            class="sidebar-menu-item {{ request()->routeIs('account.affiliate') ? 'active' : '' }}">
                            <i class="bi bi-people-fill"></i> Program Affiliate
                        </a>
                    </div>

                    <!-- Footer -->
                    <div class="sidebar-footer">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="logout-link">
                                <i class="bi bi-box-arrow-right"></i> Keluar Akun
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center py-3 px-4 small border-0 mb-4 shadow-sm"
                        style="border-radius:18px; background: #f0fdf4; color: #166534;">
                        <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                        <div><strong>Berhasil!</strong> {{ session('success') }}</div>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center py-3 px-4 small border-0 mb-4 shadow-sm"
                        style="border-radius:18px; background: #fef2f2; color: #991b1b;">
                        <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
                        <div><strong>Gagal!</strong> {{ session('error') }}</div>
                    </div>
                @endif

                <div class="account-main-card card">
                    <div class="card-body p-0">
                        @yield('account_content')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    @yield('account_js')
@endsection