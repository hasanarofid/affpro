<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Get or create cart for current user/session.
     */
    public function getCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate(['user_id' => Auth::id()]);
        }

        $sessionId = session()->getId();
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Add item to cart.
     */
    public function addItem(int $productId, ?int $variantId = null, int $qty = 1): CartItem
    {
        $cart = $this->getCart();

        // Check if item already exists
        $existing = $cart->items()
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existing) {
            $existing->increment('quantity', $qty);
            return $existing->fresh();
        }

        return $cart->items()->create([
            'product_id' => $productId,
            'product_variant_id' => $variantId,
            'quantity' => $qty,
        ]);
    }

    /**
     * Update item quantity.
     */
    public function updateItem(int $cartItemId, int $qty): ?CartItem
    {
        $cart = $this->getCart();
        $item = $cart->items()->find($cartItemId);

        if (!$item)
            return null;

        if ($qty <= 0) {
            $item->delete();
            return null;
        }

        $item->update(['quantity' => $qty]);
        return $item->fresh();
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(int $cartItemId): bool
    {
        $cart = $this->getCart();
        return $cart->items()->where('id', $cartItemId)->delete() > 0;
    }

    /**
     * Clear all items from cart.
     */
    public function clear(): void
    {
        $this->getCart()->items()->delete();
    }

    /**
     * Get cart with items loaded.
     */
    public function getCartWithItems(): Cart
    {
        return $this->getCart()->load(['items.product.primaryImage', 'items.variant.values']);
    }

    /**
     * Merge guest cart into user cart after login.
     */
    public function mergeGuestCart(): void
    {
        if (!Auth::check())
            return;

        $sessionId = session()->getId();
        $guestCart = Cart::where('session_id', $sessionId)->first();

        if (!$guestCart || $guestCart->items->isEmpty())
            return;

        $userCart = Cart::firstOrCreate(['user_id' => Auth::id()]);

        foreach ($guestCart->items as $guestItem) {
            $existing = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->where('product_variant_id', $guestItem->product_variant_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
            } else {
                $userCart->items()->create([
                    'product_id' => $guestItem->product_id,
                    'product_variant_id' => $guestItem->product_variant_id,
                    'quantity' => $guestItem->quantity,
                ]);
            }
        }

        $guestCart->items()->delete();
        $guestCart->delete();
    }

    /**
     * Validate stock availability for all cart items.
     */
    public function validateStock(): array
    {
        $errors = [];
        $cart = $this->getCartWithItems();

        foreach ($cart->items as $item) {
            $available = $item->variant
                ? $item->variant->stock
                : $item->product->stock;

            if ($item->quantity > $available) {
                $name = $item->product->name;
                if ($item->variant)
                    $name .= ' (' . $item->variant->label . ')';
                $errors[] = [
                    'item_id' => $item->id,
                    'product' => $name,
                    'requested' => $item->quantity,
                    'available' => $available,
                ];
            }
        }

        return $errors;
    }
}
