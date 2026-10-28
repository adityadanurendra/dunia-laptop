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
                            'badge' => 'Official Gaming Partner',
                            'title' => 'New ROG Zephyrus 2024 Series',
                            'subtitle' => 'Performa AI Gaming Tanpa Kompromi dengan Layar ROG Nebula 165Hz & RTX 40 Series.',
                            'cta_text' => 'Beli Sekarang',
                            'cta_url' => '/category/laptop-gaming',
                        ],
                        [
                            'image' => 'banners/hero-thinkpad-promo.jpg',
                            'badge' => 'Enterprise Grade',
                            'title' => 'ThinkPad Premium Business Sale',
                            'subtitle' => 'Ketahanan Militer Karbon Ringan untuk Eksekutif & Profesional Aktif.',
                            'cta_text' => 'Lihat Seri ThinkPad',
                            'cta_url' => '/category/laptop-bisnis',
                        ],
                    ],
                ],
            ],
            [
                'section_type' => 'category_showcase',
                'title' => 'Pilih Laptop Sesuai Kebutuhan Anda',
                'subtitle' => 'Kategori pilihan paling populer untuk pelajar, kreator, hingga gamer profesional',
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
                'section_type' => 'featured_products',
                'title' => 'Laptop Unggulan Pilihan Editor',
                'subtitle' => 'Rekomendasi unit performa terbaik dengan spesifikasi paling diminati bulan ini',
                'sort_order' => 3,
                'is_active' => true,
                'content_data' => [
                    'limit' => 4,
                    'featured_skus' => [
                        'LAP-ASUS-ROG-G14',
                        'LAP-LEN-X1C-G11',
                        'LAP-APL-MBA-M2',
                        'LAP-DELL-XPS-9320',
                    ],
                    'cta_text' => 'Jelajahi Semua Katalog',
                    'cta_url' => '/products',
                ],
            ],
            [
                'section_type' => 'promo_grid',
                'title' => 'Laptop Bekas Mulus Bergaransi',
                'subtitle' => 'Unit pilihan telah melalui 25 titik inspeksi ketat teknisi bersertifikasi',
                'sort_order' => 4,
                'is_active' => true,
                'content_data' => [
                    'badge' => 'Garansi 30 Hari Ganti Unit',
                    'highlight_points' => [
                        '100% Fungsi Normal Teruji',
                        'Bukan Bekas Servis Mesin / Korosi',
                        'Baterai Sehat di Atas 85%',
                        'Gratis Instalasi Software Lengkap',
                    ],
                    'cta_text' => 'Lihat Laptop Bekas Pilihan',
                    'cta_url' => '/products?condition=used',
                ],
            ],
            [
                'section_type' => 'latest_products',
                'title' => 'Koleksi Laptop Terbaru',
                'subtitle' => 'Stok laptop baru & bekas berkualitas yang baru saja mendarat di etalase kami',
                'sort_order' => 5,
                'is_active' => true,
                'content_data' => [
                    'limit' => 6,
                    'cta_text' => 'Lihat Seluruh Produk',
                    'cta_url' => '/products?sort=latest',
                ],
            ],
            [
                'section_type' => 'faq',
                'title' => 'Pertanyaan yang Sering Diajukan (FAQ)',
                'subtitle' => 'Informasi penting seputar garansi, kondisi unit bekas, pengiriman berasuransi, dan pembayaran',
                'sort_order' => 6,
                'is_active' => true,
                'content_data' => [
                    'items' => [
                        [
                            'question' => 'Apa perbedaan laptop Baru dan Bekas di Dunia Laptop?',
                            'answer' => 'Laptop Baru berstatus 100% Brand New In Box (BNIB) tersegel dengan garansi resmi distributor Indonesia (1–3 tahun). Laptop Bekas merupakan unit seken pilihan yang lolos 25 titik uji kualitas perangkat keras, bukan bekas servis mesin, memiliki kesehatan baterai prima, dan dilindungi Garansi Toko penuh.',
                        ],
                        [
                            'question' => 'Bagaimana jaminan keamanan pengiriman laptop ke luar kota?',
                            'answer' => 'Seluruh pengiriman laptop ke luar daerah wajib dikemas menggunakan bubble wrap tebal berlapis, pelindung sudut, serta packing kayu khusus dan perlindungan asuransi penuh hingga tiba di alamat Anda.',
                        ],
                        [
                            'question' => 'Metode pembayaran apa saja yang didukung?',
                            'answer' => 'Pembayaran diproses secara otomatis dan aman melalui gerbang pembayaran resmi Midtrans Snap yang mendukung Virtual Account berbagai bank nasional (BCA, Mandiri, BNI, BRI, Permata), QRIS, E-Wallet, serta Kartu Kredit.',
                        ],
                        [
                            'question' => 'Apakah saya bisa memeriksa kecocokan spesifikasi sebelum membeli?',
                            'answer' => 'Tentu! Setiap halaman detail laptop mencantumkan tabel spesifikasi lengkap mulai dari prosesor, kapasitas & tipe RAM, NVMe SSD, kartu grafis, resolusi layar, hingga kondisi fisik dan kesehatan baterai aktual untuk unit bekas.',
                        ],
                    ],
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
