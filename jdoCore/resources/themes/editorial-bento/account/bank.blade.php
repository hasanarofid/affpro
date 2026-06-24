@extends('theme::account.layout')
@section('title', 'Rekening Bank')

@section('account_content')
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <span class="eb-kicker">Bank</span>
            <h3 class="fw-bold mt-2 mb-0" style="font-family:var(--heading); letter-spacing:-.03em">Rekening Saya</h3>
            <div class="text-muted small mt-1">Daftar rekening untuk pencairan saldo.</div>
        </div>
    </div>

    @if(isset($accounts) && count($accounts))
        <div class="row g-3">
            @foreach($accounts as $a)
                <div class="col-md-6">
                    <div class="p-4 rounded-4 border h-100" style="border-color:#efe8dd!important;background:#fbfaf6">
                        <div class="small text-uppercase fw-bold text-muted" style="letter-spacing:.08em">{{ $a->bank_name }}</div>
                        <div class="fw-bold fs-4 mt-1">{{ $a->account_number }}</div>
                        <div class="text-muted small">a.n. {{ $a->account_holder }}</div>
                        <div class="d-flex gap-2 mt-3">
                            <form action="{{ route('account.banks.setDefault', $a) }}" method="POST">@csrf @method('PUT')<button class="btn btn-sm btn-outline-dark rounded-pill">Jadikan Utama</button></form>
                            <form action="{{ route('account.banks.destroy', $a) }}" method="POST" onsubmit="return confirm('Hapus rekening?')">@csrf @method('DELETE')<button class="btn btn-sm btn-light text-danger rounded-pill"><i class="bi bi-trash"></i></button></form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center text-muted py-5 small">Belum ada rekening tersimpan.</div>
    @endif
@endsection
