<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Enums\ShipmentStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ChatbotTemplate;
use App\Models\HomepageSection;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\Shipment;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_and_roles_are_seeded_correctly(): void
    {
        $superAdmin = User::where('email', 'superadmin@dunialaptop.com')->first();
        $this->assertNotNull($superAdmin);
        $this->assertEquals(UserRole::SUPER_ADMIN, $superAdmin->role);
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($superAdmin->isAdmin());

        $admin = User::where('email', 'admin@dunialaptop.com')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());

        $staff = User::where('email', 'staff@dunialaptop.com')->first();
        $this->assertNotNull($staff);
        $this->assertTrue($staff->isStaff());

        $customer = User::where('email', 'customer@dunialaptop.com')->first();
        $this->assertNotNull($customer);
        $this->assertTrue($customer->isCustomer());
        $this->assertCount(1, $customer->addresses);
    }

    public function test_category_and_brand_relationships(): void
    {
        $category = Category::where('slug', 'laptop-gaming')->first();
        $this->assertNotNull($category);
        $this->assertGreaterThan(0, $category->products()->count());

        $brand = Brand::where('slug', 'asus')->first();
        $this->assertNotNull($brand);
        $this->assertGreaterThan(0, $brand->products()->count());
    }

    public function test_product_specifications_hybrid_and_images(): void
    {
        $product = Product::where('sku', 'LAP-ASUS-ROG-G14')->first();
        $this->assertNotNull($product);

        // Relasi 1-to-1 ke product_specifications
        $spec = $product->specification;
        $this->assertNotNull($spec);
        $this->assertEquals(32, $spec->ram_gb);
        $this->assertEquals(14.0, (float) $spec->screen_size);
        $this->assertIsArray($spec->additional_specs);
        $this->assertArrayHasKey('wifi', $spec->additional_specs);

        // Relasi 1-to-N ke product_images
        $this->assertGreaterThan(0, $product->images()->count());
        $primaryImg = $product->primaryImage;
        $this->assertNotNull($primaryImg);
        $this->assertTrue($primaryImg->is_primary);
    }

    public function test_unique_constraints_on_sku_and_slug(): void
    {
        $this->expectException(QueryException::class);

        $category = Category::first();
        $brand = Brand::first();

        // Duplikasi SKU harus dilempar QueryException
        Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Duplicate SKU Product',
            'slug' => 'unique-slug-test-' . uniqid(),
            'sku' => 'LAP-ASUS-ROG-G14', // SKU yang sudah ada di seeder
            'condition' => ProductCondition::NEW,
            'price' => 10000000,
            'stock' => 5,
            'weight_grams' => 1500,
            'status' => ProductStatus::PUBLISHED,
        ]);
    }

    public function test_cart_and_cart_items_composite_unique(): void
    {
        $user = User::factory()->create();
        $cart = Cart::create(['user_id' => $user->id]);
        $product = Product::first();

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->expectException(QueryException::class);

        // Duplikasi item yang sama dalam satu keranjang harus gagal
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_order_creation_price_snapshot_and_stock_consistency(): void
    {
        $customer = User::where('role', UserRole::CUSTOMER)->first();
        $product = Product::where('sku', 'LAP-ACER-NITRO-V15')->first();

        $originalPrice = $product->price;
        $initialStock = $product->stock;

        // Buat Order
        $order = Order::create([
            'order_number' => 'ORD-TEST-' . time(),
            'user_id' => $customer->id,
            'subtotal' => $originalPrice * 2,
            'shipping_cost' => 50000,
            'discount_amount' => 0,
            'total_amount' => ($originalPrice * 2) + 50000,
            'status' => OrderStatus::PENDING,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => $originalPrice,
            'quantity' => 2,
            'subtotal' => $originalPrice * 2,
            'snapshot_specs' => [
                'processor' => $product->specification->processor,
                'ram_gb' => $product->specification->ram_gb,
            ],
        ]);

        // Simulasikan pemotongan stok
        $product->decrement('stock', 2);
        $this->assertEquals($initialStock - 2, $product->fresh()->stock);

        // Ubah harga katalog produk master
        $product->update(['price' => 99999999]);

        // Pastikan harga pada order_items tetap menggunakan snapshot awal (historis tidak berubah!)
        $this->assertEquals($originalPrice, $orderItem->fresh()->unit_price);
        $this->assertNotEquals($product->fresh()->price, $orderItem->fresh()->unit_price);

        // Pengujian shipment snapshot
        $shipment = Shipment::create([
            'order_id' => $order->id,
            'courier_code' => 'jne',
            'courier_service' => 'REG',
            'shipping_cost' => 50000,
            'status' => ShipmentStatus::PENDING,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081298765432',
            'destination_address' => 'Jl. Kaliurang KM 5 No. 12, Sleman',
        ]);
        $this->assertEquals(ShipmentStatus::PENDING, $shipment->status);

        // Pengujian pembayaran Midtrans & idempotensi
        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_reference' => 'MIDTRANS-' . $order->order_number,
            'payment_gateway' => 'midtrans',
            'payment_type' => 'bank_transfer',
            'gross_amount' => $order->total_amount,
            'transaction_status' => PaymentStatus::SETTLEMENT,
            'settlement_time' => now(),
            'raw_response' => ['status_code' => '200', 'transaction_status' => 'settlement'],
        ]);
        $this->assertEquals(PaymentStatus::SETTLEMENT, $payment->transaction_status);
        $this->assertEquals('200', $payment->raw_response['status_code']);

        // Pembersihan
        $order->delete();
    }

    public function test_site_settings_and_homepage_sections(): void
    {
        // SiteSetting helper
        SiteSetting::set('test_key', 'test_value', 'general', 'string');
        $this->assertEquals('test_value', SiteSetting::get('test_key'));

        SiteSetting::set('test_bool', true, 'general', 'boolean');
        $this->assertTrue(SiteSetting::get('test_bool'));

        // HomepageSection
        $hero = HomepageSection::where('section_type', 'hero_banner')->first();
        $this->assertNotNull($hero);
        $this->assertIsArray($hero->content_data);
        $this->assertArrayHasKey('banners', $hero->content_data);
    }

    public function test_chatbot_templates(): void
    {
        $tpl = ChatbotTemplate::where('category', 'warranty_policy')->first();
        $this->assertNotNull($tpl);
        $this->assertEquals('static', $tpl->response_type);
        $this->assertStringContainsString('garansi', $tpl->trigger_keywords);
    }
}

