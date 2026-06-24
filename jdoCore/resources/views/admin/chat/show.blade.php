@extends('admin.layouts.app')
@section('title', 'Chat Pelanggan')
@section('page-title', 'Detail Obrolan: ' . $conversation->user->name)

@section('content')
    <div class="row">
        <!-- Chat History -->
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4" style="border-radius:12px;overflow:hidden">
                <div class="card-header bg-white border-bottom pt-4 pb-3 px-4 d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                        style="width:40px;height:40px">
                        {{ strtoupper(substr($conversation->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">{{ $conversation->user->name }}</h6>
                        <span class="small text-muted">{{ $conversation->user->email }}</span>
                    </div>
                    <a href="{{ route('admin.chat.index') }}" class="btn btn-sm btn-outline-secondary ms-auto"
                        style="border-radius:10px">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>

                <div class="card-body p-4 chat-box" id="chatContainer"
                    style="height: 50vh; overflow-y: auto; background-color: #f8f9fa;">
                    @forelse($messages as $msg)
                        @if($msg->sender_id !== auth()->id())
                            <!-- Customer Message (Left) -->
                            <div class="d-flex mb-3">
                                <div>
                                    <div class="bg-white text-dark p-3 shadow-sm"
                                        style="border-radius: 0 15px 15px 15px; max-width: 75%;">
                                        {{ $msg->message }}
                                    </div>
                                    <div class="small text-muted mt-1 px-2" style="font-size: 0.75rem;">
                                        {{ $msg->created_at->format('H:i') }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Admin Message (Right) -->
                            <div class="d-flex mb-3 justify-content-end">
                                <div class="text-end">
                                    <div class="bg-primary text-white p-3 shadow-sm d-inline-block text-start"
                                        style="border-radius: 15px 0 15px 15px; max-width: 75%;">
                                        {{ $msg->message }}
                                    </div>
                                    <div class="small mt-1 px-2 {{ $msg->is_read ? 'text-primary' : 'text-muted' }}"
                                        style="font-size: 0.75rem;">
                                        {{ $msg->created_at->format('H:i') }}
                                        @if($msg->is_read)
                                            <i class="bi bi-check-all ms-1"></i> Dibaca
                                        @else
                                            <i class="bi bi-check ms-1"></i> Terkirim
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="text-center text-muted mt-5 opacity-50">
                            Pesan percakapan akan tampil di sini.
                        </div>
                    @endforelse
                </div>

                <div class="card-footer bg-white border-top p-4">
                    <form action="{{ route('admin.chat.store', $conversation) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="text" name="message" class="form-control"
                                placeholder="Tulis balasan pesan di sini..."
                                style="border-radius: 10px 0 0 10px; border-right: none;" required autofocus
                                autocomplete="off">
                            <button class="btn btn-primary px-4" type="submit" style="border-radius: 0 10px 10px 0;">
                                <i class="bi bi-send-fill text-white"></i> Kirim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Auto scroll to bottom
        const chatContainer = document.getElementById('chatContainer');
        chatContainer.scrollTop = chatContainer.scrollHeight;
    </script>
@endpush