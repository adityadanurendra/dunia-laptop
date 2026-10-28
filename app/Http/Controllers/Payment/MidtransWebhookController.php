<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Payment\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    /**
     * Handle incoming asynchronous webhook notifications from Midtrans.
     * Note: Exempt from CSRF verification in bootstrap/app.php.
     */
    public function handle(Request $request, MidtransService $midtransService): JsonResponse
    {
        $payload = $request->all();

        if (empty($payload)) {
            $raw = file_get_contents('php://input');
            $payload = json_decode((string) $raw, true) ?? [];
        }

        Log::info('Midtrans Webhook Received', [
            'order_id' => $payload['order_id'] ?? null,
            'status' => $payload['transaction_status'] ?? null,
        ]);

        $result = $midtransService->handleNotification($payload);

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => $result['message'] ?? 'Notification processing failed.',
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment status updated successfully.',
            'data' => $result,
        ], 200);
    }
}

