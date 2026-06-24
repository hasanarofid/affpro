@extends('theme::layouts.app')
@section('title', 'WhatsApp/Chat Toko')

@section('content')
 <div class="container py-5">
 <div class="row min-vh-50 justify-content-center">
 <div>
 <div class="card shadow-sm border-0 bg-white"
 style="border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; height: 75vh;">

 <!-- Header -->
 <div class="card-header bg-primary text-white border-bottom-0 py-3 px-4 d-flex align-items-center">
 <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3"
 style="width:45px;height:45px">
 <i class="bi bi-shop fs-4"></i>
 </div>
 <div>
 <h5 class="fw-bold mb-0 text-white">{{ app(\App\Services\SettingService::class)->storeName() }}
 </h5>
 <span class="small opacity-75 d-block mt-1">
 <i class="bi bi-circle-fill text-success"
 style="font-size: 0.5rem; vertical-align: middle;"></i> Admin Online
 </span>
 </div>
 <a href="{{ url()->previous() }}"
 class="btn btn-sm btn-light bg-opacity-10 text-white border-0 ms-auto">
 <i class="bi bi-x-lg"></i> Tutup
 </a>
 </div>

 <!-- Chat Box Area -->
 <div class="card-body p-4 bg-light flex-grow-1 overflow-auto" id="userChatContainer"
 style="display: flex; flex-direction: column; gap: 1rem;">
 <div class="text-center w-100 mb-3">
 <span class="badge bg-white text-muted shadow-sm fw-normal px-3 py-2">
 Pesan diamankan menggunakan enkripsi internal
 </span>
 </div>

 <!-- Fixed Welcome Message -->
 <div class="d-flex w-100 justify-content-start">
 <div style="max-width: 80%;">
 <div class="bg-white p-3 shadow-sm text-dark position-relative"
 style="border-radius: 0 16px 16px 16px;">
 Halo {{ Auth::user()->name }}! Ada yang bisa kami bantu seputar produk atau pesanan Anda
 hari ini?
 Silakan tinggalkan pesan Anda.
 </div>
 <div class="small text-muted mt-1 px-1">Otomatis</div>
 </div>
 </div>

 <!-- Messages loop -->
 @foreach($messages as $msg)
 @if($msg->sender_id === auth()->id())
 <!-- My message (Right) -->
 <div class="d-flex w-100 justify-content-end">
 <div style="max-width: 80%; text-align: right;">
 <div class="bg-primary text-white p-3 shadow-sm d-inline-block text-start position-relative"
 style="border-radius: 16px 0 16px 16px;">
 {!! nl2br(e($msg->message)) !!}
 </div>
 <div class="small text-muted mt-1 px-1 d-flex justify-content-end align-items-center gap-1">
 {{ $msg->created_at->format('H:i') }}
 @if($msg->is_read)
 <i class="bi bi-check-all text-primary" title="Dibaca"></i>
 @else
 <i class="bi bi-check text-muted" title="Terkirim"></i>
 @endif
 </div>
 </div>
 </div>
 @else
 <!-- Admin message (Left) -->
 <div class="d-flex w-100 justify-content-start">
 <div style="max-width: 80%;">
 <div class="bg-white p-3 shadow-sm text-dark position-relative"
 style="border-radius: 0 16px 16px 16px;">
 {!! nl2br(e($msg->message)) !!}
 </div>
 <div class="small text-muted mt-1 px-1">{{ $msg->created_at->format('H:i') }}</div>
 </div>
 </div>
 @endif
 @endforeach
 </div>

 <!-- Input Area -->
 <div class="card-footer bg-white border-top-0 p-3 pt-0">
 <form action="{{ route('chat.store') }}" method="POST" id="chatForm">
 @csrf
 <div class="input-group bg-light rounded-pill p-1 shadow-sm border mt-3">
 <input type="text" name="message"
 class="form-control border-0 bg-transparent text-dark px-3 mt-1"
 placeholder="Ketik pesan Anda..." style="box-shadow: none;" required autocomplete="off"
 autofocus>
 <button type="submit"
 class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center m-1 shadow-sm"
 style="width: 42px; height: 42px;">
 <i class="bi bi-send-fill ms-1"></i>
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
 </div>
 </div>
@endsection

@push('scripts')
 <script>
 document.addEventListener('DOMContentLoaded', () => {
 const chatContainer = document.getElementById('userChatContainer');
 chatContainer.scrollTop = chatContainer.scrollHeight;

 // Auto reload page every 10 seconds to check for new messages
 // (A simple approach instead of WebSocket or complex Ajax for now)
 setTimeout(() => {
 window.location.reload();
 }, 15000);
 });
 </script>
@endpush