@extends('theme::layouts.app')
@section('title', 'Chat — ' . app(\App\Services\SettingService::class)->storeName())

@section('styles')
<style>
    .eb-chat-shell { max-width: 760px; margin: 0 auto; }
    .eb-chat-card { background: rgba(255,255,255,.95); border:1px solid rgba(255,255,255,.7); border-radius: 28px; box-shadow: 0 24px 60px rgba(15,23,42,.07); display:flex; flex-direction:column; height:75vh; overflow:hidden; }
    .eb-chat-header { padding:18px 22px; background:linear-gradient(135deg, color-mix(in srgb, var(--primary) 86%, black 14%), color-mix(in srgb, var(--secondary) 86%, black 14%)); color:#fff; display:flex; align-items:center; gap:12px; }
    .eb-chat-body { flex:1; overflow-y:auto; padding:18px; background:#fafafa; display:flex; flex-direction:column; gap:12px; }
    .eb-chat-bubble { max-width:80%; padding:12px 14px; border-radius:18px; font-size:.95rem; }
    .eb-bubble-mine { background:#111827; color:#fff; align-self:flex-end; border-bottom-right-radius:4px; }
    .eb-bubble-them { background:#fff; border:1px solid #ece7de; align-self:flex-start; border-bottom-left-radius:4px; }
    .eb-chat-foot { padding:14px 18px; border-top:1px solid #ece7de; background:#fff; }
</style>
@endsection

@section('content')
<div class="container-xxl px-3 eb-chat-shell py-3">
    <div class="eb-chat-card">
        <div class="eb-chat-header">
            <div class="rounded-circle bg-white text-dark d-flex align-items-center justify-content-center" style="width:42px;height:42px"><i class="bi bi-chat-quote"></i></div>
            <div>
                <div class="fw-bold">{{ app(\App\Services\SettingService::class)->storeName() }}</div>
                <div class="small opacity-75">Admin akan membalas secepatnya.</div>
            </div>
            <a href="{{ url()->previous() }}" class="ms-auto btn btn-sm btn-light rounded-pill">Tutup</a>
        </div>
        <div class="eb-chat-body" id="userChatContainer">
            <div class="eb-chat-bubble eb-bubble-them">Halo {{ Auth::user()->name }}! Ada yang bisa kami bantu hari ini?</div>
            @foreach($messages as $msg)
                @if($msg->sender_id === auth()->id())
                    <div class="eb-chat-bubble eb-bubble-mine">{!! nl2br(e($msg->message)) !!}</div>
                @else
                    <div class="eb-chat-bubble eb-bubble-them">{!! nl2br(e($msg->message)) !!}</div>
                @endif
            @endforeach
        </div>
        <div class="eb-chat-foot">
            <form action="{{ route('chat.store') }}" method="POST" class="d-flex gap-2">
                @csrf
                <input type="text" name="message" class="form-control rounded-pill" placeholder="Tulis pesan Anda..." required autofocus>
                <button type="submit" class="btn btn-dark rounded-circle" style="width:46px;height:46px"><i class="bi bi-send-fill"></i></button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const c = document.getElementById('userChatContainer');
        if (c) c.scrollTop = c.scrollHeight;
        setTimeout(() => window.location.reload(), 15000);
    });
</script>
@endpush
