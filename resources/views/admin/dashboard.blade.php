<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Operasional') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-2">Panel Operasional Toko</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Selamat datang di panel pengelolaan operasional toko laptop. Akses menu pesanan, produk, dan laporan tersedia sesuai peran Anda ({{ auth()->user()->role->label() ?? auth()->user()->role }}).
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

