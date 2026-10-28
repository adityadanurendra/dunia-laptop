<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_title',
                'group' => 'general',
                'value' => 'Dunia Laptop - Toko Laptop Baru & Bekas Terpercaya',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'site_tagline',
                'group' => 'general',
                'value' => 'Pusat Laptop Terbaik, Bergaransi Resmi & Toko di Indonesia',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'company_name',
                'group' => 'general',
                'value' => 'PT Dunia Laptop Indonesia',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'contact_whatsapp',
                'group' => 'contact',
                'value' => '081234567890',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'contact_email',
                'group' => 'contact',
                'value' => 'cs@dunialaptop.com',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'store_address',
                'group' => 'contact',
                'value' => 'Jl. Affandi (Gejayan) No. 45, Sleman, D.I. Yogyakarta 55281',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'operating_hours',
                'group' => 'contact',
                'value' => 'Senin - Minggu, 09:00 - 21:00 WIB',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'theme_primary_color',
                'group' => 'appearance',
                'value' => '#2563eb',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'theme_accent_color',
                'group' => 'appearance',
                'value' => '#0ea5e9',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'midtrans_environment',
                'group' => 'payment',
                'value' => 'sandbox',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'midtrans_client_key',
                'group' => 'payment',
                'value' => 'SB-Mid-client-sample-key-12345',
                'type' => 'string',
                'is_public' => true,
            ],
            [
                'key' => 'midtrans_server_key',
                'group' => 'payment',
                'value' => 'SB-Mid-server-sample-key-67890',
                'type' => 'string',
                'is_public' => false,
            ],
            [
                'key' => 'shipping_origin_city_id',
                'group' => 'shipping',
                'value' => '419', // ID Kota Sleman / Yogyakarta
                'type' => 'string',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'group' => $setting['group'],
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'is_public' => $setting['is_public'],
                ]
            );
        }
    }
}

