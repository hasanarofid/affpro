<?php

namespace Modules\XenditPayment\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    /**
     * Handle Xendit invoice callback/webhook.
     * POST /api/payment/xendit/callback
     */
    public function callback(Request $request, PaymentService $paymentService)
    {
        Log::info('Xendit webhook incoming', $request->all());

        $result = $paymentService->handleCallback('xendit', $request);

        // Always return 200 to Xendit so they don't retry
        return response()->json([
            'status' => $result->success ? 'ok' : 'error',
            'message' => $result->message,
        ], 200);
    }
}
