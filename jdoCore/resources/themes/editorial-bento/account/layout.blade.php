@extends('theme::layouts.app')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .eb-account-shell { display:grid; grid-template-columns: 290px 1fr; gap:24px; max-width:1380px; margin: 0 auto; }
    .eb-account-card { background: rgba(255,255,255,.95); border:1px solid rgba(255,255,255,.65); border-radius: 28px; box-shadow: 0 24px 60px rgba(15,23,42,.07); overflow:hidden; }
    .eb-side-head { padding: 28px 22px 22px; text-align:center; border-bottom: 1px solid #f1ede6; background: linear-gradient(180deg, #fbfaf6, #fff); }
    .eb-side-avatar { width:78px; height:78px; border-radius:999px; margin: 0 auto 12px; background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 80%, white 20%), color-mix(in srgb, var(--secondary) 80%, white 20%)); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.6rem; font-weight:800; box-shadow: 0 14px 30px rgba(79,70,229,.18); }
    .eb-side-menu { padding: 14px 12px; display: grid; gap: 4px; }
    .eb-side-item { display:flex; align-items:center; gap:12px; padding: 10px 14px; border-radius:14px; color:#475569; text-decoration:none; font-weight:600; font-size:.93rem; transition:.2s ease; }
    .eb-side-item i { font-size:1.1rem; color:#94a3b8; }
    .eb-side-item:hover { background:#fbfaf6; color: var(--primary); }
    .eb-side-item.active { background: color-mix(in srgb, var(--primary) 8%, white 92%); color: var(--primary); }
    .eb-side-item.active i { color: var(--primary); }
    .eb-side-foot { padding: 12px 14px 22px; }
    .eb-logout { width:100%; display:flex; align-items:center; justify-content:center; gap:10px; padding: 11px 14px; background:#fef2f2; color:#ef4444; border:1px solid #fee2e2; border-radius:14px; text-decoration:none; font-weight:700; font-size:.9rem; transition:.2s ease; }
    .eb-logout:hover { background:#ef4444; color:#fff; border-color:#ef4444; }
    @media (max-width: 991.98px) { .eb-account-shell { grid-template-columns: 1fr; } }
    .eb-account-card .card-body { padding: 1.5rem; }
    table.dataTable thead th { background:#fbfaf6 !important; border-bottom: 1px solid #efe8dd !important; padding:14px 16px !important; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:#64748b; }
    table.dataTable td { padding:14px 16px !important; }
</style>
@endsection

@section('content')
<div class="container-xxl px-3">
    <div class="eb-account-shell">
        <aside class="eb-account-card">
            <div class="eb-side-head">
                <div class="eb-side-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="fw-bold">{{ auth()->user()->name }}</div>
                <div class="text-muted small">{{ auth()->user()->phone ?? auth()->user()->email }}</div>
            </div>
            <nav class="eb-side-menu">
                <a href="{{ route('account.profile') }}" class="eb-side-item {{ request()->routeIs('account.profile') ? 'active' : '' }}"><i class="bi bi-grid"></i> Dashboard</a>
                <a href="{{ route('account.orders') }}" class="eb-side-item {{ request()->routeIs('account.orders') ? 'active' : '' }}"><i class="bi bi-bag-check"></i> Pesanan</a>
                <a href="{{ route('account.addresses') }}" class="eb-side-item {{ request()->routeIs('account.addresses') ? 'active' : '' }}"><i class="bi bi-geo-alt"></i> Alamat</a>
                <a href="{{ route('account.wallet') }}" class="eb-side-item {{ request()->routeIs('account.wallet') ? 'active' : '' }}"><i class="bi bi-wallet2"></i> Saldo</a>
                <a href="{{ route('account.banks') }}" class="eb-side-item {{ request()->routeIs('account.banks') ? 'active' : '' }}"><i class="bi bi-bank"></i> Bank</a>
                <a href="{{ route('account.affiliate') }}" class="eb-side-item {{ request()->routeIs('account.affiliate') ? 'active' : '' }}"><i class="bi bi-people"></i> Affiliate</a>
            </nav>
            <div class="eb-side-foot">
                <form action="{{ route('logout') }}" method="POST">@csrf<button class="eb-logout" type="submit"><i class="bi bi-box-arrow-right"></i> Keluar Akun</button></form>
            </div>
        </aside>
        <main>
            @if(session('success'))<div class="alert alert-success rounded-4 small py-2 mb-3">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger rounded-4 small py-2 mb-3">{{ session('error') }}</div>@endif
            <div class="eb-account-card">
                <div class="card-body">
                    @yield('account_content')
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@yield('account_js')
@endsection
