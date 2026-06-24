<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Events\OrderCreated;
use App\Events\OrderCancelled;
use App\Events\OrderStatusChanged;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order from cart.
     */
    public function createOrder(Cart $cart, array $shippingAddress, string $paymentMethod, array $options = []): Order
    {
        return DB::transaction(function () use ($cart, $shippingAddress, $paymentMethod, $options) {
            $cart->load(['items.product', 'items.variant']);
            $settingService = app(SettingService::class);

            // Calculate totals
            $rawSubtotal = 0;
            $productDiscount = 0;
            $totalWeight = 0;
            $orderItems = [];

            foreach ($cart->items as $item) {
                // Determine original price and selling (effective) price
                $basePrice = $item->variant ? $item->variant->price : $item->product->base_price;
                $effectivePrice = $item->variant ? $item->variant->price : ($item->product->discount_price ?? $item->product->base_price);

                $weight = ($item->variant && $item->variant->weight) ? $item->variant->weight : $item->product->weight;

                $itemRawSubtotal = $basePrice * $item->quantity;
                $itemEffectiveSubtotal = $effectivePrice * $item->quantity;

                $rawSubtotal += $itemRawSubtotal;
                $productDiscount += ($basePrice - $effectivePrice) * $item->quantity;
                $totalWeight += $weight * $item->quantity;

                $orderItems[] = [
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product->name,
                    'variant_label' => $item->variant ? $item->variant->label : null,
                    'price' => $effectivePrice,
                    'quantity' => $item->quantity,
                    'subtotal' => $itemEffectiveSubtotal,
                    'weight' => $weight,
                ];

                // Reduce stock and increment sold count
                if ($item->variant) {
                    $item->variant->decrement('stock', $item->quantity);
                } else {
                    $item->product->decrement('stock', $item->quantity);
                }
                $item->product->increment('sold_count', $item->quantity);
            }

            $shippingCost = $options['shipping_cost'] ?? 0;
            $voucherDiscount = $options['discount_amount'] ?? 0;

            // Total discount = product discount (price coret) + voucher discount
            $totalDiscount = $productDiscount + $voucherDiscount;

            $subtotal = $rawSubtotal;
            $total = $subtotal - $totalDiscount + $shippingCost;

            $expiryHours = $settingService->get('order_expiry_hours', 24);

            // Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $options['user_id'] ?? null,
                'guest_name' => $options['guest_name'] ?? null,
                'guest_phone' => $options['guest_phone'] ?? null,
                'guest_email' => $options['guest_email'] ?? null,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'notes' => $options['notes'] ?? null,
                'shipping_address' => $shippingAddress,
                'voucher_id' => $options['voucher_id'] ?? null,
                'affiliate_code' => $options['affiliate_code'] ?? null,
                'expires_at' => now()->addHours($expiryHours),
            ]);

            // Determine initial status based on payment method
            $initialStatus = 'pending';
            if ($paymentMethod === 'cod') {
                $initialStatus = 'processing';
            }

            // Create order items
            foreach ($orderItems as $oi) {
                $order->items()->create($oi);
            }

            // Create shipment if courier data provided
            if (!empty($options['courier'])) {
                $etd = $options['courier']['etd'] ?? null;
                $estimatedDays = null;

                if ($etd) {
                    // Extract numbers from string like "3-4", "3 day", "3-5 HARI"
                    preg_match_all('/\d+/', $etd, $matches);
                    if (!empty($matches[0])) {
                        // Take the maximum number if it's a range
                        $estimatedDays = (int) max($matches[0]);
                    }
                }

                $order->shipment()->create([
                    'courier_code' => $options['courier']['code'],
                    'courier_service' => $options['courier']['service'],
                    'shipping_cost' => $shippingCost,
                    'estimated_days' => $estimatedDays,
                    'status' => 'pending',
                ]);
            }

            // Apply direct status update if it's COD
            if ($paymentMethod === 'cod') {
                $order->update(['status' => 'processing']);
            }

            // Clear cart
            $cart->items()->delete();

            // Dispatch event
            event(new OrderCreated($order));

            return $order;
        });
    }

    /**
     * Cancel an order and restore stock.
     */
    public function cancelOrder(Order $order, string $reason = ''): bool
    {
        if (!$order->canTransitionTo('cancelled')) {
            return false;
        }

        return DB::transaction(function () use ($order, $reason) {
            // Restore stock and decrement sold count
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::where('id', $item->product_variant_id)
                        ->increment('stock', $item->quantity);
                } else {
                    Product::where('id', $item->product_id)
                        ->increment('stock', $item->quantity);
                }
                Product::where('id', $item->product_id)->decrement('sold_count', $item->quantity);
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            event(new OrderCancelled($order, $reason));

            return true;
        });
    }

    /**
     * Update order status with validation.
     */
    public function updateStatus(Order $order, string $newStatus): bool
    {
        if (!$order->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $order->status;

        $timestamps = [];
        if ($newStatus === 'shipped')
            $timestamps['shipped_at'] = now();
        if ($newStatus === 'delivered')
            $timestamps['delivered_at'] = now();
        if ($newStatus === 'cancelled')
            $timestamps['cancelled_at'] = now();

        $updateData = array_merge(['status' => $newStatus], $timestamps);

        // If admin updates to processing/shipped, mark as paid if it's currently unpaid
        if (in_array($newStatus, ['processing', 'shipped']) && $order->payment_status === 'unpaid') {
            $updateData['payment_status'] = 'paid';
            $updateData['paid_at'] = now();
        }

        $order->update($updateData);

        event(new OrderStatusChanged($order, $oldStatus, $newStatus));

        return true;
    }

    /**
     * Mark order as paid.
     */
    public function markAsPaid(Order $order): void
    {
        $order->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
            'status' => $order->status === 'pending' ? 'confirmed' : $order->status,
        ]);
    }

    /**
     * Cancel expired unpaid orders (called by scheduler).
     */
    public function cancelExpiredOrders(): int
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $expired */
        $expired = Order::expirable()->get();
        $count = 0;

        foreach ($expired as $order) {
            if ($this->cancelOrder($order, 'Pesanan kadaluarsa - tidak dibayar dalam batas waktu')) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Convenience: create order from cart data (checkout controller).
     */
    public function createFromCart(Cart $cart, array $data): Order
    {
        return $this->createOrder($cart, $data['shipping_address'] ?? [], $data['payment_method'] ?? 'manual_transfer', [
            'user_id' => auth()->id(),
            'guest_name' => $data['guest_name'] ?? null,
            'guest_phone' => $data['guest_phone'] ?? null,
            'guest_email' => $data['guest_email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'discount_amount' => $data['discount_amount'] ?? 0,
            'voucher_id' => $data['voucher_id'] ?? null,
            'shipping_cost' => $data['shipping_cost'] ?? 0,
            'courier' => $data['courier'] ?? null,
        ]);
    }

    /**
     * Generate unique order number.
     */
    private function generateOrderNumber(): string
    {
        $prefix = config('jadiorder.order_number_prefix', 'JO');
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));

        $number = "{$prefix}-{$date}-{$random}";

        // Ensure uniqueness
        while (Order::where('order_number', $number)->exists()) {
            $random = strtoupper(Str::random(4));
            $number = "{$prefix}-{$date}-{$random}";
        }

        return $number;
    }

    /**
     * Create a POS (point-of-sale) order from raw items array.
     *
     * Used by the admin POS screen. Items must be an array of:
     *   [
     *     'product_id' => int,
     *     'product_variant_id' => int|null,
     *     'quantity' => int,
     *     'price_override' => float|null, // optional manual price
     *   ]
     *
     * Options accepted:
     *   - customer_name, customer_phone (walk-in customer info)
     *   - payment_method (cash | manual_transfer | qris | other) — default 'cash'
     *   - payment_amount (cash tendered, optional)
     *   - discount_amount (manual extra discount)
     *   - notes
     *   - cashier_id (auth admin id)
     */
    public function createPosOrder(array $items, array $options = []): Order
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('POS order must have at least one item.');
        }

        return DB::transaction(function () use ($items, $options) {
            $rawSubtotal = 0;
            $productDiscount = 0;
            $orderItems = [];

            foreach ($items as $row) {
                $productId = (int) ($row['product_id'] ?? 0);
                $variantId = !empty($row['product_variant_id']) ? (int) $row['product_variant_id'] : null;
                $qty = max(1, (int) ($row['quantity'] ?? 1));

                /** @var Product|null $product */
                $product = Product::find($productId);
                if (!$product) {
                    throw new \RuntimeException("Produk #{$productId} tidak ditemukan.");
                }

                $variant = null;
                if ($variantId) {
                    $variant = ProductVariant::find($variantId);
                    if (!$variant || $variant->product_id !== $product->id) {
                        throw new \RuntimeException("Varian tidak valid untuk produk {$product->name}.");
                    }
                }

                // Stock check
                $availableStock = $variant ? (int) $variant->stock : (int) $product->stock;
                if (!$product->has_variants && $availableStock < $qty) {
                    throw new \RuntimeException("Stok {$product->name} tidak cukup (tersedia {$availableStock}).");
                }
                if ($variant && $availableStock < $qty) {
                    throw new \RuntimeException("Stok varian {$variant->label} tidak cukup (tersedia {$availableStock}).");
                }

                // Pricing
                $basePrice = $variant ? (float) $variant->price : (float) $product->base_price;
                $effectivePrice = $variant
                    ? (float) $variant->price
                    : (float) ($product->discount_price ?? $product->base_price);

                // Optional manual override (cashier discount)
                if (isset($row['price_override']) && $row['price_override'] !== '' && $row['price_override'] !== null) {
                    $effectivePrice = max(0, (float) $row['price_override']);
                }

                $weight = ($variant && $variant->weight) ? (int) $variant->weight : (int) $product->weight;

                $rawSubtotal     += $basePrice * $qty;
                $productDiscount += max(0, ($basePrice - $effectivePrice)) * $qty;

                $orderItems[] = [
                    'product_id'         => $product->id,
                    'product_variant_id' => $variant?->id,
                    'product_name'       => $product->name,
                    'variant_label'      => $variant?->label,
                    'price'              => $effectivePrice,
                    'quantity'           => $qty,
                    'subtotal'           => $effectivePrice * $qty,
                    'weight'             => $weight,
                ];

                // Decrement stock
                if ($variant) {
                    $variant->decrement('stock', $qty);
                } else {
                    $product->decrement('stock', $qty);
                }
                $product->increment('sold_count', $qty);
            }

            $manualDiscount = (float) ($options['discount_amount'] ?? 0);
            $totalDiscount = $productDiscount + $manualDiscount;

            $subtotal = $rawSubtotal;
            $total = max(0, $subtotal - $totalDiscount);

            $paymentMethod = $options['payment_method'] ?? 'cash';

            $order = Order::create([
                'order_number'    => $this->generateOrderNumber(),
                'user_id'         => $options['user_id'] ?? null,
                'guest_name'      => $options['customer_name'] ?? 'Walk-in Customer',
                'guest_phone'     => $options['customer_phone'] ?? null,
                'status'          => 'delivered', // POS = barang langsung diterima
                'payment_method'  => $paymentMethod,
                'payment_status'  => 'paid',
                'paid_at'         => now(),
                'subtotal'        => $subtotal,
                'discount_amount' => $totalDiscount,
                'shipping_cost'   => 0,
                'total'           => $total,
                'notes'           => $options['notes'] ?? null,
                'admin_notes'     => 'POS order — kasir: ' . (auth()->user()->name ?? 'system'),
                'shipping_address'=> null,
                'delivered_at'    => now(),
            ]);

            foreach ($orderItems as $oi) {
                $order->items()->create($oi);
            }

            event(new OrderCreated($order));

            return $order;
        });
    }
}
