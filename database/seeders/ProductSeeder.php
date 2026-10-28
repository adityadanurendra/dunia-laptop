<?php

namespace Database\Seeders;

use App\Enums\ProductCondition;
use App\Enums\ProductStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productsData = [
            [
                'category_slug' => 'laptop-gaming',
                'brand_slug' => 'asus',
                'name' => 'ASUS ROG Zephyrus G14 GA402XY',
                'sku' => 'LAP-ASUS-ROG-G14',
                'condition' => ProductCondition::NEW,
                'price' => 31999000,
                'stock' => 5,
                'weight_grams' => 1720,
                'description' => 'Laptop gaming ultraportabel 14 inci paling bertenaga di dunia dengan bodi magnesium kokoh dan layar Nebula Display ROG 165Hz.',
                'status' => ProductStatus::PUBLISHED,
                'specs' => [
                    'processor' => 'AMD Ryzen 9 7940HS (8 Core, 16 Thread up to 5.2 GHz)',
                    'ram_gb' => 32,
                    'ram_type' => 'DDR5 4800MHz Dual Channel',
                    'storage_capacity' => '1 TB',
                    'storage_type' => 'NVMe PCIe 4.0 SSD',
                    'gpu' => 'NVIDIA GeForce RTX 4070 8GB GDDR6 (TGP 125W)',
                    'screen_size' => 14.0,
                    'screen_resolution' => '2560 x 1600 (QHD+ 165Hz, 100% DCI-P3)',
                    'physical_condition' => '100% Baru Segel Resmi',
                    'battery_condition' => '100% Brand New 76WHrs',
                    'operating_system' => 'Windows 11 Home + OHS 2021 Original',
                    'warranty' => 'Garansi Resmi ASUS Indonesia 2 Tahun + 1 Tahun Perfect Warranty',
                    'additional_specs' => [
                        'wifi' => 'Wi-Fi 6E (802.11ax) + Bluetooth 5.3',
                        'ports' => '1x Type-C USB4, 1x Type-C USB 3.2, 2x USB 3.2 Gen 2 Type-A, 1x HDMI 2.1, 1x MicroSD',
                        'keyboard' => 'Backlit Chiclet Keyboard Single Light RGB',
                    ],
                ],
                'images' => [
                    ['path' => 'products/asus-rog-g14-main.jpg', 'is_primary' => true],
                    ['path' => 'products/asus-rog-g14-side.jpg', 'is_primary' => false],
                ],
            ],
            [
                'category_slug' => 'laptop-bisnis',
                'brand_slug' => 'lenovo',
                'name' => 'Lenovo ThinkPad X1 Carbon Gen 11',
                'sku' => 'LAP-LEN-X1C-G11',
                'condition' => ProductCondition::NEW,
                'price' => 25800000,
                'stock' => 7,
                'weight_grams' => 1120,
                'description' => 'Standar emas laptop bisnis premium dengan serat karbon, bobot super ringan 1.12kg, dan keyboard ThinkPad legendaris.',
                'status' => ProductStatus::PUBLISHED,
                'specs' => [
                    'processor' => 'Intel Core i7-1365U (10 Core, 12 Thread up to 5.20 GHz vPro)',
                    'ram_gb' => 16,
                    'ram_type' => 'LPDDR5 6400MHz Soldered',
                    'storage_capacity' => '512 GB',
                    'storage_type' => 'NVMe PCIe Gen4 Performance SSD',
                    'gpu' => 'Intel Iris Xe Graphics eligible',
                    'screen_size' => 14.0,
                    'screen_resolution' => '1920 x 1200 (WUXGA IPS Anti-Glare 400 nits 100% sRGB)',
                    'physical_condition' => '100% Baru Original Lenovo Box',
                    'battery_condition' => '100% Brand New 57Wh Rapid Charge',
                    'operating_system' => 'Windows 11 Pro 64-bit Original',
                    'warranty' => 'Garansi Resmi Lenovo Indonesia 3 Tahun Onsite Premiere Support',
                    'additional_specs' => [
                        'security' => 'Fingerprint Reader, dTPM 2.0, PrivacyShutter Webcam',
                        'ports' => '2x Thunderbolt 4, 2x USB 3.2 Gen 1, 1x HDMI 2.1, 1x Audio Jack',
                    ],
                ],
                'images' => [
                    ['path' => 'products/thinkpad-x1-main.jpg', 'is_primary' => true],
                ],
            ],
            [
                'category_slug' => 'ultrabook',
                'brand_slug' => 'apple',
                'name' => 'Apple MacBook Air M2 13.6 Inci 2023',
                'sku' => 'LAP-APL-MBA-M2',
                'condition' => ProductCondition::NEW,
                'price' => 15999000,
                'stock' => 10,
                'weight_grams' => 1240,
                'description' => 'Desain ramping serba baru, layar Liquid Retina memukau, chip M2 kencang dan hemat daya dengan baterai hingga 18 jam.',
                'status' => ProductStatus::PUBLISHED,
                'specs' => [
                    'processor' => 'Apple M2 (CPU 8-core dengan 4 performa & 4 efisiensi, Neural Engine 16-core)',
                    'ram_gb' => 8,
                    'ram_type' => 'Unified Memory Bandwidth 100GB/s',
                    'storage_capacity' => '256 GB',
                    'storage_type' => 'Apple High-Speed SSD',
                    'gpu' => 'Apple M2 GPU 8-core',
                    'screen_size' => 13.6,
                    'screen_resolution' => '2560 x 1664 (Liquid Retina True Tone 500 nits)',
                    'physical_condition' => '100% Baru Brand New in Box (BNIB)',
                    'battery_condition' => '100% Brand New 52.6 Wh Lithium Polymer',
                    'operating_system' => 'macOS Sequoia Official',
                    'warranty' => 'Garansi Resmi Apple Indonesia (iBox / Digimap) 1 Tahun',
                    'additional_specs' => [
                        'charging' => 'MagSafe 3 Charging Port + Fast Charging Supported',
                        'camera' => '1080p FaceTime HD camera',
                    ],
                ],
                'images' => [
                    ['path' => 'products/macbook-air-m2-main.jpg', 'is_primary' => true],
                ],
            ],
            [
                'category_slug' => 'laptop-pelajar',
                'brand_slug' => 'hp',
                'name' => 'HP Pavilion Aero 13-be0001AU (Bekas Mulus)',
                'sku' => 'LAP-HP-AERO-USED',
                'condition' => ProductCondition::USED,
                'price' => 7850000,
                'stock' => 3,
                'weight_grams' => 970,
                'description' => 'Laptop bekas istimewa bobot di bawah 1 kg! Bodi magnesium rose gold, performa kencang Ryzen 5 Zen 3, sangat mulus terawat eks pemakaian wanita kantor.',
                'status' => ProductStatus::PUBLISHED,
                'specs' => [
                    'processor' => 'AMD Ryzen 5 5600U (6 Core, 12 Thread up to 4.2 GHz)',
                    'ram_gb' => 16,
                    'ram_type' => 'DDR4 3200MHz Dual Channel',
                    'storage_capacity' => '512 GB',
                    'storage_type' => 'NVMe PCIe M.2 SSD',
                    'gpu' => 'AMD Radeon Vega 7 Graphics',
                    'screen_size' => 13.3,
                    'screen_resolution' => '1920 x 1200 (WUXGA IPS 400 nits 100% sRGB)',
                    'physical_condition' => 'Mulus 97%, tidak ada penyok/dent, lecet pemakaian minim wajar',
                    'battery_condition' => 'Battery Health 91%, Cycle Count 160 (Awet 5-7 Jam)',
                    'operating_system' => 'Windows 11 Home Original',
                    'warranty' => 'Garansi Toko Dunia Laptop 1 Bulan Full Sparepart & Service',
                    'additional_specs' => [
                        'weight' => 'Hanya 970 gram',
                        'audio' => 'Audio by Bang & Olufsen Dual Speakers',
                    ],
                ],
                'images' => [
                    ['path' => 'products/hp-pavilion-aero-main.jpg', 'is_primary' => true],
                ],
            ],
            [
                'category_slug' => 'laptop-gaming',
                'brand_slug' => 'acer',
                'name' => 'Acer Nitro V 15 ANV15-51-5115',
                'sku' => 'LAP-ACER-NITRO-V15',
                'condition' => ProductCondition::NEW,
                'price' => 11499000,
                'stock' => 8,
                'weight_grams' => 2100,
                'description' => 'Laptop gaming terjangkau dengan performa Intel Core Generasi ke-13 dan RTX 2050, siap libas game esports dan video editing.',
                'status' => ProductStatus::PUBLISHED,
                'specs' => [
                    'processor' => 'Intel Core i5-13420H (8 Core, 12 Thread up to 4.60 GHz)',
                    'ram_gb' => 16,
                    'ram_type' => 'DDR5 5200MHz (Dual Channel)',
                    'storage_capacity' => '512 GB',
                    'storage_type' => 'NVMe PCIe Gen4 SSD',
                    'gpu' => 'NVIDIA GeForce RTX 2050 4GB GDDR6',
                    'screen_size' => 15.6,
                    'screen_resolution' => '1920 x 1080 (FHD IPS 144Hz Slim Bezel)',
                    'physical_condition' => '100% Baru Segel Box',
                    'battery_condition' => '100% Brand New 57Wh',
                    'operating_system' => 'Windows 11 Home + OHS 2021',
                    'warranty' => 'Garansi Resmi Acer Indonesia 2 Tahun Sparepart + 3 Tahun Servis',
                    'additional_specs' => [
                        'cooling' => 'Dual Fan Cooling System with NitroSense App',
                    ],
                ],
                'images' => [
                    ['path' => 'products/acer-nitro-v15-main.jpg', 'is_primary' => true],
                ],
            ],
            [
                'category_slug' => 'ultrabook',
                'brand_slug' => 'dell',
                'name' => 'Dell XPS 13 Plus 9320 (Bekas Like New)',
                'sku' => 'LAP-DELL-XPS-9320',
                'condition' => ProductCondition::USED,
                'price' => 16800000,
                'stock' => 2,
                'weight_grams' => 1260,
                'description' => 'Ultrabook futuristik dengan capacitive touch function row, invisible glass touchpad haptic, dan layar OLED sentuh beresolusi 3.5K.',
                'status' => ProductStatus::PUBLISHED,
                'specs' => [
                    'processor' => 'Intel Core i7-1260P (12 Core, 16 Thread up to 4.70 GHz)',
                    'ram_gb' => 16,
                    'ram_type' => 'LPDDR5 5200MHz',
                    'storage_capacity' => '1 TB',
                    'storage_type' => 'NVMe PCIe 4.0 SSD',
                    'gpu' => 'Intel Iris Xe Graphics',
                    'screen_size' => 13.4,
                    'screen_resolution' => '3456 x 2160 (3.5K OLED InfinityEdge Touch 400 nits)',
                    'physical_condition' => 'Kondisi 98% Mulus Like New, no scratch pada layar OLED',
                    'battery_condition' => 'Battery Health 89% (Awet 4-6 Jam)',
                    'operating_system' => 'Windows 11 Pro Original',
                    'warranty' => 'Garansi Toko Dunia Laptop 1 Bulan + Cek Hardware Menyeluruh',
                    'additional_specs' => [
                        'touchpad' => 'Seamless Glass Touchpad with Haptic Feedback',
                    ],
                ],
                'images' => [
                    ['path' => 'products/dell-xps-9320-main.jpg', 'is_primary' => true],
                ],
            ],
        ];

        foreach ($productsData as $data) {
            $category = Category::where('slug', $data['category_slug'])->first();
            $brand = Brand::where('slug', $data['brand_slug'])->first();

            if (! $category || ! $brand) {
                continue;
            }

            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']) . '-' . strtolower(Str::substr($data['sku'], -4)),
                    'condition' => $data['condition'],
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'weight_grams' => $data['weight_grams'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                ]
            );

            // Spesifikasi Laptop
            ProductSpecification::updateOrCreate(
                ['product_id' => $product->id],
                $data['specs']
            );

            // Gambar Produk
            foreach ($data['images'] as $index => $img) {
                ProductImage::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'image_path' => $img['path'],
                    ],
                    [
                        'is_primary' => $img['is_primary'],
                        'sort_order' => $index,
                    ]
                );
            }
        }
    }
}

