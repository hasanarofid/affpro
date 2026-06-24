<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function success(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        return view('theme::orders.success', compact('order'));
    }

    public function trackIndex()
    {
        return view('theme::orders.track-search');
    }

    public function trackProcess(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $orderNumber = trim($request->order_number);

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return back()->with('error', 'Pesanan tidak ditemukan. Periksa kembali nomor pesanan Anda.');
        }

        return redirect()->route('orders.track', $order->order_number);
    }


    public function track(string $orderNumber)
    {
        $order = Order::with(['items.product.primaryImage', 'items.variant', 'payments', 'shipment', 'user'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();
        return view('theme::orders.track', compact('order'));
    }

    public function payment(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.track', $order->order_number);
        }

        $val = app(\App\Services\SettingService::class)->get('bank_accounts', '[]');
        $bankAccounts = is_array($val) ? $val : (json_decode($val, true) ?: []);

        return view('theme::orders.payment', compact('order', 'bankAccounts'));
    }

    /**
     * Upload bukti transfer.
     */
    public function uploadPayment(Request $request, string $orderNumber)
    {
        $request->validate([
            'proof' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $order = Order::where('order_number', $orderNumber)
            ->where('payment_status', 'unpaid')
            ->firstOrFail();

        try {
            $file = $request->file('proof');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Pastikan direktori ada
            $directory = public_path('uploads/payment-proofs');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $file->move($directory, $filename);
            $path = 'uploads/payment-proofs/' . $filename;

            $order->payments()->create([
                'method' => 'bank_transfer',
                'amount' => $order->total,
                'status' => 'pending',
                'proof_image' => $path,
            ]);

            return redirect()->route('orders.track', $order->order_number)
                ->with('success', 'Bukti transfer berhasil diupload. Menunggu verifikasi admin.');
        } catch (\Exception $e) {
            Log::error('Upload proof error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses upload: ' . $e->getMessage());
        }
    }

    /**
     * Cancel order (only if pending).
     */
    public function cancel(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->whereIn('status', ['pending'])
            ->firstOrFail();

        // Check ownership
        if (auth()->check() && $order->user_id !== auth()->id()) {
            abort(403);
        }

        app(\App\Services\OrderService::class)->cancelOrder($order, 'Dibatalkan oleh pelanggan');

        return redirect()->route('orders.track', $order->order_number)
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }
}
