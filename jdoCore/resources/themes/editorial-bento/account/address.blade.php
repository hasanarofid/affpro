@extends('theme::account.layout')
@section('title', 'Alamat Saya')

@section('account_content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <span class="eb-kicker">Address Book</span>
            <h3 class="fw-bold mt-2 mb-0" style="font-family:var(--heading); letter-spacing:-.03em">Daftar Alamat</h3>
            <div class="text-muted small mt-1">Simpan alamat untuk checkout lebih cepat.</div>
        </div>
    </div>

    @if(isset($addresses) && count($addresses))
        <div class="row g-3">
            @foreach($addresses as $addr)
                <div class="col-md-6">
                    <div class="p-4 rounded-4 border h-100" style="border-color:#efe8dd!important;background:#fbfaf6">
                        <div class="d-flex justify-content-between">
                            <div class="fw-bold">{{ $addr->recipient_name }}</div>
                            @if($addr->is_main)<span class="badge text-bg-dark rounded-pill">Utama</span>@endif
                        </div>
                        <div class="small text-muted mt-1">{{ $addr->phone }}</div>
                        <div class="small text-muted">{{ $addr->address_line }}</div>
                        <div class="small text-muted">{{ $addr->city }}, {{ $addr->province }} {{ $addr->postal_code }}</div>
                        <div class="d-flex gap-2 mt-3">
                            <form action="{{ route('account.addresses.setDefault', $addr) }}" method="POST" class="d-inline">@csrf @method('PUT')<button class="btn btn-sm btn-outline-dark rounded-pill">Jadikan Utama</button></form>
                            <form action="{{ route('account.addresses.destroy', $addr) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus alamat?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger rounded-pill"><i class="bi bi-trash"></i></button></form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-geo-alt fs-1 text-muted opacity-50"></i>
            <h5 class="fw-bold mt-3">Belum ada alamat</h5>
            <p class="text-muted">Tambahkan alamat lewat halaman checkout untuk pertama kali.</p>
            <a href="{{ route('products.index') }}" class="btn btn-outline-dark rounded-pill px-4">Mulai Belanja</a>
        </div>
    @endif
@endsection
