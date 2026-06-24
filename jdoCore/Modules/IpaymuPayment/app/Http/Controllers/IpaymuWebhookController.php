<?php

namespace Modules\IpaymuPayment\app\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class IpaymuWebhookController extends Controller
{
    public function callback(Request $request, PaymentService $paymentService)
    {
        Log::info('iPaymu webhook incoming', $request->all());

        $result = $paymentService->handleCallback('ipaymu', $request);

        return response()->json([
            'status' => $result->success ? 'success' : 'error',
            'message' => $result->message,
        ], 200);
    }
}