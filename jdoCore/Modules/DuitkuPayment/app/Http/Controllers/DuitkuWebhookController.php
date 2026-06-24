<?php

namespace Modules\DuitkuPayment\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DuitkuWebhookController extends Controller
{
    /**
     * Handle Duitku callback notification.
     * POST /api/payment/duitku/callback
     */
    public function callback(Request $request, PaymentService $paymentService)
    {
        Log::info('Duitku webhook incoming', $request->all());

        $result = $paymentService->handleCallback('duitku', $request);

        // Duitku expects a plain "SUCCESS" string response on successful processing
        if ($result->success) {
            return response('SUCCESS', 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result->message,
        ], 200);
    }
}
