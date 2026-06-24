@php
    $settings = app(\App\Services\SettingService::class);
    $storeName = $settings->storeName();
    $storeAddress = $settings->get('store_address', '');
    $storePhone = $settings->get('store_phone', '');
    $storeFooter = $settings->get('invoice_footer_thermal', 'Terima kasih atas kunjungan Anda!');
    $widthMm = ($width ?? '80') === '58' ? 58 : 80;
    $autoprint = request('autoprint') === '1';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Receipt #{{ $order->order_number }}</title>
<style>
    @page { size: {{ $widthMm }}mm auto; margin: 0; }
    * { box-sizing: border-box; }
    html, body {
        margin: 0; padding: 0;
        background: #f1f5f9;
        font-family: 'Courier New', Courier, monospace;
        color: #000;
    }
    .receipt {
        width: {{ $widthMm }}mm;
        max-width: {{ $widthMm }}mm;
        margin: 12px auto;
        padding: 8px 6px 16px;
        background: #fff;
        font-size: {{ $widthMm == 58 ? '11px' : '12px' }};
        line-height: 1.35;
    }
    .center { text-align: center; }
    .right { text-align: right; }
    .left { text-align: left; }
    .bold { font-weight: 700; }
    .lg { font-size: {{ $widthMm == 58 ? '13px' : '14px' }}; font-weight: 700; }
    .xl { font-size: {{ $widthMm == 58 ? '16px' : '18px' }}; font-weight: 700; }
    .hr { border-top: 1px dashed #000; margin: 6px 0; }
    .row { display: flex; justify-content: space-between; gap: 6px; }
    table { width: 100%; border-collapse: collapse; }
    table td { vertical-align: top; padding: 1px 0; }
    .item-name { word-break: break-word; }
    .qty-col { white-space: nowrap; }
    .total-row td { padding: 2px 0; }
    @media print {
        body { background: #fff; }
        .receipt { margin: 0 auto; }
        .no-print { display: none !important; }
    }
    .toolbar {
        max-width: {{ $widthMm }}mm;
        margin: 12px auto 0;
        padding: 8px 6px;
        display: flex;
        gap: 6px;
        font-family: system-ui, sans-serif;
    }
    .toolbar a, .toolbar button {
        flex: 1;
        padding: 8px 10px; border-radius: 8px;
        background: #2563eb; color: #fff; border: 0; font-size: 12px;
        text-decoration: none; text-align: center; cursor: pointer;
    }
    .toolbar .secondary { background: #475569; }
</style>
</head>
<body>

<div class="toolbar no-print">
    <button onclick="window.print()">🖨️ Cetak</button>
    <a href="?width=58{{ $autoprint ? '&autoprint=1' : '' }}" class="secondary">58mm</a>
    <a href="?width=80{{ $autoprint ? '&autoprint=1' : '' }}" class="secondary">80mm</a>
</div>

<div class="receipt">
    <div class="center xl">{{ strtoupper($storeName) }}</div>
    @if($storeAddress)
        <div class="center">{{ $storeAddress }}</div>
    @endif
    @if($storePhone)
        <div class="center">Telp: {{ $storePhone }}</div>
    @endif

    <div class="hr"></div>

    <table>
        <tr><td>No</td><td class="right">{{ $order->order_number }}</td></tr>
        <tr><td>Tanggal</td><td class="right">{{ $order->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td class="right">{{ str(auth()->user()?->name ?? '-')->limit(20) }}</td></tr>
        @if($order->guest_name && $order->guest_name !== 'Walk-in Customer')
            <tr><td>Pelanggan</td><td class="right">{{ str($order->guest_name)->limit(20) }}</td></tr>
        @endif
        @if($order->guest_phone)
            <tr><td>HP</td><td class="right">{{ $order->guest_phone }}</td></tr>
        @endif
    </table>

    <div class="hr"></div>

    @foreach($order->items as $item)
        <div class="item-name bold">{{ $item->product_name }}</div>
        @if($item->variant_label)
            <div>{{ $item->variant_label }}</div>
        @endif
        <div class="row">
            <span class="qty-col">{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
            <span class="right">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
        </div>
    @endforeach

    <div class="hr"></div>

    <table>
        <tr class="total-row"><td>Subtotal</td><td class="right">{{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
        @if($order->discount_amount > 0)
            <tr class="total-row"><td>Diskon</td><td class="right">-{{ number_format($order->discount_amount, 0, ',', '.') }}</td></tr>
        @endif
        @if($order->shipping_cost > 0)
            <tr class="total-row"><td>Ongkir</td><td class="right">{{ number_format($order->shipping_cost, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="total-row"><td class="bold lg">TOTAL</td><td class="right bold lg">{{ number_format($order->total, 0, ',', '.') }}</td></tr>
        <tr class="total-row"><td>Bayar</td><td class="right">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td></tr>
    </table>

    <div class="hr"></div>

    <div class="center">{{ $storeFooter }}</div>
    <div class="center" style="margin-top:6px; font-size: 10px;">
        Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>
</div>

@if($autoprint)
<script>
    window.addEventListener('load', () => {
        setTimeout(() => window.print(), 300);
    });
</script>
@endif

</body>
</html>
