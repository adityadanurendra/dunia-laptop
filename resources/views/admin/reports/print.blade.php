<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan ({{ $startDate }} - {{ $endDate }}) | Dunia Laptop</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 11pt;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans p-4 sm:p-8 text-slate-900">
    <!-- Non-print Control Bar -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold text-slate-600">Dokumen Cetak Laporan Penjualan</span>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Sekarang (Print / PDF)</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                Tutup
            </button>
        </div>
    </div>

    <!-- Printable Paper Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-2xl shadow-sm border border-slate-200 print:border-none print:shadow-none print:p-0">
        <!-- Letterhead / Kop Laporan -->
        <div class="border-b-2 border-slate-900 pb-4 mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 uppercase">Dunia Laptop</h1>
                <p class="text-xs text-slate-600">Pusat Penjualan Laptop Baru & Bekas Berkualitas</p>
                <p class="text-[10px] text-slate-500">Website: https://dunialaptop.test &bull; Email: info@dunialaptop.test</p>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-slate-100 border border-slate-300 font-mono font-bold text-xs uppercase">
                    Laporan Penjualan
                </span>
                <p class="text-[10px] text-slate-500 mt-1">Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
        </div>

        <!-- Period Info -->
        <div class="mb-6 p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between text-xs">
            <div>
                <span class="text-slate-500 font-medium">Periode Transaksi:</span>
                <strong class="ml-1 text-slate-900">{{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</strong>
            </div>
            <div>
                <span class="text-slate-500 font-medium">Status Filter:</span>
                <strong class="ml-1 text-slate-900 capitalize">{{ $selectedStatus === 'valid' ? 'Pesanan Valid (Settlement/Processing)' : $selectedStatus }}</strong>
            </div>
        </div>

        <!-- Executive Summary Metrics -->
        <div class="grid grid-cols-3 gap-4 mb-8 text-center text-xs">
            <div class="p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                <span class="text-slate-500 block uppercase font-bold text-[10px]">Total Omzet Penjualan</span>
                <strong class="text-lg font-black text-slate-900 block mt-1 font-mono">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </strong>
            </div>
            <div class="p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                <span class="text-slate-500 block uppercase font-bold text-[10px]">Jumlah Transaksi Berhasil</span>
                <strong class="text-lg font-black text-slate-900 block mt-1 font-mono">
                    {{ $totalOrdersCount }} Transaksi
                </strong>
            </div>
            <div class="p-3 border border-slate-200 rounded-xl bg-slate-50/50">
                <span class="text-slate-500 block uppercase font-bold text-[10px]">Total Unit Terjual</span>
                <strong class="text-lg font-black text-slate-900 block mt-1 font-mono">
                    {{ $totalItemsSold }} Unit
                </strong>
            </div>
        </div>

        <!-- Table of Transactions -->
        <div class="mb-8">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Rincian Buku Kas Transaksi</h3>
            <table class="w-full text-left text-xs border border-slate-200">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-2 px-3 border-r border-slate-200 w-12 text-center">No.</th>
                        <th class="py-2 px-3 border-r border-slate-200">No. Pesanan</th>
                        <th class="py-2 px-3 border-r border-slate-200">Tanggal</th>
                        <th class="py-2 px-3 border-r border-slate-200">Pelanggan</th>
                        <th class="py-2 px-3 border-r border-slate-200 text-center">Status</th>
                        <th class="py-2 px-3 text-right">Total Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($allOrders as $index => $order)
                        <tr>
                            <td class="py-1.5 px-3 border-r border-slate-200 text-center">{{ $index + 1 }}</td>
                            <td class="py-1.5 px-3 border-r border-slate-200 font-mono font-bold">{{ $order->order_number }}</td>
                            <td class="py-1.5 px-3 border-r border-slate-200">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="py-1.5 px-3 border-r border-slate-200 font-semibold">{{ $order->customer_name }}</td>
                            <td class="py-1.5 px-3 border-r border-slate-200 text-center capitalize">{{ $order->status }}</td>
                            <td class="py-1.5 px-3 text-right font-mono font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-slate-500">Tidak ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-300">
                    <tr>
                        <td colspan="5" class="py-2 px-3 text-right uppercase">Total Penjualan:</td>
                        <td class="py-2 px-3 text-right font-mono text-sm text-slate-900">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signature Endorsement -->
        <div class="mt-12 pt-6 border-t border-slate-200 grid grid-cols-2 text-xs">
            <div>
                <p class="text-slate-500">Disiapkan Oleh:</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-900">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-500">Petugas Operasional</p>
            </div>
            <div class="text-right">
                <p class="text-slate-500">Mengetahui & Menyetujui:</p>
                <div class="h-16"></div>
                <p class="font-bold text-slate-900">( ........................................ )</p>
                <p class="text-[10px] text-slate-500">Manajer Keuangan / Super Admin</p>
            </div>
        </div>
    </div>
</body>
</html>

