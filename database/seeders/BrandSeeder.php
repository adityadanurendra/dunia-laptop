<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'ASUS', 'slug' => 'asus', 'logo' => 'brands/asus.png'],
            ['name' => 'Lenovo', 'slug' => 'lenovo', 'logo' => 'brands/lenovo.png'],
            ['name' => 'Apple', 'slug' => 'apple', 'logo' => 'brands/apple.png'],
            ['name' => 'HP', 'slug' => 'hp', 'logo' => 'brands/hp.png'],
            ['name' => 'Acer', 'slug' => 'acer', 'logo' => 'brands/acer.png'],
            ['name' => 'Dell', 'slug' => 'dell', 'logo' => 'brands/dell.png'],
            ['name' => 'MSI', 'slug' => 'msi', 'logo' => 'brands/msi.png'],
        ];

        foreach ($brands as $brand) {
            Brand::updateOrCreate(
                ['slug' => $brand['slug']],
                [
                    'name' => $brand['name'],
                    'logo' => $brand['logo'],
                    'is_active' => true,
                ]
            );
        }
    }
}

