<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Admin: view invoice for any order.
     */
    public function adminInvoice(Request $request, Order $order)
    {
        $order->load(['items', 'user', 'shipment']);

        if ($request->query('download') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice', compact('order'));
            return $pdf->download('Invoice-' . $order->order_number . '.pdf');
        }

        return view('invoice', compact('order'));
    }

    /**
     * Customer: view invoice by order number.
     */
    public function customerInvoice(Request $request, string $orderNumber)
    {
        $order = Order::with(['items', 'user', 'shipment'])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        // Check ownership if logged in
        if (auth()->check() && $order->user_id && $order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($request->query('download') === 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice', compact('order'));
            return $pdf->download('Invoice-' . $order->order_number . '.pdf');
        }

        return view('invoice', compact('order'));
    }
}
