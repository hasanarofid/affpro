<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Order::with('user')
                ->select('orders.*')
                ->when($request->status, fn($q) => $q->where('status', $request->status));

            return DataTables::of($query)
                ->addColumn('order_number_link', function ($order) {
                    $url = route('admin.orders.show', $order);
                    return '<a href="' . $url . '" class="fw-bold text-decoration-none text-primary">' . $order->order_number . '</a>';
                })
                ->addColumn('customer_name_label', function ($order) {
                    return '<div class="fw-semibold text-dark">' . $order->customer_name . '</div>';
                })
                ->addColumn('total_format', function ($order) {
                    return '<div class="fw-bold text-dark">Rp ' . number_format($order->total, 0, ',', '.') . '</div>';
                })
                ->addColumn('payment_status_badge', function ($order) {
                    $payColors = ['unpaid' => 'warning', 'paid' => 'success', 'failed' => 'danger', 'refunded' => 'info'];
                    $payLabels = ['unpaid' => 'Belum Bayar', 'paid' => 'Terverifikasi', 'failed' => 'Gagal', 'refunded' => 'Dikembalikan'];
                    $color = $payColors[$order->payment_status] ?? 'secondary';
                    $label = $payLabels[$order->payment_status] ?? $order->payment_status;
                    return '<span class="badge badge-status bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('status_badge', function ($order) {
                    $statusColors = ['pending' => 'warning', 'confirmed' => 'info', 'processing' => 'primary', 'shipped' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger', 'expired' => 'dark'];
                    $color = $statusColors[$order->status] ?? 'secondary';
                    $label = __('order.status_' . $order->status);
                    return '<span class="badge badge-status bg-' . $color . '">' . $label . '</span>';
                })
                ->addColumn('date_format', function ($order) {
                    return '<td class="text-muted small"><div class="fw-medium">' . $order->created_at->format('d M Y') . '</div><div class="opacity-75" style="font-size: 0.7rem;">' . $order->created_at->format('H:i') . '</div></td>';
                })
                ->addColumn('action', function ($order) {
                    $url = route('admin.orders.show', $order);
                    return '
                        <div class="d-flex justify-content-end">
                            <a href="' . $url . '" class="btn btn-sm btn-light text-primary rounded-pill px-3" style="font-size: 0.75rem;" title="Detail Pesanan">
                                <i class="bi bi-eye me-1"></i> Detail
                            </a>
                        </div>
                    ';
                })
                ->rawColumns(['order_number_link', 'customer_name_label', 'total_format', 'payment_status_badge', 'status_badge', 'date_format', 'action'])
                ->make(true);
        }

        return view('admin.orders.index');
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'payments', 'shipment', 'user']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order, OrderService $orderService)
    {
        $request->validate(['status' => 'required|string']);

        if ($request->status === 'cancelled') {
            $orderService->cancelOrder($order, $request->input('reason', ''));
        } else {
            $orderService->updateStatus($order, $request->status);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function verifyPayment(Payment $payment, PaymentService $paymentService)
    {
        $paymentService->verifyManualTransfer($payment, auth()->id());
        return redirect()->route('admin.orders.show', $payment->order)
            ->with('success', 'Pembayaran berhasil diverifikasi.');
    }

    public function updateResi(Request $request, Order $order)
    {
        $request->validate(['tracking_number' => 'required|string']);

        $order->shipment()->update([
            'tracking_number' => $request->tracking_number,
            'status' => 'shipped',
        ]);

        $orderService = app(OrderService::class);
        $orderService->updateStatus($order, 'shipped');

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Nomor resi berhasil diperbarui.');
    }
}
