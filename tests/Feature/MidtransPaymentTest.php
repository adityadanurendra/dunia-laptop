<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\Shipment;
use App\Models\User;
use App\Services\Payment\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $product;
    protected Order $order;
    protected string $testServerKey = 'SB-Mid-server-test-key-12345';
    protected string $testClientKey = 'SB-Mid-client-test-key-67890';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Config::set('services.midtrans.server_key', $this->testServerKey);
        Config::set('services.midtrans.client_key', $this->testClientKey);
        Config::set('services.midtrans.is_production', false);

        $this->customer = User::where('role', UserRole::CUSTOMER)->first();

        $category = Category::first();
        $brand = Brand::first();

        $this->product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Lenovo Legion 5 Pro',
            'slug' => 'lenovo-legion-5-pro',
            'sku' => 'LAP-LENOVO-LEGION-01',
            'condition' => 'new',
            'price' => 20000000,
            'stock' => 5,
            'weight_grams' => 2500,
            'description' => 'Laptop gaming bertenaga',
            'status' => 'published',
        ]);

        ProductSpecification::create([
            'product_id' => $this->product->id,
            'processor' => 'AMD Ryzen 7 7745HX',
            'ram_gb' => 32,
            'storage_capacity' => '1TB SSD',
            'gpu' => 'NVIDIA RTX 4070 8GB',
            'screen_size' => 16.0,
        ]);

        $address = Address::where('user_id', $this->customer->id)->first();

        // Create standard order
        $this->order = Order::create([
            'order_number' => 'DL-20261028-TEST01',
            'user_id' => $this->customer->id,
            'subtotal' => 20000000,
            'shipping_cost' => 50000,
            'discount_amount' => 0,
            'total_amount' => 20050000,
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'unit_price' => 20000000,
            'quantity' => 1,
            'subtotal' => 20000000,
            'snapshot_specs' => ['processor' => 'AMD Ryzen 7 7745HX'],
        ]);

        Shipment::create([
            'order_id' => $this->order->id,
            'courier_code' => 'jne',
            'courier_service' => 'REG',
            'shipping_cost' => 50000,
            'status' => 'pending',
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone,
            'destination_address' => $address->address_line . ', ' . $address->city,
        ]);
    }

    public function test_midtrans_service_builds_accurate_transaction_parameters(): void
    {
        $midtransService = app(MidtransService::class);
        $params = $midtransService->buildTransactionParams($this->order);

        $this->assertEquals($this->order->order_number, $params['transaction_details']['order_id']);
        $this->assertEquals(20050000, $params['transaction_details']['gross_amount']);
        $this->assertCount(2, $params['item_details']); // 1 product item + 1 shipping item
        $this->assertEquals(20000000, $params['item_details'][0]['price']);
        $this->assertEquals(50000, $params['item_details'][1]['price']);
        $this->assertEquals($this->customer->email, $params['customer_details']['email']);
    }

    public function test_midtrans_service_verifies_sha512_signature_correctly(): void
    {
        $midtransService = app(MidtransService::class);

        $orderNumber = $this->order->order_number;
        $statusCode = '200';
        $grossAmount = '20050000.00';

        $validSignature = hash('sha512', $orderNumber . $statusCode . $grossAmount . $this->testServerKey);

        $this->assertTrue($midtransService->verifySignature($orderNumber, $statusCode, $grossAmount, $validSignature));
        $this->assertFalse($midtransService->verifySignature($orderNumber, $statusCode, $grossAmount, 'invalid-signature-hash'));
        $this->assertFalse($midtransService->verifySignature('WRONG-ORDER-ID', $statusCode, $grossAmount, $validSignature));
    }

    public function test_webhook_csrf_is_exempted(): void
    {
        $orderNumber = $this->order->order_number;
        $statusCode = '200';
        $grossAmount = '20050000.00';
        $signature = hash('sha512', $orderNumber . $statusCode . $grossAmount . $this->testServerKey);

        // POST without CSRF token must not throw 419 Page Expired
        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-trx-12345',
            'payment_type' => 'bank_transfer',
        ]);

        $this->assertNotEquals(419, $response->getStatusCode());
        $response->assertStatus(200);
    }

    public function test_webhook_settlement_updates_order_to_processing_and_payment_to_settlement(): void
    {
        $orderNumber = $this->order->order_number;
        $statusCode = '200';
        $grossAmount = '20050000.00';
        $signature = hash('sha512', $orderNumber . $statusCode . $grossAmount . $this->testServerKey);

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-trx-settlement-01',
            'payment_type' => 'qris',
            'transaction_time' => '2026-10-28 14:00:00',
            'settlement_time' => '2026-10-28 14:05:00',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'order_status' => 'processing',
                'payment_status' => 'settlement',
            ],
        ]);

        // Assert order updated to processing
        $this->assertEquals(OrderStatus::PROCESSING, $this->order->fresh()->status);

        // Assert payment record saved
        $payment = DB::table('payments')->where('order_id', $this->order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('settlement', $payment->transaction_status);
        $this->assertEquals('qris', $payment->payment_type);
        $this->assertEquals(20050000.00, (float) $payment->gross_amount);
        $this->assertNotNull($payment->settlement_time);
    }

    public function test_webhook_pending_leaves_order_pending_and_payment_pending(): void
    {
        $orderNumber = $this->order->order_number;
        $statusCode = '201';
        $grossAmount = '20050000.00';
        $signature = hash('sha512', $orderNumber . $statusCode . $grossAmount . $this->testServerKey);

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'pending',
            'transaction_id' => 'midtrans-trx-pending-01',
            'payment_type' => 'echannel',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(OrderStatus::PENDING, $this->order->fresh()->status);

        $payment = DB::table('payments')->where('order_id', $this->order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pending', $payment->transaction_status);
    }

    public function test_webhook_expire_or_cancel_cancels_order_and_restores_product_stock_idempotently(): void
    {
        // Initial stock setup: 5 units.
        // Simulate order placement: product stock decremented from 5 to 4.
        $this->product->update(['stock' => 4]);

        $orderNumber = $this->order->order_number;
        $statusCode = '202';
        $grossAmount = '20050000.00';
        $signature = hash('sha512', $orderNumber . $statusCode . $grossAmount . $this->testServerKey);

        // 1. First webhook notification: order expires
        $response1 = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'transaction_id' => 'midtrans-trx-expire-01',
        ]);

        $response1->assertStatus(200);
        $this->assertEquals(OrderStatus::CANCELLED, $this->order->fresh()->status);

        // Stock must be restored from 4 to 5 (+1)
        $this->assertEquals(5, $this->product->fresh()->stock);

        // 2. Second duplicate webhook notification (Midtrans retry)
        $response2 = $this->postJson(route('midtrans.notification'), [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'transaction_id' => 'midtrans-trx-expire-01',
        ]);

        $response2->assertStatus(200);

        // Stock must STILL be 5 (idempotent, NOT 6!)
        $this->assertEquals(5, $this->product->fresh()->stock);
    }

    public function test_webhook_rejects_invalid_signature_with_http_400(): void
    {
        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => $this->order->order_number,
            'status_code' => '200',
            'gross_amount' => '20050000.00',
            'signature_key' => 'corrupted_signature_hash',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['status' => 'error']);

        // Order remains unchanged
        $this->assertEquals(OrderStatus::PENDING, $this->order->fresh()->status);
    }

    public function test_checkout_success_page_renders_snap_token_and_client_key(): void
    {
        $response = $this->actingAs($this->customer)->get(route('checkout.success', $this->order->order_number));

        $response->assertStatus(200);
        $response->assertSeeText($this->order->order_number);
        $response->assertSeeText('Bayar Sekarang (Midtrans Snap)');
        $response->assertSee($this->testClientKey);
        $response->assertSee('snap.js');
    }

    public function test_payment_callbacks_redirect_with_appropriate_flash_messages(): void
    {
        // Finish callback
        $finishResp = $this->get(route('payment.finish', ['order_id' => $this->order->order_number]));
        $finishResp->assertRedirect(route('checkout.success', $this->order->order_number));
        $finishResp->assertSessionHas('info');

        // Unfinish callback
        $unfinishResp = $this->get(route('payment.unfinish', ['order_id' => $this->order->order_number]));
        $unfinishResp->assertRedirect(route('checkout.success', $this->order->order_number));
        $unfinishResp->assertSessionHas('warning');

        // Error callback
        $errorResp = $this->get(route('payment.error', ['order_id' => $this->order->order_number]));
        $errorResp->assertRedirect(route('checkout.success', $this->order->order_number));
        $errorResp->assertSessionHas('error');
    }
}
