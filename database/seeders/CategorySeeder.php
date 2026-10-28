<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Laptop Gaming',
                'slug' => 'laptop-gaming',
                'description' => 'Laptop bertenaga tinggi dengan GPU diskrit untuk gaming berat dan rendering 3D.',
            ],
            [
                'name' => 'Ultrabook & Tipis',
                'slug' => 'ultrabook',
                'description' => 'Laptop tipis, ringan, dan baterai tahan lama untuk mobilitas harian tinggi.',
            ],
            [
                'name' => 'Laptop Bisnis & Kerja',
                'slug' => 'laptop-bisnis',
                'description' => 'Laptop berdaya tahan militer, fitur keamanan tingkat tinggi, dan keyboard nyaman.',
            ],
            [
                'name' => 'Laptop 2-in-1 Konvertibel',
                'slug' => 'laptop-2-in-1',
                'description' => 'Laptop layar sentuh yang fleksibel dilipat menjadi tablet dengan dukungan stylus.',
            ],
            [
                'name' => 'Laptop Murah / Pelajar',
                'slug' => 'laptop-pelajar',
                'description' => 'Pilihan laptop terjangkau untuk kebutuhan sekolah, kuliah, dan ketik dokumen.',
            ],
            [
                'name' => 'Aksesoris & Sparepart',
                'slug' => 'aksesoris-laptop',
                'description' => 'Charger original, tas laptop, mouse, sleeve case, dan pendingin laptop.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}

