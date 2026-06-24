<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: #fff;
            color: #334155;
            font-size: 14px;
            padding: 40px;
            max-width: 900px;
            margin: 0 auto;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
        }

        .store-name {
            font-size: 26px;
            font-weight: 800;
            color: {{ app(\App\Services\SettingService::class)->primaryColor() }};
            letter-spacing: -0.5px;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: 800;
            color: #0f172a;
            text-align: right;
            letter-spacing: 2px;
        }

        .invoice-number {
            color: #64748b;
            font-size: 14px;
            margin-top: 6px;
            text-align: right;
            font-weight: 500;
        }

        .section {
            margin-bottom: 32px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            line-height: 1.6;
        }

        .info-box strong {
            display: block;
            margin-bottom: 6px;
            color: #1e293b;
            font-size: 15px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 24px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            text-align: left;
            padding: 14px 16px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .summary-table {
            max-width: 360px;
            margin-left: auto;
            border: none;
            border-radius: 0;
        }

        .summary-table td {
            padding: 8px 16px;
            border: none;
            font-size: 15px;
        }

        .total-row td {
            font-size: 20px;
            font-weight: 800;
            color: {{ app(\App\Services\SettingService::class)->primaryColor() }};
            border-top: 2px dashed #cbd5e1;
            padding-top: 16px;
            margin-top: 8px;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            line-height: 1.6;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
            
            .info-box {
                border: 1px solid #ececec;
            }
            
            table {
                border: 1px solid #ececec;
            }
        }
    </style>
</head>

<body>
    @if(!request()->has('download'))
        <div class="no-print"
            style="text-align:right;margin-bottom:20px; display:flex; justify-content:flex-end; gap:10px;">
            <button onclick="window.print()"
                style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;padding:10px 24px;border-radius:8px;cursor:pointer;font-weight:600">
                <span style="margin-right:6px">🖨️</span> Cetak Resi
            </button>
            <a href="?download=pdf"
                style="background:{{ app(\App\Services\SettingService::class)->primaryColor() }};color:#fff;border:none;padding:10px 24px;border-radius:8px;cursor:pointer;font-weight:600; text-decoration:none; display:inline-block;">
                <span style="margin-right:6px">⬇️</span> Download PDF
            </a>
        </div>
    @endif

    <div class="invoice-header">
        <div>
            @php
                $logo = app(\App\Services\SettingService::class)->get('store_logo');
                $logoSrc = null;
                if ($logo) {
                    $logoPath = public_path(ltrim($logo, '/'));
                    if (file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoMime = mime_content_type($logoPath);
                        $logoSrc = 'data:' . $logoMime . ';base64,' . $logoData;
                    } else {
                        $logoSrc = asset($logo);
                    }
                }
            @endphp
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo" style="height: 40px; object-fit: contain; margin-bottom: 5px;">
            @else
                <div class="store-name">{{ app(\App\Services\SettingService::class)->storeName() }}</div>
            @endif
            <div style="color:#888;font-size:13px;margin-top:4px">
                {{ app(\App\Services\SettingService::class)->get('store_phone', '') }}
                @if(app(\App\Services\SettingService::class)->get('store_email'))
                    <br>{{ app(\App\Services\SettingService::class)->get('store_email') }}
                @endif
            </div>
        </div>
        <div>
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">#{{ $order->order_number }}</div>
            <div class="invoice-number">{{ $order->created_at->translatedFormat('d F Y') }}</div>
        </div>
    </div>

    <div class="section">
        <div class="info-grid">
            <div class="info-box">
                <div class="section-title">Dikirim Ke</div>
                @php $addr = is_array($order->shipping_address) ? $order->shipping_address : json_decode($order->shipping_address, true); @endphp
                <strong>{{ $addr['name'] ?? ($order->user->name ?? $order->guest_name ?? '-') }}</strong>
                {{ $addr['phone'] ?? '' }}<br>
                {{ $addr['address'] ?? '' }}<br>
                {{ implode(', ', array_filter([$addr['city'] ?? '', $addr['province'] ?? '', $addr['postal_code'] ?? ''])) }}
            </div>
            <div class="info-box">
                <div class="section-title">Info Pesanan</div>
                <strong>Status: {{ ucfirst($order->status) }}</strong>
                Pembayaran: {{ $order->payment_status === 'paid' ? 'Lunas' : ucfirst($order->payment_status) }}<br>
                Metode:
                {{ $order->payment_method === 'manual_transfer' ? 'Transfer Bank' : strtoupper($order->payment_method) }}
            </div>
        </div>
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th style="width:40px">No</th>
                    <th>Produk</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            {{ $item->product_name }}
                            @if($item->variant_label) <span style="color:#888">({{ $item->variant_label }})</span> @endif
                        </td>
                        @php 
                            $effectivePrice = $item->price;
                            $basePrice = $item->variant ? $item->variant->price : ($item->product->base_price ?? $item->price);
                            $displayRowSubtotal = $basePrice * $item->quantity;
                        @endphp
                        <td class="text-right">Rp {{ number_format($basePrice, 0, ',', '.') }}</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">Rp {{ number_format($displayRowSubtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($order->shipping_cost > 0)
                <tr>
                    <td>Ongkir</td>
                    <td class="text-right">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($order->discount_amount > 0)
                <tr style="color:#28a745">
                    <td>Diskon</td>
                    <td class="text-right">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>Total</td>
                <td class="text-right">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if($order->notes)
        <div class="section">
            <div class="section-title">Catatan</div>
            <p style="background:#f8f9fa;padding:10px;border-radius:6px">{{ $order->notes }}</p>
        </div>
    @endif

    <div class="footer">
        Terima kasih telah berbelanja di {{ app(\App\Services\SettingService::class)->storeName() }}<br>
        Invoice ini dibuat secara otomatis dan sah tanpa tanda tangan.
    </div>
</body>

</html>