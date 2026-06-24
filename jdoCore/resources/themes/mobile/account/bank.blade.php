@extends('theme::account.layout')
@section('title', 'Data Rekening Bank')

@section('account_content')
 <div class="card-body p-0">
 <div class="p-4 border-bottom d-flex justify-content-between align-items-center text-dark">
 <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-bank2 me-2 text-primary"></i>Rekening Bank</h5>
 <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
 data-bs-target="#addBankModal">
 <i class="bi bi-plus-lg me-1"></i> Tambah Rekening
 </button>
 </div>

 <div class="p-4 pb-5">
 @forelse($accounts as $account)
 <div class="card mb-3 {{ $account->is_main ? 'border-primary' : '' }}"
 style="border-radius:10px; {{ $account->is_main ? 'background-color: var(--primary); /* Opacity trick using rgb */ background-color: rgba(var(--primary-rgb, 79, 70, 229), 0.03);' : '' }}">
 <div class="p-3">
 <div class="d-flex justify-content-between align-items-center">
 <div class="d-flex align-items-center">
 <div class="bg-light rounded d-flex justify-content-center align-items-center border me-3"
 style="width: 48px; height: 48px;">
 <i class="bi bi-wallet2 text-muted fs-4"></i>
 </div>
 <div>
 <div class="d-flex align-items-center mb-1">
 <span class="fw-bold fs-6 text-dark me-2">{{ $account->bank_name }}</span>
 @if($account->is_main)
 <span class="badge bg-primary">Utama</span>
 @endif
 </div>
 <div class="fw-medium text-dark">{{ $account->account_number }}</div>
 <div class="text-muted small">a.n {{ $account->account_name }}</div>
 </div>
 </div>
 <div class="dropdown">
 <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
 <i class="bi bi-three-dots-vertical"></i>
 </button>
 <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="border-radius:10px; border:none;">
 @if(!$account->is_main)
 <li>
 <form action="{{ route('account.banks.setDefault', $account->id) }}" method="POST">
 @csrf @method('PUT')
 <button class="dropdown-item small text-primary"><i
 class="bi bi-check2-circle me-2"></i>Jadikan Utama</button>
 </form>
 </li>
 @endif
 <li><a class="dropdown-item small" href="#" data-bs-toggle="modal"
 data-bs-target="#editBankModal{{ $account->id }}"><i
 class="bi bi-pencil me-2"></i>Ubah</a></li>
 <li>
 <hr class="dropdown-divider">
 </li>
 <li>
 <form action="{{ route('account.banks.destroy', $account->id) }}" method="POST"
 onsubmit="return confirm('Hapus rekening ini?')">
 @csrf @method('DELETE')
 <button class="dropdown-item small text-danger"><i
 class="bi bi-trash me-2"></i>Hapus</button>
 </form>
 </li>
 </ul>
 </div>
 </div>
 </div>
 </div>

 <!-- Edit Modal -->
 <div class="modal fade" id="editBankModal{{ $account->id }}" tabindex="-1">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content" style="border-radius:16px; border:none;">
 <form action="{{ route('account.banks.update', $account->id) }}" method="POST">
 @csrf @method('PUT')
 <div class="modal-header border-bottom-0 pt-4 px-4">
 <h6 class="modal-title fw-bold">Ubah Rekening</h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body px-4">
 <div class="mb-3">
 <label class="form-label small fw-medium">Nama Bank *</label>
 <input type="text" name="bank_name" class="form-control"
 value="{{ $account->bank_name }}" required placeholder="Contoh: BCA, Mandiri, BNI"
 style="border-radius:10px">
 </div>
 <div class="mb-3">
 <label class="form-label small fw-medium">Nomor Rekening *</label>
 <input type="text" name="account_number" class="form-control"
 value="{{ $account->account_number }}" required style="border-radius:10px">
 </div>
 <div class="mb-3">
 <label class="form-label small fw-medium">Nama Pemilik Rekening *</label>
 <input type="text" name="account_name" class="form-control"
 value="{{ $account->account_name }}" required style="border-radius:10px">
 </div>
 </div>
 <div class="modal-footer border-top-0 pb-4 px-4">
 <button type="button" class="btn btn-light" data-bs-dismiss="modal"
 style="border-radius:10px">Batal</button>
 <button type="submit" class="btn btn-primary" style="border-radius:10px">Simpan
 Perubahan</button>
 </div>
 </form>
 </div>
 </div>
 </div>
 @empty
 <div class="text-center py-5">
 <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center mb-3"
 style="width:80px; height:80px;">
 <i class="bi bi-bank" style="font-size:2.5rem;color:#cbd5e1"></i>
 </div>
 <h6 class="fw-bold text-dark">Belum ada rekening</h6>
 <p class="text-muted small mb-4">Tambahkan rekening bank untuk mempermudah proses pencairan saldo.</p>
 </div>
 @endforelse
 </div>
 </div>

 <!-- Add Modal -->
 <div class="modal fade" id="addBankModal" tabindex="-1">
 <div class="modal-dialog modal-dialog-centered">
 <div class="modal-content" style="border-radius:16px; border:none;">
 <form action="{{ route('account.banks.store') }}" method="POST">
 @csrf
 <div class="modal-header border-bottom-0 pt-4 px-4">
 <h6 class="modal-title fw-bold">Tambah Rekening Baru</h6>
 <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
 </div>
 <div class="modal-body px-4">
 <div class="mb-3">
 <label class="form-label small fw-medium">Nama Bank *</label>
 <input type="text" name="bank_name" class="form-control" required
 placeholder="Contoh: BCA, Mandiri, BNI" style="border-radius:10px">
 </div>
 <div class="mb-3">
 <label class="form-label small fw-medium">Nomor Rekening *</label>
 <input type="text" name="account_number" class="form-control" required
 style="border-radius:10px">
 </div>
 <div class="mb-3">
 <label class="form-label small fw-medium">Nama Pemilik Rekening *</label>
 <input type="text" name="account_name" class="form-control" required style="border-radius:10px">
 </div>
 </div>
 <div class="modal-footer border-top-0 pb-4 px-4">
 <button type="button" class="btn btn-light" data-bs-dismiss="modal"
 style="border-radius:10px">Batal</button>
 <button type="submit" class="btn btn-primary" style="border-radius:10px">Simpan Rekening</button>
 </div>
 </form>
 </div>
 </div>
 </div>
@endsection