@extends('layouts.storefront')

@section('title', 'Riwayat Pesanan Saya')
@section('meta_description', 'Daftar dan status seluruh riwayat pembelian laptop Anda di Dunia Laptop.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-store-primary">Beranda</a>
        <span>/</span>
        <span class="text-slate-900 font-semibold">Riwayat Pesanan</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-slate-200 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Riwayat Pesanan Saya</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pantau status transaksi, pembayaran Midtrans, dan nomor resi pengiriman laptop Anda.</p>
        </div>
        <a
            href="{{ route('catalog.index') }}"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-xs font-bold text-slate-700 shadow-sm transition shrink-0"
        >
            <svg class="w-4 h-4 text-store-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Beli Laptop Baru/Bekas</span>
        </a>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mt-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex items-center gap-3 text-sm text-emerald-800">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mt-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 flex items-start gap-3 text-sm text-rose-800">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold">Kendala pada pesanan:</p>
                <ul class="list-disc list-inside mt-1 text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Status Filter Tabs --}}
    @php
        $tabs = [
            null => ['label' => 'Semua Pesanan', 'count' => $totalCount],
            'pending' => ['label' => 'Menunggu Pembayaran', 'count' => $statusCounts['pending'] ?? 0],
            'processing' => ['label' => 'Diproses', 'count' => $statusCounts['processing'] ?? 0],
            'shipped' => ['label' => 'Dikirim', 'count' => $statusCounts['shipped'] ?? 0],
            'completed' => ['label' => 'Selesai', 'count' => $statusCounts['completed'] ?? 0],
            'cancelled' => ['label' => 'Dibatalkan', 'count' => $statusCounts['cancelled'] ?? 0],
        ];
    @endphp

    <div class="mt-6 flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        @foreach($tabs as $tabKey => $tab)
            @php
                $isActive = $currentStatus === $tabKey;
            @endphp
            <a
                href="{{ route('orders.index', $tabKey ? ['status' => $tabKey] : []) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition whitespace-nowrap select-none {{ $isActive ? 'bg-slate-900 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
            >
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $isActive ? 'bg-slate-800 text-slate-200' : 'bg-slate-100 text-slate-600' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- Order Cards List --}}
    <div class="mt-6 space-y-6">
        @if($orders->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-14 text-center max-w-xl mx-auto shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900">Belum Ada Pesanan</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                    @if($currentStatus)
                        Tidak ditemukan pesanan dengan filter status "{{ $tabs[$currentStatus]['label'] ?? $currentStatus }}".
                    @else
                        Anda belum pernah melakukan pemesanan laptop. Temukan unit impian Anda sekarang!
                    @endif
                </p>
                <div class="mt-6">
                    <a
                        href="{{ route('catalog.index') }}"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-store-primary text-white text-xs font-bold hover:opacity-90 shadow transition"
                    >
                        <span>Mulai Belanja</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        @else
            @foreach($orders as $order)
                @php
                    $isPending = $order->status === 'pending';
                    $isProcessing = $order->status === 'processing';
                    $isShipped = $order->status === 'shipped';
                    $isCompleted = $order->status === 'completed';
                    $isCancelled = $order->status === 'cancelled';
                @endphp
                <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden transition hover:border-slate-300">
                    {{-- Card Header --}}
                    <div class="p-4 sm:p-6 bg-slate-50/80 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex flex-wrap items-center gap-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase text-slate-400 block">Nomor Pesanan</span>
                                <span class="font-black font-mono text-sm text-slate-900">{{ $order->order_number }}</span>
                            </div>
                            <div class="hidden sm:block w-px h-6 bg-slate-300"></div>
                            <div>
                                <span class="text-[10px] font-bold uppercase text-slate-400 block">Tanggal Pemesanan</span>
                                <span class="font-medium text-slate-600">{{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }} WIB</span>
                            </div>
                        </div>

                        {{-- Status Badges --}}
                        <div class="flex flex-wrap items-center gap-2">
                            @if($isPending)
                                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 font-extrabold text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Menunggu Pembayaran
                                </span>
                            @elseif($isProcessing)
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-900 font-extrabold text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                    Diproses Teknisi
                                </span>
                            @elseif($isShipped)
                                <span class="px-3 py-1 rounded-full bg-sky-100 text-sky-900 font-extrabold text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-sky-600"></span>
                                    Dalam Pengiriman
                                </span>
                            @elseif($isCompleted)
                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                    Pesanan Selesai
                                </span>
                            @elseif($isCancelled)
                                <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-800 font-extrabold text-[11px] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                    Dibatalkan
                                </span>
                            @endif

                            @if($order->shipment && !empty($order->shipment->tracking_number))
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono text-[11px] font-bold">
                                    Resi: {{ $order->shipment->tracking_number }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Card Body: Items Preview --}}
                    <div class="p-4 sm:p-6 space-y-4">
                        @foreach($order->items as $item)
                            @php
                                $specs = $item->specs ?? [];
                                $isUsed = ($specs['condition'] ?? 'new') === 'used';
                            @endphp
                            <div class="flex items-center gap-4 text-xs">
                                <div class="w-16 h-16 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        @if($isUsed)
                                            <span class="px-1.5 py-0.2 rounded bg-amber-100 text-amber-900 text-[9px] font-extrabold uppercase">Bekas</span>
                                        @else
                                            <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 text-[9px] font-extrabold uppercase">Baru</span>
                                        @endif
                                        <span class="text-slate-400 font-mono text-[10px]">SKU: {{ $item->product_sku }}</span>
                                    </div>
                                    <h4 class="font-bold text-sm text-slate-900 truncate">{{ $item->product_name }}</h4>
                                    @if(!empty($specs['processor']) || !empty($specs['ram_gb']))
                                        <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">
                                            {{ $specs['processor'] ?? '' }} &bull; {{ $specs['ram_gb'] ?? '' }}GB RAM &bull; {{ $specs['storage_capacity'] ?? '' }}
                                        </p>
                                    @endif
                                    <p class="text-slate-500 mt-1">
                                        {{ $item->quantity }} unit &times; Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </p>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase">Subtotal</span>
                                    <span class="font-extrabold text-sm text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Card Footer: Total & Actions --}}
                    <div class="p-4 sm:p-6 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div class="flex items-baseline gap-2">
                            <span class="text-xs text-slate-500 font-medium">Total Pembayaran (termasuk ongkir):</span>
                            <span class="text-lg font-black text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            @if($isPending)
                                {{-- Cancel Button --}}
                                <form action="{{ route('orders.cancel', $order->order_number) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok produk akan dikembalikan ke toko.')">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="px-3.5 py-2 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition"
                                    >
                                        Batalkan Pesanan
                                    </button>
                                </form>

                                {{-- Pay Button --}}
                                <a
                                    href="{{ route('orders.show', $order->order_number) }}"
                                    class="px-4 py-2 rounded-xl bg-store-primary hover:opacity-90 text-white text-xs font-extrabold shadow transition flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <span>Bayar Sekarang</span>
                                </a>
                            @endif

                            <a
                                href="{{ route('orders.show', $order->order_number) }}"
                                class="px-4 py-2 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-slate-700 text-xs font-bold transition flex items-center gap-1"
                            >
                                <span>Lihat Rincian</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

