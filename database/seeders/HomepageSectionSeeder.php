<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'section_type' => 'hero_banner',
                'title' => 'Promo Spesial Laptop Gaming & Kerja',
                'subtitle' => 'Dapatkan cashback hingga Rp 2.000.000 + garansi resmi terpanjang',
                'sort_order' => 1,
                'is_active' => true,
                'content_data' => [
                    'banners' => [
                        [
                            'image' => 'banners/hero-rog-promo.jpg',
                            'title' => 'New ROG Zephyrus 2024 Series',
                            'subtitle' => 'Performa AI Gaming Tanpa Kompromi',
                            'cta_text' => 'Beli Sekarang',
                            'cta_url' => '/category/laptop-gaming',
                        ],
                        [
                            'image' => 'banners/hero-thinkpad-promo.jpg',
                            'title' => 'ThinkPad Premium Business Sale',
                            'subtitle' => 'Ketahanan Militer untuk Profesional Aktif',
                            'cta_text' => 'Lihat Seri ThinkPad',
                            'cta_url' => '/category/laptop-bisnis',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'category_showcase',
                'title' => 'Pilih Laptop Sesuai Kebutuhan Anda',
                'subtitle' => 'Kategori pilihan paling populer untuk pelajar, kreator, hingga gamer',
                'sort_order' => 2,
                'is_active' => true,
                'content_data' => [
                    'categories_slug' => [
                        'laptop-gaming',
                        'ultrabook',
                        'laptop-bisnis',
                        'laptop-pelajar',
                    ],
                ],
            ],
            [
                'section_type' => 'promo_grid',
                'title' => 'Laptop Bekas Mulus Bergaransi',
                'subtitle' => 'Unit pilihan telah melalui 25 titik inspeksi ketat teknisi bersertifikasi',
                'sort_order' => 3,
                'is_active' => true,
                'content_data' => [
                    'badge' => 'Garansi 30 Hari Ganti Unit',
                    'highlight_points' => [
                        '100% Fungsi Normal Teruji',
                        'Bukan Bekas Servis Mesin / Korosi',
                        'Baterai Sehat di Atas 85%',
                        'Gratis Instalasi Software Lengkap',
                    ],
                    'cta_url' => '/products?condition=used',
                ],
            ],
        ];

        foreach ($sections as $sec) {
            HomepageSection::updateOrCreate(
                ['section_type' => $sec['section_type']],
                [
                    'title' => $sec['title'],
                    'subtitle' => $sec['subtitle'],
                    'sort_order' => $sec['sort_order'],
                    'is_active' => $sec['is_active'],
                    'content_data' => $sec['content_data'],
                ]
            );
        }
    }
}

