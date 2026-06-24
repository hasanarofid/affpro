<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin POS (Point of Sale) controller.
 *
 * Provides a full-screen ordering interface for in-store sales:
 *   GET  /admin/pos          → POS UI (product picker + cart)
 *   GET  /admin/pos/products → JSON product search (ajax)
 *   POST /admin/pos/orders   → create order from POS cart
 */
class PosController extends Controller
{
    /**
     * Render the POS screen.
     */
    public function index()
    {
        $categories = Category::active()->orderBy('name')->get(['id', 'name']);
        return view('admin.pos.index', compact('categories'));
    }

    /**
     * AJAX product search (used by POS picker).
     *
     * Filters: ?q=, ?category_id=
     */
    public function products(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q', ''));
        $categoryId = $request->input('category_id');

        $products = Product::with(['primaryImage', 'variants', 'variantTypes.values'])
            ->where('is_active', true)
            ->when($categoryId, fn($qq) => $qq->where('category_id', $categoryId))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('sku', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(60)
            ->get();

        $rows = $products->map(function ($p) {
            return [
                'id'              => $p->id,
                'name'            => $p->name,
                'sku'             => $p->sku,
                'image'           => $p->primaryImage ? asset($p->primaryImage->path) : null,
                'base_price'      => (int) $p->base_price,
                'discount_price'  => $p->discount_price ? (int) $p->discount_price : null,
                'effective_price' => (int) ($p->discount_price ?? $p->base_price),
                'stock'           => (int) $p->stock,
                'has_variants'    => (bool) $p->has_variants,
                'variants'        => $p->has_variants
                    ? $p->variants->map(fn($v) => [
                        'id'    => $v->id,
                        'label' => $v->label,
                        'sku'   => $v->sku,
                        'price' => (int) $v->price,
                        'stock' => (int) $v->stock,
                    ])->values()
                    : [],
            ];
        });

        return response()->json(['data' => $rows]);
    }

    /**
     * Create a POS order.
     */
    public function storeOrder(Request $request, OrderService $orderService): JsonResponse
    {
        $request->validate([
            'items'                       => 'required|array|min:1',
            'items.*.product_id'          => 'required|integer|exists:products,id',
            'items.*.product_variant_id'  => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity'            => 'required|integer|min:1',
            'items.*.price_override'      => 'nullable|numeric|min:0',
            'customer_name'               => 'nullable|string|max:120',
            'customer_phone'              => 'nullable|string|max:30',
            'payment_method'              => 'nullable|string|in:cash,manual_transfer,qris,other',
            'payment_amount'              => 'nullable|numeric|min:0',
            'discount_amount'             => 'nullable|numeric|min:0',
            'notes'                       => 'nullable|string|max:500',
        ]);

        try {
            $order = $orderService->createPosOrder($request->input('items'), [
                'customer_name'   => $request->input('customer_name'),
                'customer_phone'  => $request->input('customer_phone'),
                'payment_method'  => $request->input('payment_method', 'cash'),
                'discount_amount' => (float) $request->input('discount_amount', 0),
                'notes'           => $request->input('notes'),
            ]);

            $change = 0;
            if ($request->filled('payment_amount')) {
                $change = max(0, ((float) $request->input('payment_amount')) - (float) $order->total);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan POS berhasil dibuat',
                'data' => [
                    'order_id'        => $order->id,
                    'order_number'    => $order->order_number,
                    'total'           => (int) $order->total,
                    'change'          => (int) $change,
                    'thermal_url'     => route('admin.pos.invoice.thermal', $order),
                    'pdf_url'         => route('admin.pos.invoice.pdf', $order),
                    'web_invoice_url' => route('admin.orders.invoice', $order),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Render thermal-style invoice (auto-print friendly).
     */
    public function thermalInvoice(Order $order)
    {
        $order->load('items');
        $width = request('width', '80'); // 58 | 80
        return view('admin.pos.invoice-thermal', compact('order', 'width'));
    }

    /**
     * Download A4 PDF invoice (re-uses existing invoice template).
     */
    public function pdfInvoice(Order $order)
    {
        $order->load(['items', 'user', 'shipment']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice', compact('order'));
        return $pdf->download('Invoice-' . $order->order_number . '.pdf');
    }
}
