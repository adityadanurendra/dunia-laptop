<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make(env('DEFAULT_USER_PASSWORD', '12345678'));

        // 1. Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Administrator',
                'password' => $defaultPassword,
                'role' => UserRole::SUPER_ADMIN,
                'phone' => '081200000001',
                'email_verified_at' => now(),
            ]
        );

        // 2. Admin Operasional Toko
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Store Admin Operasional',
                'password' => $defaultPassword,
                'role' => UserRole::ADMIN,
                'phone' => '081200000002',
                'email_verified_at' => now(),
            ]
        );

        // 3. Staff Produk & Inventori
        User::updateOrCreate(
            ['email' => 'staff@gmail.com'],
            [
                'name' => 'Staff Katalog & Stok',
                'password' => $defaultPassword,
                'role' => UserRole::STAFF,
                'phone' => '081200000003',
                'email_verified_at' => now(),
            ]
        );

        // 4. Pelanggan Contoh (Customer)
        $customer = User::updateOrCreate(
            ['email' => 'customer@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'password' => $defaultPassword,
                'role' => UserRole::CUSTOMER,
                'phone' => '081298765432',
                'email_verified_at' => now(),
            ]
        );

        // Buat alamat default untuk pelanggan contoh
        Address::updateOrCreate(
            [
                'user_id' => $customer->id,
                'label' => 'Rumah Utama',
            ],
            [
                'recipient_name' => 'Budi Santoso',
                'phone' => '081298765432',
                'address_line' => 'Jl. Kaliurang KM 5, Gang Megatruh No. 12',
                'city' => 'Sleman',
                'province' => 'DI Yogyakarta',
                'postal_code' => '55281',
                'is_default' => true,
            ]
        );
    }
}

