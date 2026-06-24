<?php

namespace Modules\MidtransPayment\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Handle Midtrans notification callback.
     * POST /api/payment/midtrans/callback
     *
     * Midtrans sends JSON notification for every transaction status change:
     * - pending, capture, settlement, deny, cancel, expire, refund
     */
    public function callback(Request $request, PaymentService $paymentService)
    {
        Log::info('Midtrans webhook incoming', $request->all());

        $result = $paymentService->handleCallback('midtrans', $request);

        // Always return 200 to Midtrans so they don't retry
        return response()->json([
            'status' => $result->success ? 'ok' : 'error',
            'message' => $result->message,
        ], 200);
    }
}
