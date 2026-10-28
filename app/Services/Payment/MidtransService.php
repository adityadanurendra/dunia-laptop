<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Throwable;

class MidtransService
{
    public function __construct()
    {
        $this->configureMidtrans();
    }

    /**
     * Configure Midtrans global parameters safely.
     */
    protected function configureMidtrans(): void
    {
        Config::$serverKey = (string) config('services.midtrans.server_key', '');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = (bool) config('services.midtrans.is_sanitized', true);
        Config::$is3ds = (bool) config('services.midtrans.is_3ds', true);
    }

    /**
     * Get Midtrans Client Key for client-side frontend Snap integration.
     */
    public function getClientKey(): string
    {
        return (string) config('services.midtrans.client_key', '');
    }

    /**
     * Get Midtrans Server Key.
     */
    public function getServerKey(): string
    {
        return (string) config('services.midtrans.server_key', '');
    }

    /**
     * Return the appropriate Midtrans Snap JS URL based on environment.
     */
    public function getSnapJsUrl(): string
    {
        return config('services.midtrans.is_production', false)
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    /**
     * Build parameters array for Midtrans Snap API.
     */
    public function buildTransactionParams(object $order): array
    {
        $items = DB::table('order_items')->where('order_id', $order->id)->get();
        $user = DB::table('users')->where('id', $order->user_id)->first();
        $shipment = DB::table('shipments')->where('order_id', $order->id)->first();

        $itemDetails = [];
        foreach ($items as $item) {
            $itemDetails[] = [
                'id' => (string) ($item->product_sku ?: $item->product_id),
                'price' => (int) round((float) $item->unit_price),
                'quantity' => (int) $item->quantity,
                'name' => Str::limit($item->product_name, 50, ''),
            ];
        }

        if ((float) $order->shipping_cost > 0) {
            $itemDetails[] = [
                'id' => 'SHIPPING-FEE',
                'price' => (int) round((float) $order->shipping_cost),
                'quantity' => 1,
                'name' => 'Ongkos Kirim Kurir (' . strtoupper($shipment->courier_code ?? 'KURIR') . ')',
            ];
        }

        if ((float) $order->discount_amount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) round((float) $order->discount_amount),
                'quantity' => 1,
                'name' => 'Diskon Voucher Belanja',
            ];
        }

        $recipientName = $shipment->recipient_name ?? ($user->name ?? 'Pelanggan Dunia Laptop');
        $recipientPhone = $shipment->recipient_phone ?? ($user->phone ?? '081234567890');

