@extends('layouts.admin')

@section('header', 'Laporan Penjualan & Omzet')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">Laporan Penjualan & Omzet</h2>
            <p class="text-xs text-slate-500 mt-0.5">Analisis performa finansial, volume penjualan unit laptop, dan produk terlaris</p>
        </div>
        <div>
            <a href="{{ route('admin.reports.sales.print', request()->query()) }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md shadow-slate-900/20 transition-all">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Laporan / Export PDF</span>
            </a>
        </div>
    </div>

    <!-- Date Range & Preset Filter Form -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="flex flex-col lg:flex-row lg:items-end gap-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 flex-1">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Dari Tanggal (Mulai)</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sampai Tanggal (Selesai)</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <div class="w-full lg:w-56">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Transaksi</label>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="valid" {{ $selectedStatus === 'valid' ? 'selected' : '' }}>Transaksi Valid (Processing, Shipped, Selesai)</option>
                    <option value="completed" {{ $selectedStatus === 'completed' ? 'selected' : '' }}>Hanya Selesai (Completed)</option>
                    <option value="processing" {{ $selectedStatus === 'processing' ? 'selected' : '' }}>Hanya Processing</option>
                    <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status (Termasuk Pending & Cancel)</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-colors shadow-sm shadow-blue-600/30">
                    Filter Laporan
                </button>
            </div>
        </form>

        <!-- Quick Presets -->
        <div class="pt-3 border-t border-slate-100 flex items-center gap-2 flex-wrap text-xs">
            <span class="text-slate-400 font-medium">Preset Cepat:</span>
            <a href="{{ route('admin.reports.sales', ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString(), 'status' => $selectedStatus]) }}" 
                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-colors">
                Hari Ini
            </a>
            <a href="{{ route('admin.reports.sales', ['start_date' => now()->subDays(7)->toDateString(), 'end_date' => now()->toDateString(), 'status' => $selectedStatus]) }}" 
                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-colors">
                7 Hari Terakhir
            </a>
            <a href="{{ route('admin.reports.sales', ['start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->toDateString(), 'status' => $selectedStatus]) }}" 
                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-colors">
                Bulan Ini
            </a>
            <a href="{{ route('admin.reports.sales', ['start_date' => now()->subMonth()->startOfMonth()->toDateString(), 'end_date' => now()->subMonth()->endOfMonth()->toDateString(), 'status' => $selectedStatus]) }}" 
                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-colors">
                Bulan Lalu
            </a>
            <a href="{{ route('admin.reports.sales', ['start_date' => now()->startOfYear()->toDateString(), 'end_date' => now()->toDateString(), 'status' => $selectedStatus]) }}" 
                class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition-colors">
                Tahun Ini
            </a>
        </div>
    </div>

    <!-- 4 Report Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Omzet Penjualan</span>
            <p class="mt-2 text-2xl font-black text-slate-900 tracking-tight font-mono">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-[11px] text-slate-500">
                Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pesanan Berhasil</span>
            <p class="mt-2 text-2xl font-black text-blue-600 tracking-tight font-mono">
                {{ number_format($totalOrdersCount, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-[11px] text-slate-500">
                Total transaksi terverifikasi
            </p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Unit Terjual</span>
            <p class="mt-2 text-2xl font-black text-emerald-600 tracking-tight font-mono">
                {{ number_format($totalItemsSold, 0, ',', '.') }} <span class="text-sm font-sans font-normal text-slate-500">unit</span>
            </p>
            <p class="mt-1 text-[11px] text-slate-500">
                Laptop fisik keluar gudang
            </p>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Rata-rata Nilai Pesanan</span>
            <p class="mt-2 text-2xl font-black text-indigo-600 tracking-tight font-mono">
                Rp {{ number_format($averageOrderValue, 0, ',', '.') }}
            </p>
            <p class="mt-1 text-[11px] text-slate-500">
                Average Order Value (AOV)
            </p>
        </div>
    </div>

    <!-- Leaderboard Top Selling Products & Daily Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Selling Laptops -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Top 5 Laptop Terlaris</h3>
                    <p class="text-[11px] text-slate-500">Produk dengan kuantitas penjualan tertinggi</p>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 border border-blue-100">
                    Leaderboard
                </span>
            </div>
            @if($topProducts->isEmpty())
                <div class="p-8 text-center text-slate-500 text-xs">
                    Belum ada data penjualan pada rentang tanggal ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100">
                            <tr>
                                <th class="py-3 px-4">Peringkat & Produk</th>
                                <th class="py-3 px-4 text-center">Unit Terjual</th>
                                <th class="py-3 px-4 text-right">Total Penjualan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($topProducts as $index => $item)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-[10px]">
                                                {{ $index + 1 }}
                                            </span>
                                            <div>
                                                <div class="font-bold text-slate-900">{{ $item->product_name }}</div>
                                                <div class="text-[10px] text-slate-400 font-mono">SKU: {{ $item->product_sku }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-900">
                                        {{ $item->total_quantity }} unit
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">
                                        Rp {{ number_format($item->total_sales, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- Daily Sales Breakdown -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Rekapitulasi Harian</h3>
                    <p class="text-[11px] text-slate-500">Tren penjualan per tanggal dalam rentang filter</p>
                </div>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100">
                    Tren Harian
                </span>
            </div>
            @if($dailySales->isEmpty())
                <div class="p-8 text-center text-slate-500 text-xs">
                    Belum ada rekapan harian pada rentang tanggal ini.
                </div>
            @else
                <div class="overflow-x-auto max-h-72">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100 sticky top-0">
                            <tr>
                                <th class="py-3 px-4">Tanggal Transaksi</th>
                                <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                                <th class="py-3 px-4 text-right">Omzet Harian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($dailySales as $day)
                                <tr class="hover:bg-slate-50/80">
                                    <td class="py-2.5 px-4 font-semibold text-slate-900">
                                        {{ \Carbon\Carbon::parse($day->sale_date)->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="py-2.5 px-4 text-center font-semibold text-slate-700">
                                        {{ $day->orders_count }} pesanan
                                    </td>
                                    <td class="py-2.5 px-4 text-right font-mono font-bold text-slate-900">
                                        Rp {{ number_format($day->daily_total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Detailed Ledger Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-sm font-bold text-slate-900">Rincian Buku Kas Transaksi</h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar transaksi pesanan individual sesuai filter tanggal aktif</p>
        </div>

        @if($ordersList->isEmpty())
            <div class="p-10 text-center text-slate-500 text-xs">
                Tidak ada pesanan individual yang tercatat dalam periode ini.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">No. Pesanan</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Pelanggan</th>
                            <th class="py-3 px-4">Status Pesanan</th>
                            <th class="py-3 px-4">Status Bayar</th>
                            <th class="py-3 px-4 text-right">Total Transaksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($ordersList as $ord)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-3 px-4 font-mono font-bold text-blue-600">
                                    <a href="{{ route('admin.orders.show', $ord->order_number) }}" class="hover:underline">
                                        {{ $ord->order_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    {{ \Carbon\Carbon::parse($ord->created_at)->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-900">
                                    {{ $ord->customer_name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">
                                        {{ ucfirst($ord->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-[11px] font-semibold {{ $ord->payment_status === 'settlement' ? 'text-emerald-600' : 'text-slate-600' }}">
                                        {{ $ord->payment_status ? ucfirst($ord->payment_status) : 'Pending' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($ord->total_amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $ordersList->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

