<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\VoucherService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index(CartService $cartService, PaymentService $paymentService)
    {
        if (auth()->check() && auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            return redirect()->route('home')->with('error', 'Admin tidak dapat melakukan pesanan.');
        }

        $cart = $cartService->getCart();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang Anda kosong.');
        }

        $cart->load('items.product.primaryImage', 'items.variant');

        $addresses = collect();
        if (auth()->check()) {
            $addresses = auth()->user()->addresses()->latest()->get();
        }

        // Determine which gateway modules are active (for dynamic checkout UI)
        $hasGateway = $paymentService->hasGatewayProvider();
        $gatewayProviders = array_keys($paymentService->getRegisteredProviders());
        $gatewayName = $paymentService->getActiveGatewayName(); // first provider name

        return view('theme::checkout', compact('cart', 'addresses', 'hasGateway', 'gatewayProviders', 'gatewayName'));
    }

    /**
     * AJAX: validate and preview voucher discount.
     */
    public function applyVoucher(Request $request, VoucherService $voucherService)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'subtotal' => 'required|numeric|min:0',
            'shipping_cost' => 'nullable|numeric|min:0',
            'expected_type' => 'nullable|string|in:product,shipping',
        ]);

        $result = $voucherService->apply(
            $request->code,
            (float) $request->subtotal,
            (float) $request->input('shipping_cost', 0),
        );

        if ($result['valid'] && $request->filled('expected_type')) {
            $expected = $request->input('expected_type');
            if ($result['discount_type'] !== $expected) {
                $typeStr = $expected === 'shipping' ? 'Ongkir' : 'Produk';
                $result = [
                    'valid' => false,
                    'message' => "Kode ini bukan Voucher {$typeStr}.",
                ];
            }
        }

        return response()->json($result);
    }

    public function process(Request $request, CartService $cartService, OrderService $orderService, PaymentService $paymentService)
    {
        $settings = app(\App\Services\SettingService::class);
        if ($request->filled('guest_phone')) {
            $request->merge(['guest_phone' => $settings->formatPhone($request->guest_phone)]);
        }

        if (auth()->check() && auth()->user()->hasAnyRole(['admin', 'superadmin'])) {
            return redirect()->route('home')->with('error', 'Admin tidak dapat melakukan pesanan.');
        }

        $cart = $cartService->getCart();
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang kosong.');
        }

        $rules = [
            'payment_method' => 'required|string|in:manual_transfer,cod,gateway,wallet',
        ];

        if (!auth()->check()) {
            $rules['guest_name'] = 'required|string';
            $rules['guest_phone'] = 'required|string';
        }

        if (!$cart->is_digital_only) {
            if (auth()->check()) {
                $rules['address_id'] = 'required|exists:user_addresses,id,user_id,' . auth()->id();
            } else {
                $rules['shipping.address'] = 'required|string';
                $rules['shipping.city'] = 'required|string';
                $rules['shipping.province'] = 'required|string';
            }
        }

        $request->validate($rules);

        // Wallet payment requires authenticated user
        if ($request->input('payment_method') === 'wallet') {
            if (!auth()->check()) {
                return back()->with('error', 'Silakan login untuk membayar memakai saldo wallet.');
            }
            $walletEnabled = app(\App\Services\SettingService::class)->get('payment_method_wallet', '1') == '1';
            if (!$walletEnabled) {
                return back()->with('error', 'Pembayaran via saldo sedang dinonaktifkan.');
            }
        }

        $shippingAddress = [];
        if (!$cart->is_digital_only) {
            if ($request->filled('address_id')) {
                $addressRow = \App\Models\UserAddress::find($request->address_id);
                $shippingAddress = [
                    'name' => $addressRow->recipient_name,
                    'phone' => $addressRow->phone,
                    'address' => $addressRow->address_line,
                    'city' => $addressRow->city,
                    'province' => $addressRow->province,
                    'postal_code' => $addressRow->postal_code,
                ];
            } else {
                $shippingAddress = $request->input('shipping');
                if (empty($shippingAddress['name'])) {
                    $shippingAddress['name'] = $request->input('guest_name');
                }
                if (empty($shippingAddress['phone'])) {
                    $shippingAddress['phone'] = $request->input('guest_phone');
                }
            }
        }

        // Determine selected courier if applicable
        $shippingCost = 0;
        $courierData = null;

        if (!$cart->is_digital_only && $request->filled('shipping_cost') && $request->has('courier_info')) {
            $shippingCost = (float) $request->input('shipping_cost');

            $courierInfoArr = $request->input('courier_info');
            if (is_array($courierInfoArr) && count($courierInfoArr) > 0) {
                $courierData = reset($courierInfoArr);
            }
        }

        // Handle voucher (must run AFTER shipping cost is known so shipping vouchers work)
        $voucherService = app(VoucherService::class);
        $discountAmount = 0;     // potongan produk
        $shippingDiscount = 0;   // potongan ongkir
        $voucherId = null;
        $shippingVoucherId = null;

        if ($request->filled('product_voucher_code')) {
            $voucherResult = $voucherService->apply($request->product_voucher_code, $cart->total, $shippingCost);
            if ($voucherResult['valid'] && $voucherResult['discount_type'] !== 'shipping') {
                $voucherId = $voucherResult['voucher']->id;
                $discountAmount = (float) $voucherResult['discount'];
                $voucherService->markUsed($voucherResult['voucher']);
            }
        }

        if ($request->filled('shipping_voucher_code')) {
            $voucherResult = $voucherService->apply($request->shipping_voucher_code, $cart->total, $shippingCost);
            if ($voucherResult['valid'] && $voucherResult['discount_type'] === 'shipping') {
                $shippingVoucherId = $voucherResult['voucher']->id;
                $shippingDiscount = (float) $voucherResult['discount'];
                $voucherService->markUsed($voucherResult['voucher']);
            }
        }

        $order = $orderService->createFromCart($cart, [
            'guest_name' => $request->input('guest_name'),
            'guest_phone' => $request->input('guest_phone'),
            'guest_email' => $request->input('guest_email'),
            'payment_method' => $request->input('payment_method'),
            'shipping_address' => $shippingAddress,
            'notes' => $request->input('notes'),
            'discount_amount' => $discountAmount,
            'voucher_id' => $voucherId,
            'shipping_discount_amount' => $shippingDiscount,
            'shipping_voucher_id' => $shippingVoucherId,
            'shipping_cost' => $shippingCost,
            'courier' => $courierData,
        ]);

        // Wallet: deduct balance & mark order as paid (langsung lunas)
        if ($request->input('payment_method') === 'wallet') {
            $user = auth()->user();
            $wallet = $user->wallet ?? $user->wallet()->firstOrCreate(['user_id' => $user->id], ['balance' => 0]);

            if ((float) $wallet->balance < (float) $order->total) {
                // Rollback: cancel order so stock is restored
                $orderService->cancelOrder($order, 'Saldo wallet tidak cukup');
                return back()->with('error', 'Saldo wallet tidak cukup. Silakan top up terlebih dahulu.');
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($wallet, $order) {
                $wallet->withdraw(
                    (float) $order->total,
                    'Pembayaran pesanan ' . $order->order_number,
                    $order->order_number,
                    'completed'
                );

                $order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => $order->status === 'pending' ? 'processing' : $order->status,
                ]);
            });

            return redirect()->route('orders.success', $order->order_number)
                ->with('success', 'Pembayaran berhasil menggunakan saldo wallet.');
        }

        // If payment method is gateway, charge via the specified gateway provider & redirect
        if ($request->input('payment_method') === 'gateway') {
            // Determine which provider to use:
            // 1. The user explicitly selected one (gateway_provider field) 
            // 2. Or fall back to the first registered provider
            $providerName = $request->input('gateway_provider') ?: $paymentService->getActiveGatewayName();

            if ($providerName && $paymentService->resolveProvider($providerName)) {
                $order->load('items'); // Ensure items are loaded for charge payload
                $chargeResult = $paymentService->charge($order, $providerName);

                if ($chargeResult->success && $chargeResult->redirectUrl) {
                    return redirect()->away($chargeResult->redirectUrl);
                }

                // If charge failed, redirect to payment page with error
                return redirect()->route('orders.payment', $order->order_number)
                    ->with('error', $chargeResult->message ?? 'Gagal membuat pembayaran otomatis. Silakan coba lagi.');
            }

            // No provider found — fall through to manual success page
            return redirect()->route('orders.success', $order->order_number)
                ->with('warning', 'Modul payment gateway tidak ditemukan. Gunakan metode pembayaran lain.');
        }

        return redirect()->route('orders.success', $order->order_number);
    }
}
