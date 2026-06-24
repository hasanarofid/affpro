@extends('admin.layouts.app')
@section('title', 'Order #' . $order->order_number)
@section('page-title', 'Detail Pesanan')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card" style="border:none;border-radius:12px">
                <div class="card-header bg-white d-flex align-items-center">
                    <h6 class="mb-0 fw-semibold">{{ $order->order_number }}</h6>
                    @php $statusColors = ['pending' => 'warning', 'confirmed' => 'info', 'processing' => 'primary', 'shipped' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger', 'expired' => 'dark']; @endphp
                    <span
                        class="badge badge-status bg-{{ $statusColors[$order->status] ?? 'secondary' }} ms-2">{{ __('order.status_' . $order->status) }}</span>
                    <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank"
                        class="btn btn-sm btn-outline-secondary ms-auto" style="border-radius:8px">
                        <i class="bi bi-printer me-1"></i> Cetak Invoice
                    </a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th>Harga</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $item->product_name }}</div>
                                        @if($item->variant_label)<small
                                        class="text-muted">{{ $item->variant_label }}</small>@endif
                                    </td>
                                    @php 
                                        $basePrice = $item->variant ? $item->variant->price : ($item->product->base_price ?? $item->price);
                                        $rowBaseSubtotal = $basePrice * $item->quantity;
                                    @endphp
                                    <td>Rp {{ number_format($basePrice, 0, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rp {{ number_format($rowBaseSubtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between"><span>Subtotal</span><span>Rp
                            {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                    @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between text-success"><span>Diskon</span><span>-Rp
                    {{ number_format($order->discount_amount, 0, ',', '.') }}</span></div>@endif
                    <div class="d-flex justify-content-between"><span>Ongkir</span><span>Rp
                            {{ number_format($order->shipping_cost, 0, ',', '.') }}</span></div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold"><span>Total</span><span>Rp
                            {{ number_format($order->total, 0, ',', '.') }}</span></div>
                </div>
            </div>

            <!-- Payment -->
            @if($order->payments->isNotEmpty())
                <div class="card mt-3" style="border:none;border-radius:12px">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-semibold">Pembayaran</h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Metode</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Bukti</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->payments as $payment)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        <td>
                                            @php 
                                                $payLabels = ['pending' => 'Menunggu', 'success' => 'Terverifikasi', 'failed' => 'Gagal', 'refunded' => 'Refund'];
                                                $statusColor = $payment->status === 'success' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger');
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}">{{ $payLabels[$payment->status] ?? $payment->status }}</span>
                                        </td>
                                        <td>
                                            @if($payment->proof_image)
                                                <button type="button" onclick="showProof('{{ asset($payment->proof_image) }}')" 
                                                    class="btn btn-sm btn-outline-info" style="border-radius:6px">
                                                    <i class="bi bi-image"></i>
                                                </button>
                                            @else 
                                                - 
                                            @endif
                                        </td>
                                        <td>
                                            @if($payment->status === 'pending')
                                                <form action="{{ route('admin.payments.verify', $payment) }}" method="POST">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-sm btn-success" style="border-radius:6px"><i
                                                            class="bi bi-check-lg me-1"></i>Verifikasi</button>
                                                </form>
                                            @else
                                                @if($payment->verified_at)<small
                                                class="text-muted">{{ $payment->verified_at->format('d/m/Y H:i') }}</small>@endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Customer Info -->
            <div class="card" style="border:none;border-radius:12px">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Info Pelanggan</h6>
                    <p class="mb-1"><i class="bi bi-person me-2 text-muted"></i>{{ $order->customer_name }}</p>
                    <p class="mb-1"><i class="bi bi-telephone me-2 text-muted"></i>{{ $order->customer_phone ?: '-' }}</p>
                    @if($order->is_guest)<span class="badge bg-warning text-dark">Guest</span>@endif
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card mt-3" style="border:none;border-radius:12px">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Alamat Pengiriman</h6>
                    @php $addr = $order->shipping_address; @endphp
                    <p class="mb-0 small text-muted">
                        {{ $addr['name'] ?? '' }}<br>
                        {{ $addr['phone'] ?? '' }}<br>
                        {{ $addr['address'] ?? '' }}<br>
                        {{ $addr['city'] ?? '' }}, {{ $addr['province'] ?? '' }} {{ $addr['postal_code'] ?? '' }}
                    </p>
                </div>
            </div>

            <!-- Status Update -->
            @if(!in_array($order->status, ['delivered', 'cancelled', 'expired']))
                <div class="card mt-3" style="border:none;border-radius:12px">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Update Status</h6>
                        <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST">
                            @csrf @method('PUT')
                            <select name="status" class="form-select mb-2">
                                @foreach(\App\Models\Order::STATUS_TRANSITIONS[$order->status] ?? [] as $next)
                                    <option value="{{ $next }}">{{ __('order.status_' . $next) }}</option>
                                @endforeach
                            </select>
                            <textarea name="reason" class="form-control mb-2" placeholder="Alasan (opsional)"
                                rows="2"></textarea>
                            <button class="btn btn-primary w-100" style="border-radius:10px">Update</button>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Resi -->
            @if($order->status === 'processing' || $order->status === 'confirmed')
                <div class="card mt-3" style="border:none;border-radius:12px">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Input Resi</h6>
                        <form action="{{ route('admin.orders.updateResi', $order) }}" method="POST">
                            @csrf @method('PUT')
                            <input type="text" name="tracking_number" class="form-control mb-2" placeholder="Nomor resi"
                                value="{{ $order->shipment->tracking_number ?? '' }}">
                            <button class="btn btn-outline-primary w-100" style="border-radius:10px"><i
                                    class="bi bi-truck me-1"></i> Simpan & Kirim</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <!-- Modal Bukti Transfer -->
    <div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Bukti Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img id="proofImage" src="" class="img-fluid rounded-4 shadow-sm" alt="Bukti Transfer">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                    <a id="proofDownload" href="" target="_blank" class="btn btn-primary rounded-pill px-4" download>Download</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function showProof(url) {
            $('#proofImage').attr('src', url);
            $('#proofDownload').attr('href', url);
            new bootstrap.Modal(document.getElementById('proofModal')).show();
        }
    </script>
@endsection