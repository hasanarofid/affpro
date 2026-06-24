<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cartService)
    {
        $cart = $cartService->getCart();
        $cart?->load('items.product.primaryImage', 'items.variant');
        return view('theme::cart', compact('cart'));
    }

    public function count(CartService $cartService)
    {
        $cart = $cartService->getCart();
        return response()->json(['count' => $cart?->items->sum('quantity') ?? 0]);
    }

    public function add(Request $request, CartService $cartService)
    {
        if (auth()->check() && auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Admin tidak dapat melakukan pesanan.']);
            }
            return redirect()->back()->with('error', 'Admin tidak dapat melakukan pesanan.');
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'integer|min:1',
        ]);

        $cartService->addItem(
            $request->product_id,
            $request->variant_id,
            $request->input('quantity', 1)
        );

        if ($request->filled('buy_now') && $request->input('buy_now') == '1') {
            return redirect()->route('checkout.index');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Produk ditambahkan ke keranjang.',
                'count' => $cartService->getCart()?->items->sum('quantity') ?? 0
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $itemId, CartService $cartService)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $cartService->updateItem($itemId, $request->quantity);
        if ($request->ajax()) {
            $cart = $cartService->getCartWithItems();
            return response()->json([
                'success' => true,
                'count' => $cart?->items->sum('quantity') ?? 0,
                'total' => number_format($cart->total, 0, ',', '.'),
                'total_items' => $cart->total_items,
                'item_subtotal' => number_format($cart->items->where('id', $itemId)->first()?->subtotal ?? 0, 0, ',', '.')
            ]);
        }
        return redirect()->route('cart.index');
    }

    public function remove(Request $request, int $itemId, CartService $cartService)
    {
        $cartService->removeItem($itemId);
        if ($request->ajax()) {
            $cart = $cartService->getCartWithItems();
            return response()->json([
                'success' => true,
                'count' => $cart?->items->sum('quantity') ?? 0,
                'total' => number_format($cart->total ?? 0, 0, ',', '.'),
                'total_items' => $cart->total_items ?? 0
            ]);
        }
        return redirect()->route('cart.index')->with('success', 'Item dihapus dari keranjang.');
    }
}
