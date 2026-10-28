<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $customerA;
    protected User $customerB;
    protected Product $product;
    protected Order $orderPending;
    protected Order $orderProcessing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        Config::set('services.midtrans.server_key', 'SB-Mid-server-test');
        Config::set('services.midtrans.client_key', 'SB-Mid-client-test');
        Config::set('services.midtrans.is_production', false);

        $this->customerA = User::where('role', UserRole::CUSTOMER)->first();

        // Create second customer for isolation test
        $this->customerB = User::create([
            'name' => 'Customer Lain',
            'email' => 'customer.lain@dunialaptop.com',
            'password' => bcrypt('password'),
            'role' => UserRole::CUSTOMER,
        ]);

        $category = Category::first();
        $brand = Brand::first();

        $this->product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'ASUS ROG Zephyrus G16',
            'slug' => 'asus-rog-zephyrus-g16',
            'sku' => 'LAP-ROG-G16-TEST',
            'condition' => 'new',
            'price' => 32000000,
            'stock' => 5,
            'weight_grams' => 2000,
            'description' => 'Laptop gaming ultra-slim',
            'status' => 'published',
        ]);

        ProductSpecification::create([
            'product_id' => $this->product->id,
            'processor' => 'Intel Core Ultra 9 185H',
            'ram_gb' => 32,
            'storage_capacity' => '2TB SSD',
            'gpu' => 'NVIDIA RTX 4080 12GB',
            'screen_size' => 16.0,
            'warranty' => '2 Tahun Resmi',
        ]);

        $address = Address::where('user_id', $this->customerA->id)->first();

        // Order 1: Pending (Customer A)
        $this->orderPending = Order::create([
            'order_number' => 'DL-20261028-PENDING',
            'user_id' => $this->customerA->id,
            'subtotal' => 32000000,
            'shipping_cost' => 50000,
            'discount_amount' => 0,
            'total_amount' => 32050000,
            'status' => OrderStatus::PENDING,
        ]);

        OrderItem::create([
            'order_id' => $this->orderPending->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'unit_price' => 32000000,
            'quantity' => 1,
            'subtotal' => 32000000,
            'snapshot_specs' => [
                'processor' => 'Intel Core Ultra 9 185H',
                'ram_gb' => 32,
                'warranty' => '2 Tahun Resmi',
            ],
        ]);

        Shipment::create([
            'order_id' => $this->orderPending->id,
            'courier_code' => 'jne',
            'courier_service' => 'REG',
            'shipping_cost' => 50000,
            'status' => 'pending',
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone,
            'destination_address' => $address->address_line . ', ' . $address->city,
        ]);

        // Order 2: Processing with Tracking Number (Customer A)
        $this->orderProcessing = Order::create([
            'order_number' => 'DL-20261028-PROCESSED',
            'user_id' => $this->customerA->id,
            'subtotal' => 32000000,
            'shipping_cost' => 50000,
            'discount_amount' => 0,
            'total_amount' => 32050000,
            'status' => OrderStatus::PROCESSING,
        ]);

        OrderItem::create([
            'order_id' => $this->orderProcessing->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'unit_price' => 32000000,
            'quantity' => 1,
            'subtotal' => 32000000,
            'snapshot_specs' => ['processor' => 'Intel Core Ultra 9 185H'],
        ]);

        Shipment::create([
            'order_id' => $this->orderProcessing->id,
            'courier_code' => 'sicepat',
            'courier_service' => 'BEST',
            'tracking_number' => 'SCPT1234567890',
            'shipping_cost' => 50000,
            'status' => 'picked_up',
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->phone,
            'destination_address' => $address->address_line . ', ' . $address->city,
        ]);

        Payment::create([
            'order_id' => $this->orderProcessing->id,
            'transaction_reference' => 'MID-SETTLED-001',
            'payment_gateway' => 'midtrans',
            'payment_type' => 'qris',
            'gross_amount' => 32050000,
            'transaction_status' => 'settlement',
            'settlement_time' => now(),
        ]);
    }

    public function test_guest_cannot_access_orders_and_is_redirected_to_login(): void
    {
        $response = $this->get(route('orders.index'));
        $response->assertRedirect(route('login'));

        $detailResponse = $this->get(route('orders.show', $this->orderPending->order_number));
        $detailResponse->assertRedirect(route('login'));
    }

    public function test_customer_can_view_order_history_list(): void
    {
        $response = $this->actingAs($this->customerA)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSeeText($this->orderPending->order_number);
        $response->assertSeeText($this->orderProcessing->order_number);
        $response->assertSeeText('Riwayat Pesanan Saya');
        $response->assertSeeText('ASUS ROG Zephyrus G16');
    }

    public function test_customer_can_filter_orders_by_status(): void
    {
        // Filter: Pending only
        $pendingResp = $this->actingAs($this->customerA)->get(route('orders.index', ['status' => 'pending']));
        $pendingResp->assertStatus(200);
        $pendingResp->assertSeeText($this->orderPending->order_number);
        $pendingResp->assertDontSeeText($this->orderProcessing->order_number);

        // Filter: Processing only
        $procResp = $this->actingAs($this->customerA)->get(route('orders.index', ['status' => 'processing']));
        $procResp->assertStatus(200);
        $procResp->assertSeeText($this->orderProcessing->order_number);
        $procResp->assertDontSeeText($this->orderPending->order_number);
    }

    public function test_customer_can_view_order_details_with_specs_and_shipment_resi(): void
    {
        $response = $this->actingAs($this->customerA)->get(route('orders.show', $this->orderProcessing->order_number));

        $response->assertStatus(200);
        $response->assertSeeText($this->orderProcessing->order_number);
        $response->assertSeeText('SCPT1234567890'); // Nomor resi
        $response->assertSeeText('Intel Core Ultra 9 185H'); // Snapshot specs
        $response->assertSeeText('qris'); // Payment type
        $response->assertSeeText('Diproses Teknisi');
    }

    public function test_customer_cannot_view_or_cancel_another_customers_order(): void
    {
        // Customer B tries to view Customer A's order -> 404
        $viewResponse = $this->actingAs($this->customerB)->get(route('orders.show', $this->orderPending->order_number));
        $viewResponse->assertStatus(404);

        // Customer B tries to cancel Customer A's order -> fails
        $cancelResponse = $this->actingAs($this->customerB)->post(route('orders.cancel', $this->orderPending->order_number));
        $cancelResponse->assertSessionHasErrors(['order']);

        // Order remains pending
        $this->assertEquals(OrderStatus::PENDING, $this->orderPending->fresh()->status);
    }

    public function test_customer_can_cancel_pending_order_and_stock_is_restored(): void
    {
        $initialStock = $this->product->stock; // 5

        $response = $this->actingAs($this->customerA)->post(route('orders.cancel', $this->orderPending->order_number), [
            'reason' => 'Ingin mengganti metode pembayaran',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Order status is now cancelled
        $this->assertEquals(OrderStatus::CANCELLED, $this->orderPending->fresh()->status);

        // Product stock is restored (+1 unit from orderPending)
        $this->assertEquals($initialStock + 1, $this->product->fresh()->stock);
    }

    public function test_customer_cannot_cancel_already_processed_order(): void
    {
        $response = $this->actingAs($this->customerA)->post(route('orders.cancel', $this->orderProcessing->order_number));

        $response->assertSessionHasErrors(['order']);

        // Order status remains processing
        $this->assertEquals(OrderStatus::PROCESSING, $this->orderProcessing->fresh()->status);
    }

    public function test_pending_order_details_page_renders_snap_token_for_repayment(): void
    {
        $response = $this->actingAs($this->customerA)->get(route('orders.show', $this->orderPending->order_number));

        $response->assertStatus(200);
        $response->assertSeeText('Bayar Sekarang (Midtrans)');
        $response->assertSeeText('Batalkan Pesanan Ini');
        $response->assertSee('snap.js');
    }
}

