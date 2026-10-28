@extends('layouts.admin')

@section('header', 'Dashboard Operasional')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header Banner (Solid High-Contrast Background) -->
    <div class="p-6 rounded-2xl bg-slate-900 text-white shadow-md border border-slate-800">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">
                        Selamat Datang, {{ auth()->user()->name }}
                    </h2>
                    @php
                        $role = auth()->user()->role;
                        $roleVal = $role instanceof \App\Enums\UserRole ? $role->value : (string)$role;
                        $roleLabel = $role instanceof \App\Enums\UserRole ? $role->label() : ucfirst($roleVal);
                        $badgeClass = match($roleVal) {
                            'super_admin' => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
                            'admin' => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
                            default => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
                        };
                    @endphp
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider border {{ $badgeClass }}">
                        {{ $roleLabel }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-2xl">
                    Pantau kinerja omzet penjualan laptop, kelola antrean pesanan pelanggan, serta perbarui ketersediaan unit stok secara langsung.
                </p>
            </div>
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition-all transform active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>+ Tambah Laptop</span>
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.reports.sales') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition-colors">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span>Laporan Omzet</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- 4 Main Operational Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Card 1: Total Omzet Valid -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Omzet Valid</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-2xl font-black text-slate-900 tracking-tight font-mono">
                Rp {{ number_format($validSales, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-[11px] text-slate-500 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                <span>Pesanan processing, shipped, & completed</span>
            </p>
        </div>

        <!-- Card 2: Pesanan Baru & Perlu Diproses -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pesanan Aktif</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $newOrdersCount }}</span>
                <span class="text-xs text-slate-500">pesanan</span>
            </div>
            <div class="mt-1 text-[11px] text-slate-500 flex items-center gap-2">
                <span class="text-amber-600 font-semibold">{{ $pendingOrdersCount }} pending</span>
                <span>&bull;</span>
                <span class="text-blue-600 font-semibold">{{ $processingOrdersCount }} diproses</span>
            </div>
        </div>

        <!-- Card 3: Stok Menipis -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Stok Kritis (&le; 2)</span>
                <div class="w-10 h-10 rounded-xl {{ $lowStockCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-900' }} tracking-tight">{{ $lowStockCount }}</span>
                <span class="text-xs text-slate-500">unit laptop</span>
            </div>
            <p class="mt-1 text-[11px] {{ $lowStockCount > 0 ? 'text-rose-600 font-semibold' : 'text-slate-500' }}">
                {{ $lowStockCount > 0 ? 'Perlu tindakan restok segera' : 'Kapasitas stok aman' }}
            </p>
        </div>

        <!-- Card 4: Total Katalog Laptop -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-slate-300 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Katalog Aktif</span>
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-2xl font-black text-slate-900 tracking-tight">{{ $totalProductsCount }}</span>
                <span class="text-xs text-slate-500">model produk</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">
                Dipublikasikan di storefront toko
            </p>
        </div>
    </div>

    <!-- Alert Panel if Low Stock Exists -->
    @if($lowStockProducts->isNotEmpty())
        <div class="p-5 rounded-2xl bg-amber-50 border border-amber-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-xs sm:text-sm font-bold text-amber-900">Peringatan: Stok Laptop Menipis</h3>
                </div>
                <a href="{{ route('admin.products.index', ['stock_status' => 'low_stock']) }}" class="text-xs font-bold text-amber-800 hover:text-amber-950 underline">
                    Lihat Semua Stok Menipis &rarr;
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($lowStockProducts as $lowItem)
                    <div class="p-3 bg-white rounded-xl border border-amber-200 shadow-sm flex items-center justify-between">
                        <div class="overflow-hidden pr-2">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ $lowItem->name }}</p>
                            <p class="text-[10px] text-slate-500">SKU: {{ $lowItem->sku }} &bull; {{ $lowItem->brand_name ?? 'Umum' }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black {{ $lowItem->stock == 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' }}">
                                Sisa: {{ $lowItem->stock }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Recent Transactions Table & Side Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Recent Orders Table (2 Columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Pesanan Terbaru Masuk</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Daftar transaksi pelanggan terkini</p>
                </div>
                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline">
                        Lihat Seluruh Pesanan &rarr;
                    </a>
                @endif
            </div>

            @if($recentOrders->isEmpty())
                <div class="p-8 text-center text-slate-500">
                    <p class="text-xs">Belum ada transaksi pesanan yang masuk.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">No. Pesanan</th>
                                <th class="py-3 px-4">Pelanggan</th>
                                <th class="py-3 px-4">Total</th>
                                <th class="py-3 px-4">Status Pesanan</th>
                                <th class="py-3 px-4">Pembayaran</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-blue-600">
                                        {{ $order->order_number }}
                                        <div class="text-[10px] text-slate-400 font-sans font-normal">
                                            {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-slate-900">{{ $order->customer_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $order->customer_email }}</div>
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-900 font-mono">
                                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $orderStatusBadge = match($order->order_status) {
                                                'completed' => 'bg-emerald-100 text-emerald-800',
                                                'processing' => 'bg-blue-100 text-blue-800',
                                                'shipped' => 'bg-purple-100 text-purple-800',
                                                'cancelled' => 'bg-rose-100 text-rose-800',
                                                default => 'bg-amber-100 text-amber-800',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $orderStatusBadge }}">
                                            {{ ucfirst($order->order_status) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $payStatusBadge = match($order->payment_status) {
                                                'settlement', 'capture' => 'text-emerald-600 font-bold',
                                                'pending' => 'text-amber-600 font-semibold',
                                                'cancel', 'expire', 'deny' => 'text-rose-600 font-semibold',
                                                default => 'text-slate-500',
                                            };
                                        @endphp
                                        <span class="text-[11px] {{ $payStatusBadge }}">
                                            {{ $order->payment_status ? ucfirst($order->payment_status) : 'Menunggu' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                            <a href="{{ route('admin.orders.show', $order->order_number) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-blue-50 text-blue-600 hover:text-blue-700 text-xs font-semibold transition-colors">
                                                Kelola &rarr;
                                            </a>
                                        @else
                                            <span class="text-[10px] text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Right: Shortcuts & Access Information -->
        <div class="space-y-6">
            <!-- Shortcuts Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Tautan Cepat</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.products.create') }}" class="w-full flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-100 hover:border-blue-200 transition-colors text-xs font-bold text-slate-800 hover:text-blue-700">
                        <span class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            Tambah Laptop Baru
                        </span>
                        <span>&rarr;</span>
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="w-full flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-100 transition-colors text-xs font-bold text-slate-800">
                        <span class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            Kelola Stok & Harga
                        </span>
                        <span>&rarr;</span>
                    </a>

                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.orders.index') }}" class="w-full flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-100 transition-colors text-xs font-bold text-slate-800">
                            <span class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                Proses Pesanan Masuk
                            </span>
                            <span>&rarr;</span>
                        </a>

                        <a href="{{ route('admin.reports.sales') }}" class="w-full flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-100 transition-colors text-xs font-bold text-slate-800">
                            <span class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                Unduh / Cetak Laporan
                            </span>
                            <span>&rarr;</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Role Permissions Overview Card -->
            <div class="bg-slate-900 rounded-2xl p-5 text-slate-300 border border-slate-800 text-xs">
                <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Akses Role Anda
                </h4>
                <p class="text-slate-400 mb-3 leading-relaxed">
                    Anda masuk sebagai <strong class="text-white">{{ $roleLabel }}</strong>.
                </p>
                <ul class="space-y-1.5 text-slate-300">
                    <li class="flex items-center gap-2">
                        <span class="text-emerald-400 font-bold">&check;</span> Dashboard metrik & katalog produk
                    </li>
                    <li class="flex items-center gap-2">
                        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                            <span class="text-emerald-400 font-bold">&check;</span> Kelola pesanan & input resi kurir
                        @else
                            <span class="text-slate-500 font-bold">&times;</span> <span class="text-slate-500">Akses pesanan (Khusus Admin)</span>
                        @endif
                    </li>
                    <li class="flex items-center gap-2">
                        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                            <span class="text-emerald-400 font-bold">&check;</span> Laporan penjualan & omzet toko
                        @else
                            <span class="text-slate-500 font-bold">&times;</span> <span class="text-slate-500">Laporan omzet (Khusus Admin)</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
