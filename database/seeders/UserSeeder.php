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
        // 1. Super Admin (Kata sandi diambil dari environment variable untuk keamanan produksi)
        $superAdminPassword = env('SUPER_ADMIN_PASSWORD', 'SuperAdminSecret123!');
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@dunialaptop.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make($superAdminPassword),
                'role' => UserRole::SUPER_ADMIN,
                'phone' => '081200000001',
                'email_verified_at' => now(),
            ]
        );

        // 2. Admin Operasional Toko
        $adminPassword = env('ADMIN_PASSWORD', 'AdminSecret123!');
        User::updateOrCreate(
            ['email' => 'admin@dunialaptop.com'],
            [
                'name' => 'Store Admin Operasional',
                'password' => Hash::make($adminPassword),
                'role' => UserRole::ADMIN,
                'phone' => '081200000002',
                'email_verified_at' => now(),
            ]
        );

        // 3. Staff Produk & Inventori
        $staffPassword = env('STAFF_PASSWORD', 'StaffSecret123!');
        User::updateOrCreate(
            ['email' => 'staff@dunialaptop.com'],
            [
                'name' => 'Staff Katalog & Stok',
                'password' => Hash::make($staffPassword),
                'role' => UserRole::STAFF,
                'phone' => '081200000003',
                'email_verified_at' => now(),
            ]
        );

        // 4. Pelanggan Contoh (Customer)
        $customer = User::updateOrCreate(
            ['email' => 'customer@dunialaptop.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('CustomerSecret123!'),
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

