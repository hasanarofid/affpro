<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    private function getDates(Request $request)
    {
        return [
            $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d')),
            $request->input('end_date', Carbon::now()->format('Y-m-d'))
        ];
    }

    private function handleExport(Request $request, $type, $data, $startDate, $endDate)
    {
        if ($request->has('export')) {
            $exportFormat = $request->input('export');
            if ($exportFormat === 'pdf') {
                return $this->exportPdf($type, $data, $startDate, $endDate);
            } elseif ($exportFormat === 'excel') {
                return $this->exportExcel($type, $data, $startDate, $endDate);
            } elseif ($exportFormat === 'print') {
                $isPrintMode = true;
                return view('admin.reports.print', compact('type', 'data', 'startDate', 'endDate', 'isPrintMode'));
            }
        }
        return null;
    }

    public function transactions(Request $request)
    {
        [$startDate, $endDate] = $this->getDates($request);
        $status = $request->input('status', 'all');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $query = Order::with('user')->whereBetween('created_at', [$start, $end]);
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $data = $query->latest()->get();

        if ($exportResponse = $this->handleExport($request, 'transactions', $data, $startDate, $endDate)) {
            return $exportResponse;
        }

        return view('admin.reports.transactions', compact('startDate', 'endDate', 'status', 'data'));
    }

    public function users(Request $request)
    {
        [$startDate, $endDate] = $this->getDates($request);
        $userId = $request->input('user_id');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $query = Order::with('user')
            ->whereNotNull('user_id')
            ->whereBetween('created_at', [$start, $end]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $data = $query->select('user_id', DB::raw('count(*) as total_orders'), DB::raw('sum(total) as sum_total'))
            ->groupBy('user_id')
            ->orderByDesc('sum_total')
            ->get();

        if ($exportResponse = $this->handleExport($request, 'users', $data, $startDate, $endDate)) {
            return $exportResponse;
        }

        $selectedUser = $userId ? User::find($userId) : null;

        return view('admin.reports.users', compact('startDate', 'endDate', 'userId', 'selectedUser', 'data'));
    }

    public function profitLoss(Request $request)
    {
        [$startDate, $endDate] = $this->getDates($request);

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $data = Order::where('status', 'delivered')
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('sum(subtotal) as total_subtotal'),
                DB::raw('sum(discount_amount) as total_discount'),
                DB::raw('sum(shipping_cost) as total_shipping'),
                DB::raw('sum(total) as total_sales_revenue'),
                DB::raw('count(*) as total_orders')
            )->first();

        if ($exportResponse = $this->handleExport($request, 'profit_loss', $data, $startDate, $endDate)) {
            return $exportResponse;
        }

        return view('admin.reports.profit_loss', compact('startDate', 'endDate', 'data'));
    }

    public function products(Request $request)
    {
        [$startDate, $endDate] = $this->getDates($request);
        $productId = $request->input('product_id');
        $orderStatus = $request->input('order_status', 'completed'); // default to completed

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $query = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$start, $end]);

        if ($orderStatus === 'active') {
            $query->whereIn('orders.status', ['pending', 'processing', 'confirmed', 'shipped']);
        } elseif ($orderStatus === 'completed') {
            $query->where('orders.status', 'delivered');
        } elseif ($orderStatus === 'cancelled') {
            $query->whereIn('orders.status', ['cancelled', 'expired']);
        } else { // default to 'all_except_cancelled' 
            $query->whereNotIn('orders.status', ['cancelled', 'expired']);
        }

        if ($productId) {
            $query->where('order_items.product_id', $productId);
        }

        $data = $query->select('order_items.product_name', 'order_items.variant_label', DB::raw('sum(order_items.quantity) as total_qty'), DB::raw('sum(order_items.subtotal) as total_revenue'))
            ->groupBy('order_items.product_name', 'order_items.variant_label')
            ->orderByDesc('total_qty')
            ->get();

        if ($exportResponse = $this->handleExport($request, 'products', $data, $startDate, $endDate)) {
            return $exportResponse;
        }

        $selectedProduct = $productId ? \App\Models\Product::find($productId) : null;

        return view('admin.reports.products', compact('startDate', 'endDate', 'productId', 'selectedProduct', 'orderStatus', 'data'));
    }

    public function searchUsers(Request $request)
    {
        $term = $request->input('q');
        $users = User::where('role', 'user')
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            })
            ->limit(30)
            ->get();

        return response()->json([
            'results' => $users->map(fn($row) => ['id' => $row->id, 'text' => $row->name . ' (' . $row->email . ')'])
        ]);
    }

    public function searchProducts(Request $request)
    {
        $term = $request->input('q');
        $products = \App\Models\Product::where('name', 'like', "%{$term}%")
            ->limit(30)
            ->get();

        return response()->json([
            'results' => $products->map(fn($row) => ['id' => $row->id, 'text' => $row->name])
        ]);
    }

    private function exportPdf($type, $data, $startDate, $endDate)
    {
        $isPrintMode = false;
        $pdf = Pdf::loadView('admin.reports.print', compact('type', 'data', 'startDate', 'endDate', 'isPrintMode'));
        return $pdf->download("laporan_{$type}_{$startDate}_{$endDate}.pdf");
    }

    private function exportExcel($type, $data, $startDate, $endDate)
    {
        $filename = "laporan_{$type}_{$startDate}_{$endDate}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($type, $data) {
            $file = fopen('php://output', 'w');

            switch ($type) {
                case 'transactions':
                    fputcsv($file, ['No', 'Tanggal', 'Nomor Order', 'Pelanggan', 'Status', 'Total (Rp)']);
                    foreach ($data as $i => $row) {
                        fputcsv($file, [
                            $i + 1,
                            $row->created_at->format('d/m/Y H:i'),
                            $row->order_number,
                            $row->customer_name,
                            $row->status,
                            $row->total
                        ]);
                    }
                    break;
                case 'users':
                    fputcsv($file, ['No', 'Pelanggan', 'Email', 'Total Transaksi', 'Total Nilai (Rp)']);
                    foreach ($data as $i => $row) {
                        fputcsv($file, [
                            $i + 1,
                            $row->user->name ?? '-',
                            $row->user->email ?? '-',
                            $row->total_orders,
                            $row->sum_total
                        ]);
                    }
                    break;
                case 'profit_loss':
                    fputcsv($file, ['Deskripsi', 'Nilai (Rp)']);
                    fputcsv($file, ['Total Pesanan Lunas/Sukses', $data->total_orders]);
                    fputcsv($file, ['Total Subtotal Penjualan', $data->total_subtotal]);
                    fputcsv($file, ['Total Diskon Diberikan', $data->total_discount]);
                    fputcsv($file, ['Total Pendapatan Ongkir', $data->total_shipping]);
                    fputcsv($file, ['TOTAL PENDAPATAN BERSIH', $data->total_sales_revenue]);
                    break;
                case 'products':
                    fputcsv($file, ['No', 'Nama Produk', 'Varian', 'Terjual (pcs)', 'Total Pendapatan (Rp)']);
                    foreach ($data as $i => $row) {
                        fputcsv($file, [
                            $i + 1,
                            $row->product_name,
                            $row->variant_label ?? '-',
                            $row->total_qty,
                            $row->total_revenue
                        ]);
                    }
                    break;
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
