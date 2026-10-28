@extends('layouts.admin')

@section('header', 'Manajemen Pesanan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">Manajemen Pesanan</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola alur pemrosesan pesanan pelanggan, pembayaran, dan pengiriman kurir</p>
        </div>
    </div>

    <!-- Status Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-200 text-xs font-semibold">
        <a href="{{ route('admin.orders.index') }}" 
            class="px-3.5 py-2 rounded-xl flex items-center gap-2 whitespace-nowrap transition-colors {{ empty($currentStatus) ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <span>Semua Pesanan</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ empty($currentStatus) ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                {{ $statusCounts['all'] }}
            </span>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" 
            class="px-3.5 py-2 rounded-xl flex items-center gap-2 whitespace-nowrap transition-colors {{ $currentStatus === 'pending' ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <span>Menunggu Pembayaran</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentStatus === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                {{ $statusCounts['pending'] }}
            </span>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" 
            class="px-3.5 py-2 rounded-xl flex items-center gap-2 whitespace-nowrap transition-colors {{ $currentStatus === 'processing' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <span>Perlu Diproses</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentStatus === 'processing' ? 'bg-white/20 text-white' : 'bg-blue-100 text-blue-800' }}">
                {{ $statusCounts['processing'] }}
            </span>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" 
            class="px-3.5 py-2 rounded-xl flex items-center gap-2 whitespace-nowrap transition-colors {{ $currentStatus === 'shipped' ? 'bg-purple-600 text-white shadow-sm shadow-purple-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <span>Sedang Dikirim</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentStatus === 'shipped' ? 'bg-white/20 text-white' : 'bg-purple-100 text-purple-800' }}">
                {{ $statusCounts['shipped'] }}
            </span>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" 
            class="px-3.5 py-2 rounded-xl flex items-center gap-2 whitespace-nowrap transition-colors {{ $currentStatus === 'completed' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <span>Selesai</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentStatus === 'completed' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $statusCounts['completed'] }}
            </span>
        </a>

        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}" 
            class="px-3.5 py-2 rounded-xl flex items-center gap-2 whitespace-nowrap transition-colors {{ $currentStatus === 'cancelled' ? 'bg-rose-600 text-white shadow-sm shadow-rose-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <span>Dibatalkan</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $currentStatus === 'cancelled' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                {{ $statusCounts['cancelled'] }}
            </span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="w-full flex flex-col sm:flex-row items-center gap-3">
            @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif

            <div class="relative w-full sm:w-80">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari No. Pesanan, nama, email, resi..." 
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select name="payment_status" class="w-full sm:w-48 text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <option value="">Semua Status Bayar</option>
                <option value="settlement" {{ $paymentStatus === 'settlement' ? 'selected' : '' }}>Berhasil (Settlement)</option>
                <option value="pending" {{ $paymentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="cancel" {{ $paymentStatus === 'cancel' ? 'selected' : '' }}>Dibatalkan</option>
                <option value="expire" {{ $paymentStatus === 'expire' ? 'selected' : '' }}>Kedaluwarsa</option>
            </select>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors">
                    Terapkan
                </button>
                @if($search || $paymentStatus || $currentStatus)
                    <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-colors">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-12 text-center text-slate-500">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="text-sm font-bold text-slate-800">Tidak ada pesanan ditemukan</h3>
                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan filter status atau kata kunci pencarian Anda.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4">No. Pesanan & Waktu</th>
                            <th class="py-3.5 px-4">Pelanggan</th>
                            <th class="py-3.5 px-4">Kurir & Resi</th>
                            <th class="py-3.5 px-4">Total Tagihan</th>
                            <th class="py-3.5 px-4">Status Pesanan</th>
                            <th class="py-3.5 px-4">Pembayaran</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($orders as $order)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-blue-600">
                                    <a href="{{ route('admin.orders.show', $order->order_number) }}" class="hover:underline">
                                        {{ $order->order_number }}
                                    </a>
                                    <div class="text-[10px] text-slate-400 font-sans font-normal mt-0.5">
                                        {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }} WIB
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $order->customer_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $order->customer_email }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800 uppercase">
                                        {{ $order->courier_code }} - {{ $order->courier_service }}
                                    </div>
                                    @if($order->tracking_number)
                                        <div class="font-mono text-[11px] text-indigo-600 font-bold mt-0.5 flex items-center gap-1">
                                            <span>Resi:</span>
                                            <span>{{ $order->tracking_number }}</span>
                                        </div>
                                    @else
                                        <span class="inline-block mt-0.5 text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-medium">
                                            Belum ada resi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-900">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $orderBadge = match($order->order_status) {
                                            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            default => 'bg-amber-100 text-amber-800 border-amber-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $orderBadge }}">
                                        {{ ucfirst($order->order_status) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @php
                                        $payClass = match($order->payment_status) {
                                            'settlement', 'capture' => 'text-emerald-700 font-bold',
                                            'pending' => 'text-amber-700 font-semibold',
                                            'cancel', 'expire', 'deny' => 'text-rose-700 font-semibold',
                                            default => 'text-slate-500',
                                        };
                                    @endphp
                                    <div class="text-[11px] {{ $payClass }}">
                                        {{ $order->payment_status ? ucfirst($order->payment_status) : 'Menunggu' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 capitalize">{{ $order->payment_type ? str_replace('_', ' ', $order->payment_type) : '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order->order_number) }}" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-semibold shadow-sm transition-colors">
                                        <span>Kelola</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-slate-100">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

