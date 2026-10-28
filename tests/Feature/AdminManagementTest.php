<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $staff;
    protected User $customer;
    protected Category $category;
    protected Brand $brand;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->superAdmin = User::where('role', UserRole::SUPER_ADMIN)->first();
        $this->admin = User::where('role', UserRole::ADMIN)->first();
        $this->staff = User::where('role', UserRole::STAFF)->first();
        $this->customer = User::where('role', UserRole::CUSTOMER)->first();

        $this->category = Category::first();
        $this->brand = Brand::first();
        $this->product = Product::first();
    }

    /**
     * Helper to create a test order with item, shipment, and payment records.
     */
    protected function createTestOrder(User $customer, string $orderStatus = 'pending', float $amount = 12000000, int $qty = 1): Order
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-' . uniqid(),
            'user_id' => $customer->id,
            'subtotal' => $amount,
            'shipping_cost' => 50000,
            'discount_amount' => 0,
            'total_amount' => $amount + 50000,
            'status' => $orderStatus,
            'customer_notes' => 'Catatan tes admin',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'unit_price' => $amount / $qty,
            'quantity' => $qty,
            'subtotal' => $amount,
            'snapshot_specs' => [
                'processor' => 'AMD Ryzen 7 7735HS',
                'ram_gb' => 16,
                'storage_capacity' => '512GB',
                'gpu' => 'RTX 4050',
                'condition' => 'new',
            ],
        ]);

        Shipment::create([
            'order_id' => $order->id,
            'courier_code' => 'JNE',
            'courier_service' => 'REG',
            'tracking_number' => null,
            'shipping_cost' => 50000,
            'status' => 'pending',
            'recipient_name' => 'Budi Tester',
            'recipient_phone' => '081234567890',
            'destination_address' => 'Jl. Jendral Sudirman No. 12, Jakarta',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'transaction_reference' => 'MID-' . $order->order_number,
            'payment_gateway' => 'midtrans',
            'gross_amount' => $order->total_amount,
            'payment_type' => 'bank_transfer',
            'transaction_status' => $orderStatus === 'processing' || $orderStatus === 'completed' ? 'settlement' : 'pending',
            'settlement_time' => $orderStatus === 'processing' || $orderStatus === 'completed' ? now() : null,
        ]);

        return $order;
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_customer_gets_403_forbidden_on_all_admin_routes(): void
    {
        $this->actingAs($this->customer);

        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/products')->assertForbidden();
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/reports/sales')->assertForbidden();
    }

    public function test_staff_can_access_dashboard_and_products_but_forbidden_from_orders_and_reports(): void
    {
        $this->actingAs($this->staff);

        // Allowed routes for staff
        $this->get('/admin/dashboard')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/products/create')->assertOk();

        // Forbidden routes for staff (Backend RBAC)
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/reports/sales')->assertForbidden();
    }

    public function test_admin_and_super_admin_can_access_all_operational_modules(): void
    {
        // Admin test
        $this->actingAs($this->admin);
        $this->get('/admin/dashboard')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/orders')->assertOk();
        $this->get('/admin/reports/sales')->assertOk();

        // Super Admin test
        $this->actingAs($this->superAdmin);
        $this->get('/admin/dashboard')->assertOk();
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/orders')->assertOk();
        $this->get('/admin/reports/sales')->assertOk();
    }

    public function test_admin_dashboard_metrics_aggregation(): void
    {
        $this->actingAs($this->admin);

        // Create 1 processing order (valid omzet: 10,050,000) and 1 pending order
        $orderProcessing = $this->createTestOrder($this->customer, 'processing', 10000000, 1);
        $orderPending = $this->createTestOrder($this->customer, 'pending', 5000000, 1);

        // Make product low stock
        $this->product->update(['stock' => 1, 'status' => 'published']);

        $response = $this->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Total Omzet Valid');
        $response->assertSee(number_format($orderProcessing->total_amount, 0, ',', '.'));
        $response->assertSee($orderProcessing->order_number);
        $response->assertSee($orderPending->order_number);
    }

    public function test_order_management_lists_orders_and_filters_by_status(): void
    {
        $this->actingAs($this->admin);

        $orderProcessing = $this->createTestOrder($this->customer, 'processing', 10000000, 1);
        $orderPending = $this->createTestOrder($this->customer, 'pending', 5000000, 1);

        // All orders
        $responseAll = $this->get('/admin/orders');
        $responseAll->assertOk();
        $responseAll->assertSee($orderProcessing->order_number);
        $responseAll->assertSee($orderPending->order_number);

        // Filter processing only
        $responseProcessing = $this->get('/admin/orders?status=processing');
        $responseProcessing->assertOk();
        $responseProcessing->assertSee($orderProcessing->order_number);
        $responseProcessing->assertDontSee($orderPending->order_number);

        // Search by order number
        $responseSearch = $this->get('/admin/orders?search=' . $orderPending->order_number);
        $responseSearch->assertOk();
        $responseSearch->assertSee($orderPending->order_number);
        $responseSearch->assertDontSee($orderProcessing->order_number);
    }

    public function test_order_management_show_page_displays_order_details_and_snapshot(): void
    {
        $this->actingAs($this->admin);
        $order = $this->createTestOrder($this->customer, 'processing', 8000000, 1);

        $response = $this->get("/admin/orders/{$order->order_number}");

        $response->assertOk();
        $response->assertSee($order->order_number);
        $response->assertSee('Budi Tester');
        $response->assertSee('Jl. Jendral Sudirman');
        $response->assertSee('Catatan Khusus dari Pembeli');
        $response->assertSee('Catatan tes admin');
        $response->assertSee('Spesifikasi Snapshot');
    }

    public function test_order_status_update_and_stock_restoration_on_cancellation(): void
    {
        $this->actingAs($this->admin);

        $this->product->update(['stock' => 5]);
        $order = $this->createTestOrder($this->customer, 'processing', 8000000, 2);

        // 1. Update status to shipped
        $responseUpdate = $this->patch("/admin/orders/{$order->order_number}/status", [
            'status' => 'shipped',
        ]);
        $responseUpdate->assertRedirect("/admin/orders/{$order->order_number}");
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
        $this->assertEquals(5, $this->product->fresh()->stock);

        // 2. Cancel order by admin -> MUST atomically restore stock (5 + 2 = 7)
        $responseCancel = $this->patch("/admin/orders/{$order->order_number}/status", [
            'status' => 'cancelled',
        ]);
        $responseCancel->assertRedirect("/admin/orders/{$order->order_number}");
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
        $this->assertEquals(7, $this->product->fresh()->stock);
    }

    public function test_shipment_update_records_tracking_number_and_status(): void
    {
        $this->actingAs($this->admin);
        $order = $this->createTestOrder($this->customer, 'processing', 8000000, 1);

        $response = $this->patch("/admin/orders/{$order->order_number}/shipment", [
            'courier_code' => 'JNE',
            'courier_service' => 'REG',
            'tracking_number' => 'JNE123456789ID',
            'shipment_status' => 'in_transit',
        ]);

        $response->assertRedirect("/admin/orders/{$order->order_number}");

        $this->assertDatabaseHas('shipments', [
            'order_id' => $order->id,
            'tracking_number' => 'JNE123456789ID',
            'status' => 'in_transit',
        ]);

        // Order status automatically synchronizes to 'shipped'
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'shipped',
        ]);
    }

    public function test_sales_report_aggregates_revenue_and_top_products_within_date_range(): void
    {
        $this->actingAs($this->admin);

        $orderCompleted = $this->createTestOrder($this->customer, 'completed', 15000000, 2);
        $orderCompleted->update(['created_at' => now()->startOfMonth()]);

        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->toDateString();

        $response = $this->get("/admin/reports/sales?start_date={$startDate}&end_date={$endDate}&status=valid");

        $response->assertOk();
        $response->assertSee('Total Omzet Penjualan');
        $response->assertSee(number_format($orderCompleted->total_amount, 0, ',', '.'));
        $response->assertSee($orderCompleted->order_number);

        // Test Print View
        $responsePrint = $this->get("/admin/reports/sales/print?start_date={$startDate}&end_date={$endDate}&status=valid");
        $responsePrint->assertOk();
        $responsePrint->assertSee('Laporan Penjualan');
        $responsePrint->assertSee($orderCompleted->order_number);
    }

    public function test_product_crud_with_specifications_and_images(): void
    {
        Storage::fake('public');
        $this->actingAs($this->staff);

        $fakeImage = UploadedFile::fake()->create('laptop_rog.jpg', 100, 'image/jpeg');

        // 1. Create Product
        $createData = [
            'name' => 'Lenovo Legion Pro 5 16IRX8',
            'sku' => 'LAP-LENOVO-LEGION-001',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'condition' => 'new',
            'price' => 22500000,
            'stock' => 8,
            'weight_grams' => 2500,
            'description' => 'Laptop gaming bertenaga tinggi dengan layar WQXGA 240Hz.',
            'status' => 'published',
            // Specs
            'processor' => 'Intel Core i7-13700HX',
            'ram_gb' => 16,
            'ram_type' => 'DDR5 4800MHz',
            'storage_capacity' => '1TB',
            'storage_type' => 'NVMe PCIe 4.0 SSD',
            'gpu' => 'NVIDIA GeForce RTX 4060 8GB GDDR6',
            'screen_size' => 16.0,
            'screen_resolution' => 'WQXGA (2560x1600) 240Hz',
            'operating_system' => 'Windows 11 Home Original',
            'warranty' => 'Garansi Resmi Lenovo Indonesia 3 Tahun',
            'physical_condition' => null,
            'battery_condition' => null,
            'images' => [$fakeImage],
        ];

        $responseCreate = $this->post('/admin/products', $createData);
        $responseCreate->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', [
            'sku' => 'LAP-LENOVO-LEGION-001',
            'name' => 'Lenovo Legion Pro 5 16IRX8',
            'price' => 22500000,
            'stock' => 8,
        ]);

        $createdProduct = Product::where('sku', 'LAP-LENOVO-LEGION-001')->first();
        $this->assertNotNull($createdProduct);

        $this->assertDatabaseHas('product_specifications', [
            'product_id' => $createdProduct->id,
            'processor' => 'Intel Core i7-13700HX',
            'ram_gb' => 16,
            'storage_capacity' => '1TB',
        ]);

        $this->assertDatabaseHas('product_images', [
            'product_id' => $createdProduct->id,
            'is_primary' => true,
        ]);

        // 2. Edit Page
        $responseEdit = $this->get("/admin/products/{$createdProduct->id}/edit");
        $responseEdit->assertOk();
        $responseEdit->assertSee('Lenovo Legion Pro 5');

        // 3. Update Product
        $updateData = $createData;
        $updateData['price'] = 21999000;
        $updateData['processor'] = 'Intel Core i7-13700HX Extreme';
        unset($updateData['images']);

        $responseUpdate = $this->put("/admin/products/{$createdProduct->id}", $updateData);
        $responseUpdate->assertRedirect('/admin/products');

        $this->assertDatabaseHas('products', [
            'id' => $createdProduct->id,
            'price' => 21999000,
        ]);

        $this->assertDatabaseHas('product_specifications', [
            'product_id' => $createdProduct->id,
            'processor' => 'Intel Core i7-13700HX Extreme',
        ]);

        // 4. Quick Update (Stock & Price inline)
        $responseQuick = $this->patch("/admin/products/{$createdProduct->id}/quick-update", [
            'price' => 20500000,
            'stock' => 12,
        ]);
        $responseQuick->assertRedirect();
        $this->assertDatabaseHas('products', [
            'id' => $createdProduct->id,
            'price' => 20500000,
            'stock' => 12,
        ]);

        // 5. Soft Delete
        $responseDelete = $this->delete("/admin/products/{$createdProduct->id}");
        $responseDelete->assertRedirect('/admin/products');

        $this->assertSoftDeleted('products', [
            'id' => $createdProduct->id,
        ]);
    }
}