        return [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) round((float) $order->total_amount),
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $recipientName,
                'email' => $user->email ?? 'customer@dunialaptop.com',
                'phone' => $recipientPhone,
                'billing_address' => [
                    'first_name' => $recipientName,
                    'phone' => $recipientPhone,
                    'address' => $shipment->destination_address ?? 'Indonesia',
                ],
                'shipping_address' => [
                    'first_name' => $recipientName,
                    'phone' => $recipientPhone,
                    'address' => $shipment->destination_address ?? 'Indonesia',
                ],
            ],
        ];
    }

    /**
     * Create or retrieve an existing Snap transaction token for an order.
     */
    public function createSnapTransaction(object $order): array
    {
        // 1. Check if a valid snap token is already saved in payments table
        $existingPayment = DB::table('payments')
            ->where('order_id', $order->id)
            ->where('payment_gateway', 'midtrans')
            ->where('transaction_status', 'pending')
            ->orderByDesc('id')
            ->first();

        if ($existingPayment && ! empty($existingPayment->raw_response)) {
            $decoded = json_decode($existingPayment->raw_response, true);
            if (! empty($decoded['snap_token'])) {
                return [
                    'token' => $decoded['snap_token'],
                    'redirect_url' => $decoded['redirect_url'] ?? null,
                    'client_key' => $this->getClientKey(),
                    'snap_js_url' => $this->getSnapJsUrl(),
                ];
            }
        }

        $params = $this->buildTransactionParams($order);
        $serverKey = $this->getServerKey();

        // 2. Offline / Testing fallback when server key is mock or dummy
        if (empty($serverKey) || str_starts_with($serverKey, 'dummy-') || app()->environment('testing')) {
            $snapToken = 'snap-token-' . Str::random(24);
            $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v2/vtweb/' . $snapToken;
        } else {
            try {
                $this->configureMidtrans();
                $snapResponse = Snap::createTransaction($params);
                $snapToken = $snapResponse->token;
                $redirectUrl = $snapResponse->redirect_url ?? null;
            } catch (Throwable $e) {
                Log::error('Midtrans Snap Error: ' . $e->getMessage(), [
                    'order_number' => $order->order_number,
                ]);
                throw $e;
            }
        }

        // 3. Store snapshot in payments table via DB facade
        $rawPayload = json_encode([
            'snap_token' => $snapToken,
            'redirect_url' => $redirectUrl,
            'params' => $params,
        ]);

        if ($existingPayment) {
            DB::table('payments')->where('id', $existingPayment->id)->update([
                'transaction_reference' => $snapToken,
                'gross_amount' => $order->total_amount,
                'raw_response' => $rawPayload,
                'updated_at' => now(),
            ]);
        } else {
            DB::table('payments')->insert([
                'order_id' => $order->id,
                'transaction_reference' => $snapToken,
                'payment_gateway' => 'midtrans',
                'payment_type' => null,
                'gross_amount' => $order->total_amount,
                'transaction_status' => 'pending',
                'raw_response' => $rawPayload,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'token' => $snapToken,
            'redirect_url' => $redirectUrl,
            'client_key' => $this->getClientKey(),
            'snap_js_url' => $this->getSnapJsUrl(),
        ];
    }

    /**
     * Validate official SHA512 signature from Midtrans notification payload.
     * Formula: SHA512(order_id + status_code + gross_amount + ServerKey)
     */
    public function verifySignature(string $orderNumber, string $statusCode, string $grossAmount, string $receivedSignature): bool
    {
        $serverKey = $this->getServerKey();
        $expected = hash('sha512', $orderNumber . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expected, trim($receivedSignature));
    }

    /**
     * Process asynchronous webhook notification with strict idempotency and stock restitution.
     */
    public function handleNotification(array $payload): array
    {
        $orderNumber = $payload['order_id'] ?? null;
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $paymentType = $payload['payment_type'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;
        $transactionTime = $payload['transaction_time'] ?? null;
        $settlementTime = $payload['settlement_time'] ?? null;

        if (empty($orderNumber) || empty($statusCode) || empty($grossAmount) || empty($signatureKey)) {
            return [
                'success' => false,
                'message' => 'Format payload notifikasi tidak lengkap.',
            ];
        }

        // 1. Signature Verification (FR-22)
        if (! $this->verifySignature($orderNumber, $statusCode, $grossAmount, $signatureKey)) {
            Log::warning("Midtrans Webhook: Signature mismatch untuk pesanan {$orderNumber}", [
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => 'Verifikasi signature SHA512 gagal.',
            ];
        }

        // 2. Idempotent Status Handling inside DB::transaction (FR-23, FR-25, FR-31)
        return DB::transaction(function () use (
            $orderNumber,
            $transactionStatus,
            $fraudStatus,
            $paymentType,
            $grossAmount,
            $transactionId,
            $transactionTime,
            $settlementTime,
            $payload
        ) {
            $order = DB::table('orders')
                ->where('order_number', $orderNumber)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                return [
                    'success' => false,
                    'message' => "Pesanan dengan nomor {$orderNumber} tidak ditemukan.",
                ];
            }

            $currentOrderStatus = $order->status;
            $newOrderStatus = $currentOrderStatus;
            $newPaymentStatus = 'pending';
            $stockRestored = false;

            // Map status Midtrans to system enums
            if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
                $newPaymentStatus = 'settlement';
                // Only advance to processing if not already shipped or completed
                if (in_array($currentOrderStatus, ['pending', 'processing'], true)) {
                    $newOrderStatus = 'processing';
                }
            } elseif ($transactionStatus === 'pending') {
                $newPaymentStatus = 'pending';
                if ($currentOrderStatus === 'pending') {
                    $newOrderStatus = 'pending';
                }
            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
                $newPaymentStatus = ($transactionStatus === 'expire') ? 'expire' : 'cancel';
                $newOrderStatus = 'cancelled';

                // Stock Restitution: Only restore stock if the order was NOT previously cancelled (Idempotency)
                if ($currentOrderStatus !== 'cancelled') {
                    $orderItems = DB::table('order_items')->where('order_id', $order->id)->get();
                    foreach ($orderItems as $item) {
                        DB::table('products')
                            ->where('id', $item->product_id)
                            ->increment('stock', (int) $item->quantity);
                    }
                    $stockRestored = true;
                    Log::info("Midtrans Webhook: Stok berhasil dikembalikan untuk pesanan {$orderNumber} ({$transactionStatus})");
                }
            } else {
                $newPaymentStatus = $transactionStatus;
            }

            // Update order status if changed
            if ($newOrderStatus !== $currentOrderStatus) {
                DB::table('orders')->where('id', $order->id)->update([
                    'status' => $newOrderStatus,
                    'updated_at' => now(),
                ]);
            }

            // Update payments record
            $payment = DB::table('payments')->where('order_id', $order->id)->first();
            $resolvedSettlementTime = ($newPaymentStatus === 'settlement')
                ? ($settlementTime ?: now())
                : null;

            if ($payment) {
                DB::table('payments')->where('id', $payment->id)->update([
                    'transaction_reference' => $transactionId ?: $payment->transaction_reference,
                    'payment_type' => $paymentType ?: $payment->payment_type,
                    'gross_amount' => (float) $grossAmount,
                    'transaction_status' => $newPaymentStatus,
                    'transaction_time' => $transactionTime ?: $payment->transaction_time,
                    'settlement_time' => $resolvedSettlementTime ?: $payment->settlement_time,
                    'raw_response' => json_encode($payload),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('payments')->insert([
                    'order_id' => $order->id,
                    'transaction_reference' => $transactionId ?: ('MID-' . time()),
                    'payment_gateway' => 'midtrans',
                    'payment_type' => $paymentType,
                    'gross_amount' => (float) $grossAmount,
                    'transaction_status' => $newPaymentStatus,
                    'transaction_time' => $transactionTime ?: now(),
                    'settlement_time' => $resolvedSettlementTime,
                    'raw_response' => json_encode($payload),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'success' => true,
                'order_number' => $orderNumber,
                'order_status' => $newOrderStatus,
                'payment_status' => $newPaymentStatus,
                'stock_restored' => $stockRestored,
            ];
        });
    }
}

