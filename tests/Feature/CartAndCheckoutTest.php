<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\User;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Product $productInStock;
    protected Product $productLowStock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->customer = User::where('role', UserRole::CUSTOMER)->first();

        $category = Category::first();
        $brand = Brand::first();

        // Setup test products
        $this->productInStock = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Laptop ASUS ZenBook Test',
            'slug' => 'laptop-asus-zenbook-test',
            'sku' => 'LAP-ASUS-TEST-001',
            'condition' => 'new',
            'price' => 15000000,
            'stock' => 10,
            'weight_grams' => 1400,
            'description' => 'Laptop premium pengujian',
            'status' => 'published',
        ]);

        ProductSpecification::create([
            'product_id' => $this->productInStock->id,
            'processor' => 'Intel Core i7-13700H',
            'ram_gb' => 16,
            'ram_type' => 'LPDDR5',
            'storage_capacity' => '1TB SSD',
            'storage_type' => 'NVMe PCIe Gen 4',
            'gpu' => 'Intel Iris Xe',
            'screen_size' => 14.0,
            'screen_resolution' => '2.8K OLED',
            'warranty' => '2 Tahun Resmi ASUS',
        ]);

        $this->productLowStock = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'ThinkPad T480s Bekas Mulus',
            'slug' => 'thinkpad-t480s-bekas-mulus',
            'sku' => 'LAP-LENOVO-TEST-002',
            'condition' => 'used',
            'price' => 5500000,
            'stock' => 1,
            'weight_grams' => 1350,
            'description' => 'Unit bekas mulus siap pakai',
            'status' => 'published',
        ]);

        ProductSpecification::create([
            'product_id' => $this->productLowStock->id,
            'processor' => 'Intel Core i5-8350U',
            'ram_gb' => 16,
            'ram_type' => 'DDR4',
            'storage_capacity' => '512GB SSD',
            'gpu' => 'Intel UHD 620',
            'screen_size' => 14.0,
            'physical_condition' => 'Grade A Mulus 95%',
            'battery_condition' => 'Normal Health 85%',
            'warranty' => '1 Bulan Toko',
        ]);
    }

    public function test_guest_can_add_item_to_cart_with_session(): void
    {
        $response = $this->post(route('cart.store'), [
            'product_id' => $this->productInStock->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->productInStock->id,
            'quantity' => 2,
        ]);
    }

    public function test_buy_now_adds_to_cart_and_redirects_to_checkout(): void
    {
        $response = $this->actingAs($this->customer)->post(route('cart.store'), [
            'product_id' => $this->productInStock->id,
            'quantity' => 1,
            'buy_now' => 1,
        ]);

        $response->assertRedirect(route('checkout.index'));
    }

    public function test_cart_validates_stock_limit_on_add(): void
    {
        // Product low stock only has 1 unit available
        $response = $this->post(route('cart.store'), [
            'product_id' => $this->productLowStock->id,
            'quantity' => 5,
        ]);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_cart_item_can_be_updated_and_removed(): void
    {
        $cartService = app(CartService::class);
        $result = $cartService->addItem($this->customer->id, null, $this->productInStock->id, 1);

        $itemId = $result['cart_item_id'];

        // Update quantity to 3
        $updateResp = $this->actingAs($this->customer)->patch(route('cart.update', $itemId), [
            'quantity' => 3,
        ]);
        $updateResp->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('cart_items', [
            'id' => $itemId,
            'quantity' => 3,
        ]);

        // Remove item
        $deleteResp = $this->actingAs($this->customer)->delete(route('cart.destroy', $itemId));
        $deleteResp->assertRedirect(route('cart.index'));

        $this->assertDatabaseMissing('cart_items', [
            'id' => $itemId,
        ]);
    }

    public function test_cart_can_be_cleared(): void
    {
        $cartService = app(CartService::class);
        $cartService->addItem($this->customer->id, null, $this->productInStock->id, 1);
        $cartService->addItem($this->customer->id, null, $this->productLowStock->id, 1);

        $response = $this->actingAs($this->customer)->delete(route('cart.clear'));
        $response->assertRedirect(route('cart.index'));

        $cart = $cartService->getCartDetails($this->customer->id, null);
        $this->assertCount(0, $cart->items);
    }

    public function test_cart_page_renders_items_and_realtime_stock_flags(): void
    {
        $cartService = app(CartService::class);
        $cartService->addItem($this->customer->id, null, $this->productInStock->id, 1);

        $response = $this->actingAs($this->customer)->get(route('cart.index'));
        $response->assertStatus(200);
        $response->assertSeeText($this->productInStock->name);
        $response->assertSeeText('Ringkasan Pesanan');
        $response->assertSeeText('Lanjut ke Pembayaran');
    }

    public function test_guest_cart_is_merged_upon_user_login(): void
    {
        $cartService = app(CartService::class);
        $guestSessionId = 'guest-session-uuid-12345';

        // Guest adds laptop to cart
        $cartService->addItem(null, $guestSessionId, $this->productInStock->id, 2);

        // Guest logs in with session
        $this->withSession(['_token' => 'dummy_token']);

        // Merge cart into user
        $cartService->mergeSessionCartToUser($this->customer->id, $guestSessionId);

        $userCart = $cartService->getCartDetails($this->customer->id, null);
        $this->assertGreaterThanOrEqual(1, $userCart->items->count());
        $this->assertEquals(2, $userCart->items->where('product_id', $this->productInStock->id)->first()->quantity);
    }

    public function test_customer_can_manage_shipping_addresses(): void
    {
        // 1. Add new address
        $response = $this->actingAs($this->customer)->post(route('addresses.store'), [
            'label' => 'Kantor Baru',
            'recipient_name' => 'Budi Santoso',
            'phone' => '081299887766',
            'address_line' => 'Gedung Cyber 2 Lantai 15, Jl. HR Rasuna Said',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12950',
            'is_default' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->customer->id,
            'label' => 'Kantor Baru',
            'is_default' => 1,
        ]);

        $newAddress = Address::where('user_id', $this->customer->id)->where('label', 'Kantor Baru')->first();

        // 2. Update address
        $this->actingAs($this->customer)->put(route('addresses.update', $newAddress->id), [
            'label' => 'Kantor Pusat',
            'recipient_name' => 'Budi Santoso',
            'phone' => '081299887766',
            'address_line' => 'Gedung Cyber 2 Lantai 16, Jl. HR Rasuna Said',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12950',
            'is_default' => 1,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $newAddress->id,
            'label' => 'Kantor Pusat',
        ]);

        // 3. Delete address
        $this->actingAs($this->customer)->delete(route('addresses.destroy', $newAddress->id));
        $this->assertDatabaseMissing('addresses', [
            'id' => $newAddress->id,
        ]);
    }

    public function test_checkout_requires_authentication(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_checkout_fails_if_cart_is_empty(): void
    {
        $response = $this->actingAs($this->customer)->get(route('checkout.index'));
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_creates_order_locks_stock_and_decrements_atomically(): void
    {
        $cartService = app(CartService::class);
        $orderService = app(OrderService::class);

        $initialStock = $this->productInStock->stock;

        // Add 2 items to customer cart
        $cartService->addItem($this->customer->id, null, $this->productInStock->id, 2);

        $address = Address::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($address);

        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'address_id' => $address->id,
            'courier' => 'jne_REG',
            'customer_notes' => 'Harap kirim dengan kemasan kayu.',
        ]);

        // Assert redirected to checkout.success with DL- order number
        $response->assertRedirect();
        $this->assertTrue(session()->has('success'));

        // Verify stock decremented atomically
        $this->assertEquals($initialStock - 2, $this->productInStock->fresh()->stock);

        // Verify order created in database
        $order = DB::table('orders')->where('user_id', $this->customer->id)->orderByDesc('id')->first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('DL-', $order->order_number);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(30000000, (float) $order->subtotal);
        $this->assertGreaterThan(0, (float) $order->shipping_cost);

        // Verify order_items has snapshot_specs
        $orderItem = DB::table('order_items')->where('order_id', $order->id)->first();
        $this->assertNotNull($orderItem);
        $this->assertEquals($this->productInStock->sku, $orderItem->product_sku);
        $this->assertEquals(2, $orderItem->quantity);

        $specs = json_decode($orderItem->snapshot_specs, true);
        $this->assertIsArray($specs);
        $this->assertEquals('Intel Core i7-13700H', $specs['processor']);
        $this->assertEquals(16, $specs['ram_gb']);
        $this->assertEquals('new', $specs['condition']);

        // Verify shipment created
        $shipment = DB::table('shipments')->where('order_id', $order->id)->first();
        $this->assertNotNull($shipment);
        $this->assertEquals('jne', $shipment->courier_code);
        $this->assertEquals('REG', $shipment->courier_service);
        $this->assertEquals('pending', $shipment->status);
        $this->assertStringContainsString($address->recipient_name, $shipment->destination_address);

        // Verify cart is cleared
        $cartCount = $cartService->getItemCount($this->customer->id, null);
        $this->assertEquals(0, $cartCount);

        // Verify success page renders
        $successResp = $this->actingAs($this->customer)->get(route('checkout.success', $order->order_number));
        $successResp->assertStatus(200);
        $successResp->assertSeeText($order->order_number);
        $successResp->assertSeeText('Pesanan Berhasil Dibuat!');
    }

    public function test_checkout_rolls_back_if_product_becomes_out_of_stock(): void
    {
        $cartService = app(CartService::class);
        $orderService = app(OrderService::class);

        // Add the single used laptop to cart
        $cartService->addItem($this->customer->id, null, $this->productLowStock->id, 1);

        // Simulate another user purchasing and reducing stock to 0 right before checkout
        DB::table('products')->where('id', $this->productLowStock->id)->update(['stock' => 0]);

        $address = Address::where('user_id', $this->customer->id)->first();

        // Attempt checkout
        $response = $this->actingAs($this->customer)->post(route('checkout.process'), [
            'address_id' => $address->id,
            'courier' => 'sicepat_REG',
        ]);

        // Must fail with validation error
        $response->assertSessionHasErrors(['stock']);

        // No order should be created for this user
        $newOrders = DB::table('orders')->where('user_id', $this->customer->id)->count();
        $this->assertEquals(0, $newOrders);

        // Product stock remains 0 (not negative!)
        $this->assertEquals(0, $this->productLowStock->fresh()->stock);
    }
}

