<?php

namespace Database\Seeders;

use App\Models\ChatbotTemplate;
use Illuminate\Database\Seeder;

class ChatbotTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'category' => 'warranty_policy',
                'trigger_keywords' => 'garansi,klaim garansi,garansi bekas,warranty,rusak',
                'response_type' => 'static',
                'static_response' => "Ketentuan Garansi Dunia Laptop:\n1. Laptop Baru: Garansi Resmi Brand (ASUS/Lenovo/Apple/dll) 1-3 tahun.\n2. Laptop Bekas: Garansi Toko 1 Bulan Full Sparepart & Servis (bukan karena kelalaian pemakaian/air).\n3. Layanan bantuan klaim kami siap bantu tanpa biaya tambahan!",
                'dynamic_handler' => null,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'category' => 'store_info',
                'trigger_keywords' => 'lokasi,alamat,toko fisik,buka jam berapa,jam operasional,offline store',
                'response_type' => 'static',
                'static_response' => "Toko fisik Dunia Laptop beralamat di Jl. Affandi (Gejayan) No. 45, Sleman, D.I. Yogyakarta.\nJam Buka:\nSenin - Sabtu: 09.00 - 21.00 WIB\nMinggu: 10.00 - 18.00 WIB\nBisa langsung datang untuk tes unit sepuasnya!",
                'dynamic_handler' => null,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'category' => 'payment_method',
                'trigger_keywords' => 'cara bayar,metode pembayaran,midtrans,cicilan,kartu kredit,qris,transfer bank,cod',
                'response_type' => 'static',
                'static_response' => "Kami menerima berbagai metode pembayaran aman via Midtrans:\n- Virtual Account (BCA, Mandiri, BNI, BRI, Permata)\n- QRIS (GoPay, OVO, Dana, ShopeePay)\n- Kartu Kredit / Cicilan 0% hingga 12 bulan\n- Pembayaran instan via Alfamart/Indomaret.",
                'dynamic_handler' => null,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'category' => 'product_inquiry',
                'trigger_keywords' => 'stok,apakah ready,ketersediaan,ada stok,laptop gaming ready',
                'response_type' => 'dynamic',
                'static_response' => null,
                'dynamic_handler' => 'App\\Services\\Chatbot\\Handlers\\CheckProductStockHandler',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'category' => 'product_inquiry',
                'trigger_keywords' => 'rekomendasi,laptop budget,rekomendasi laptop,cari laptop,laptop 5 jutaan,laptop 10 jutaan',
                'response_type' => 'dynamic',
                'static_response' => null,
                'dynamic_handler' => 'App\\Services\\Chatbot\\Handlers\\RecommendLaptopHandler',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $tpl) {
            ChatbotTemplate::updateOrCreate(
                [
                    'category' => $tpl['category'],
                    'sort_order' => $tpl['sort_order'],
                ],
                [
                    'trigger_keywords' => $tpl['trigger_keywords'],
                    'response_type' => $tpl['response_type'],
                    'static_response' => $tpl['static_response'],
                    'dynamic_handler' => $tpl['dynamic_handler'],
                    'is_active' => $tpl['is_active'],
                ]
            );
        }
    }
}

