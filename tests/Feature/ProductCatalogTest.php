<?php

namespace Tests\Feature;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_renders_active_sections_in_sort_order_and_applies_site_settings(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('PT Dunia Laptop Indonesia');
        $response->assertSee('081234567890');
        $response->assertSee('--color-store-primary: 37 99 235', false);

        // Default seeded order: hero_banner (1) -> category_showcase (2) -> featured_products (3) -> promo_grid (4) -> latest_products (5) -> faq (6)
        $response->assertSeeInOrder([
            'New ROG Zephyrus 2024 Series',
            'Pilih Laptop Sesuai Kebutuhan Anda',
            'Laptop Unggulan Pilihan Editor',
            'Laptop Bekas Mulus Bergaransi',
            'Koleksi Laptop Terbaru',
            'Pertanyaan yang Sering Diajukan (FAQ)',
        ]);

        // Deactivate FAQ and move promo_grid above category_showcase
        DB::table('homepage_sections')
            ->where('section_type', 'faq')
            ->update(['is_active' => false]);

        DB::table('homepage_sections')
            ->where('section_type', 'promo_grid')
            ->update(['sort_order' => 1]);

        DB::table('homepage_sections')
            ->where('section_type', 'hero_banner')
            ->update(['sort_order' => 2]);

        // Inject invalid theme color to verify FR-41 sanitization fallback
        DB::table('site_settings')
            ->where('key', 'theme_primary_color')
            ->update(['value' => 'expression(alert(1))']);

        $reorderedResponse = $this->get(route('home'));
        $reorderedResponse->assertOk();
        $reorderedResponse->assertDontSee('Pertanyaan yang Sering Diajukan (FAQ)');
        $reorderedResponse->assertSeeInOrder([
            'Laptop Bekas Mulus Bergaransi',
            'New ROG Zephyrus 2024 Series',
            'Pilih Laptop Sesuai Kebutuhan Anda',
        ]);
        $reorderedResponse->assertSee('--color-store-primary: 37 99 235', false);
    }

    public function test_catalog_displays_only_published_non_deleted_products(): void
    {
        $category = Category::where('slug', 'laptop-gaming')->firstOrFail();
        $brand = Brand::where('slug', 'asus')->firstOrFail();

        $draftProduct = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Secret Draft Laptop XYZ',
            'status' => ProductStatus::DRAFT,
        ]);

        $archivedProduct = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Archived Old Laptop ABC',
            'status' => ProductStatus::ARCHIVED,
        ]);

        $deletedProduct = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Soft Deleted Laptop DEL',
            'status' => ProductStatus::PUBLISHED,
        ]);
        $deletedProduct->delete();

        $response = $this->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('ASUS ROG Zephyrus G14 GA402XY');
        $response->assertDontSee($draftProduct->name);
        $response->assertDontSee($archivedProduct->name);
        $response->assertDontSee($deletedProduct->name);
    }

    public function test_catalog_search_matches_name_sku_and_description(): void
    {
        // Search by Name
        $byName = $this->get(route('catalog.index', ['search' => 'Zephyrus']));
        $byName->assertOk();
        $byName->assertSee('ASUS ROG Zephyrus G14 GA402XY');
        $byName->assertDontSee('Lenovo ThinkPad X1 Carbon Gen 11');

        // Search by SKU
        $bySku = $this->get(route('catalog.index', ['search' => 'LAP-LEN-X1C-G11']));
        $bySku->assertOk();
        $bySku->assertSee('Lenovo ThinkPad X1 Carbon Gen 11');
        $bySku->assertDontSee('ASUS ROG Zephyrus G14 GA402XY');

        // Search by Description keyword
        $byDesc = $this->get(route('catalog.index', ['search' => 'Liquid Retina']));
        $byDesc->assertOk();
        $byDesc->assertSee('Apple MacBook Air M2 13.6 Inci 2023');
        $byDesc->assertDontSee('Acer Nitro V 15 ANV15-51-5115');
    }

    public function test_catalog_filters_by_condition_new_and_used(): void
    {
        $newResponse = $this->get(route('catalog.index', ['condition' => 'new']));
        $newResponse->assertOk();
        $newResponse->assertSee('ASUS ROG Zephyrus G14 GA402XY');
        $newResponse->assertSee('Acer Nitro V 15 ANV15-51-5115');
        $newResponse->assertDontSee('HP Pavilion Aero 13-be0001AU (Bekas Mulus)');
        $newResponse->assertDontSee('Dell XPS 13 Plus 9320 (Bekas Like New)');

        $usedResponse = $this->get(route('catalog.index', ['condition' => 'used']));
        $usedResponse->assertOk();
        $usedResponse->assertSee('HP Pavilion Aero 13-be0001AU (Bekas Mulus)');
        $usedResponse->assertSee('Dell XPS 13 Plus 9320 (Bekas Like New)');
        $usedResponse->assertDontSee('ASUS ROG Zephyrus G14 GA402XY');
    }

    public function test_catalog_filters_by_category_brand_price_range_and_stock_availability(): void
    {
        // Category route (/category/{slug})
        $catRoute = $this->get(route('catalog.category', 'laptop-gaming'));
        $catRoute->assertOk();
        $catRoute->assertSee('ASUS ROG Zephyrus G14 GA402XY');
        $catRoute->assertSee('Acer Nitro V 15 ANV15-51-5115');
        $catRoute->assertDontSee('Apple MacBook Air M2 13.6 Inci 2023');

        // Combined Category + Brand filter
        $catBrand = $this->get(route('catalog.index', [
            'category' => 'laptop-gaming',
            'brand' => 'acer',
        ]));
        $catBrand->assertOk();
        $catBrand->assertSee('Acer Nitro V 15 ANV15-51-5115');
        $catBrand->assertDontSee('ASUS ROG Zephyrus G14 GA402XY');

        // Price range filter: 15,000,000 to 26,000,000
        $priceRange = $this->get(route('catalog.index', [
            'min_price' => 15000000,
            'max_price' => 26000000,
        ]));
        $priceRange->assertOk();
        $priceRange->assertSee('Apple MacBook Air M2 13.6 Inci 2023'); // 15,999,000
        $priceRange->assertSee('Dell XPS 13 Plus 9320 (Bekas Like New)'); // 16,800,000
        $priceRange->assertSee('Lenovo ThinkPad X1 Carbon Gen 11'); // 25,800,000
        $priceRange->assertDontSee('HP Pavilion Aero 13-be0001AU (Bekas Mulus)'); // 7,850,000
        $priceRange->assertDontSee('ASUS ROG Zephyrus G14 GA402XY'); // 31,999,000

        // Out-of-stock visibility vs in_stock filter (FR-11)
        $outOfStockProduct = Product::factory()->create([
            'category_id' => Category::where('slug', 'ultrabook')->firstOrFail()->id,
            'brand_id' => Brand::where('slug', 'asus')->firstOrFail()->id,
            'name' => 'ASUS Zenbook Habis Stok Test',
            'condition' => ProductCondition::NEW,
            'price' => 14000000,
            'stock' => 0,
            'status' => ProductStatus::PUBLISHED,
        ]);

        $allPublished = $this->get(route('catalog.index'));
        $allPublished->assertOk();
        $allPublished->assertSee($outOfStockProduct->name);
        $allPublished->assertSee('Stok Habis');

        $onlyInStock = $this->get(route('catalog.index', ['in_stock' => 1]));
        $onlyInStock->assertOk();
        $onlyInStock->assertDontSee($outOfStockProduct->name);
    }

    public function test_catalog_sorting_by_lowest_and_highest_price(): void
    {
        $cheapestFirst = $this->get(route('catalog.index', ['sort' => 'price_asc']));
        $cheapestFirst->assertOk();
        $cheapestFirst->assertSeeInOrder([
            'HP Pavilion Aero 13-be0001AU (Bekas Mulus)', // 7,850,000
            'Acer Nitro V 15 ANV15-51-5115',              // 11,499,000
            'Apple MacBook Air M2 13.6 Inci 2023',        // 15,999,000
            'Dell XPS 13 Plus 9320 (Bekas Like New)',     // 16,800,000
            'Lenovo ThinkPad X1 Carbon Gen 11',           // 25,800,000
            'ASUS ROG Zephyrus G14 GA402XY',              // 31,999,000
        ]);

        $expensiveFirst = $this->get(route('catalog.index', ['sort' => 'price_desc']));
        $expensiveFirst->assertOk();
        $expensiveFirst->assertSeeInOrder([
            'ASUS ROG Zephyrus G14 GA402XY',
            'Lenovo ThinkPad X1 Carbon Gen 11',
            'Dell XPS 13 Plus 9320 (Bekas Like New)',
            'Apple MacBook Air M2 13.6 Inci 2023',
            'Acer Nitro V 15 ANV15-51-5115',
            'HP Pavilion Aero 13-be0001AU (Bekas Mulus)',
        ]);
    }

    public function test_product_detail_page_renders_specs_used_condition_gallery_and_protects_unpublished(): void
    {
        // 1. Used product detail verification
        $usedLaptop = DB::table('products')->where('sku', 'LAP-HP-AERO-USED')->first();
        $this->assertNotNull($usedLaptop);

        $usedDetail = $this->get(route('catalog.show', $usedLaptop->slug));
        $usedDetail->assertOk();
        $usedDetail->assertSee('Unit Bekas Berkualitas');
        $usedDetail->assertSee('Rincian Inspeksi Kondisi Laptop Bekas');
        $usedDetail->assertSee('Mulus 97%, tidak ada penyok/dent');
        $usedDetail->assertSee('Battery Health 91%');
        $usedDetail->assertSee('AMD Ryzen 5 5600U');
        $usedDetail->assertSee('Garansi Toko Dunia Laptop 1 Bulan');
        $usedDetail->assertSee('Audio by Bang & Olufsen Dual Speakers');
        $usedDetail->assertSee('Tambah ke Keranjang');
        $usedDetail->assertSee('Beli Sekarang');

        // 2. Multi-angle New product detail verification
        $rogLaptop = DB::table('products')->where('sku', 'LAP-ASUS-ROG-G14')->first();
        $this->assertNotNull($rogLaptop);

        $rogDetail = $this->get(route('catalog.show', $rogLaptop->slug));
        $rogDetail->assertOk();
        $rogDetail->assertSee('100% Baru (BNIB)');
        $rogDetail->assertSee('NVIDIA GeForce RTX 4070');
        $rogDetail->assertSee('Sudut #2');
        $rogDetail->assertSee('Wi-Fi 6E');

        // 3. Out-of-stock disables purchase buttons
        DB::table('products')->where('id', $rogLaptop->id)->update(['stock' => 0]);
        $outOfStockDetail = $this->get(route('catalog.show', $rogLaptop->slug));
        $outOfStockDetail->assertOk();
        $outOfStockDetail->assertSee('Stok Habis');

        // 4. Unpublished (draft) product returns 404 (FR-10)
        DB::table('products')->where('id', $rogLaptop->id)->update(['status' => ProductStatus::DRAFT->value]);
        $this->get(route('catalog.show', $rogLaptop->slug))->assertNotFound();
    }
}

