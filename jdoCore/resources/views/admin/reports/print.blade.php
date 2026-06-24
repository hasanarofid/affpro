<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan - {{ $type }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0 0 5px;
            font-size: 24px;
            color: #111;
        }

        .header p {
            margin: 0;
            color: #666;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f8f9fa;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .profit-loss-table td {
            padding: 15px;
            font-size: 16px;
            border: none;
            border-bottom: 1px solid #eee;
        }

        .profit-loss-table tr:last-child td {
            border-bottom: none;
            font-weight: bold;
            font-size: 18px;
        }

        @page {
            margin: 2cm;
            size: A4;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            button {
                display: none !important;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body onload="requestPrint()">
    <div class="header">
        @php
            $storeName = app(\App\Services\SettingService::class)->storeName();
        @endphp
        <h1>{{ $storeName }}</h1>
        <p>Laporan:
            @if($type === 'transactions') Semua Transaksi
            @elseif($type === 'users') Transaksi per User
            @elseif($type === 'profit_loss') Laba Rugi
            @elseif($type === 'products') Penjualan Produk Terlaris
            @endif
        </p>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d
            {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    @if($type === 'transactions')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nomor Order</th>
                    <th>Pelanggan</th>
                    <th>Status</th>
                    <th class="text-end">Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $row->order_number }}</td>
                        <td>{{ $row->customer_name }}</td>
                        <td style="text-transform: capitalize">{{ $row->status }}</td>
                        <td class="text-end">{{ number_format($row->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
                @if($data->isNotEmpty())
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Pendapatan</td>
                        <td class="text-end fw-bold">{{ number_format($data->sum('total'), 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

    @elseif($type === 'users')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Email</th>
                    <th class="text-center">Total Transaksi</th>
                    <th class="text-end">Total Nilai (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row->user->name ?? '-' }}</td>
                        <td>{{ $row->user->email ?? '-' }}</td>
                        <td class="text-center">{{ $row->total_orders }}</td>
                        <td class="text-end">{{ number_format($row->sum_total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @elseif($type === 'profit_loss')
        <table class="profit-loss-table">
            <tbody>
                <tr>
                    <td>Total Pesanan Selesai/Dibayar</td>
                    <td class="text-end">{{ $data->total_orders ?? 0 }} Transaksi</td>
                </tr>
                <tr>
                    <td>Total Subtotal Penjualan</td>
                    <td class="text-end">Rp {{ number_format($data->total_subtotal ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Diskon Diberikan</td>
                    <td class="text-end" style="color: #dc3545">-Rp
                        {{ number_format($data->total_discount ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Pendapatan Ongkir</td>
                    <td class="text-end" style="color: #198754">+Rp
                        {{ number_format($data->total_shipping ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr style="background:#f8f9fa;">
                    <td>TOTAL PENDAPATAN</td>
                    <td class="text-end" style="color: #0d6efd">Rp
                        {{ number_format($data->total_sales_revenue ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

    @elseif($type === 'products')
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Produk</th>
                    <th>Varian</th>
                    <th class="text-center">Terjual (Pcs)</th>
                    <th class="text-end">Total Pendapatan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $i => $row)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $row->product_name }}</td>
                        <td>{{ $row->variant_label ?? '-' }}</td>
                        <td class="text-center fw-bold">{{ $row->total_qty }}</td>
                        <td class="text-end">{{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div style="text-align: right; margin-top: 50px; font-size: 12px; color: #777">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </div>

    @if(isset($isPrintMode) && $isPrintMode)
    <div class="no-print" style="margin-top: 30px; text-align: center;">
        <button onclick="window.print()"
            style="padding: 10px 20px; background: #0d6efd; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-size: 16px;">
            Cetak Sekarang
        </button>
    </div>
    <script>
        function requestPrint() {
            window.print();
        }
    </script>
    @endif
</body>

</html>