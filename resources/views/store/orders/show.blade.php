@extends('layouts.storefront')

@section('title', 'Rincian Pesanan ' . $order->order_number)
@section('meta_description', 'Rincian lengkap unit laptop, pelacakan pengiriman kurir, dan pembayaran pesanan Anda.')

@section('content')
@php
    $isPending = $order->status === 'pending';
    $isProcessing = $order->status === 'processing';
    $isShipped = $order->status === 'shipped';
    $isCompleted = $order->status === 'completed';
    $isCancelled = $order->status === 'cancelled';

    $stepIndex = match ($order->status) {
        'pending' => 1,
        'processing' => 2,
        'shipped' => 3,
        'completed' => 4,
        default => 1,
    };

    $waMessage = rawurlencode("Halo CS Dunia Laptop, saya ingin menanyakan pesanan dengan nomor {$order->order_number}.");
    $waUrl = ($siteSettings['whatsapp_url'] ?? '#') !== '#' ? ($siteSettings['whatsapp_url'] . '?text=' . $waMessage) : '#';
@endphp

<div
    x-data="{
        copiedResi: false,
        showCancelModal: false,
        copyTrackingNumber(text) {
            navigator.clipboard.writeText(text);
            this.copiedResi = true;
            setTimeout(() => { this.copiedResi = false; }, 2500);
        }
    }"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
>
    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-store-primary">Beranda</a>
        <span>/</span>
        <a href="{{ route('orders.index') }}" class="hover:text-store-primary">Riwayat Pesanan</a>
        <span>/</span>
        <span class="text-slate-900 font-semibold font-mono">{{ $order->order_number }}</span>
    </nav>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex items-center gap-3 text-sm text-emerald-800">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 flex items-start gap-3 text-sm text-rose-800">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold">Terjadi kendala pada aksi pesanan:</p>
                <ul class="list-disc list-inside mt-1 text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Order Header & Stepper Card --}}
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-slate-100 gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-xl sm:text-2xl font-black font-mono text-slate-900">{{ $order->order_number }}</h1>
                    @if($isPending)
                        <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-black uppercase">
                            Menunggu Pembayaran
                        </span>
                    @elseif($isProcessing)
                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-900 text-xs font-black uppercase">
                            Diproses Teknisi
                        </span>
                    @elseif($isShipped)
                        <span class="px-3 py-1 rounded-full bg-sky-100 text-sky-900 text-xs font-black uppercase">
                            Dalam Pengiriman Kurir
                        </span>
                    @elseif($isCompleted)
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase">
                            Pesanan Selesai
                        </span>
                    @elseif($isCancelled)
                        <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-black uppercase">
                            Dibatalkan
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Dibuat pada {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d F Y, H:i') }} WIB
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a
                    href="{{ $waUrl }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-800 text-xs font-bold hover:bg-emerald-100 transition"
                >
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>Tanya CS Toko</span>
                </a>
            </div>
        </div>

        {{-- Progress Status Stepper (Active for non-cancelled orders) --}}
        @if(!$isCancelled)
            <div class="mt-8">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 relative">
                    {{-- Step 1 --}}
                    <div class="flex items-center sm:flex-col text-left sm:text-center gap-3 sm:gap-2">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs shrink-0 {{ $stepIndex >= 1 ? 'bg-store-primary text-white ring-4 ring-store-primary/20' : 'bg-slate-100 text-slate-400' }}">
                            1
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Pesanan Dibuat</span>
                            <span class="block text-[10px] text-slate-400">Unit Terkunci</span>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="flex items-center sm:flex-col text-left sm:text-center gap-3 sm:gap-2">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs shrink-0 {{ $stepIndex >= 2 ? 'bg-store-primary text-white ring-4 ring-store-primary/20' : 'bg-slate-100 text-slate-400' }}">
                            2
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Pembayaran Terverifikasi</span>
                            <span class="block text-[10px] text-slate-400">QC &amp; Packing Kayu</span>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="flex items-center sm:flex-col text-left sm:text-center gap-3 sm:gap-2">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs shrink-0 {{ $stepIndex >= 3 ? 'bg-store-primary text-white ring-4 ring-store-primary/20' : 'bg-slate-100 text-slate-400' }}">
                            3
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Dalam Pengiriman</span>
                            <span class="block text-[10px] text-slate-400">Resi Tersedia</span>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="flex items-center sm:flex-col text-left sm:text-center gap-3 sm:gap-2">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs shrink-0 {{ $stepIndex >= 4 ? 'bg-emerald-600 text-white ring-4 ring-emerald-600/20' : 'bg-slate-100 text-slate-400' }}">
                            4
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-slate-900">Pesanan Selesai</span>
                            <span class="block text-[10px] text-slate-400">Garansi Aktif</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="mt-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-xs text-rose-800">
                <p class="font-bold">Pesanan ini telah dibatalkan.</p>
                <p class="mt-0.5 text-rose-700">Unit laptop telah dikembalikan ke stok katalog toko dan tidak lagi dikunci untuk transaksi ini.</p>
            </div>
        @endif
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        {{-- Left Column: Laptop Items, Shipping & Tracking, Notes --}}
        <div class="lg:col-span-8 space-y-6">
            {{-- Laptop Items with Snapshot Specs --}}
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-4">
                    Rincian Unit Laptop Terbeli
                </h2>

                <div class="mt-4 divide-y divide-slate-100">
                    @foreach($order->items as $item)
                        @php
                            $specs = $item->specs ?? [];
                            $isUsed = ($specs['condition'] ?? 'new') === 'used';
                        @endphp
                        <div class="py-5 first:pt-0 last:pb-0 flex flex-col sm:flex-row items-start gap-4">
                            {{-- Image --}}
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    @if($isUsed)
                                        <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase">Bekas Berkualitas</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">Baru (BNIB)</span>
                                    @endif
                                    <span class="text-slate-400 font-mono text-xs">SKU: {{ $item->product_sku }}</span>
                                </div>

                                <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ $item->product_name }}</h3>

                                {{-- Full Snapshot Specifications Table --}}
                                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2 text-[11px] p-3 rounded-xl bg-slate-50 border border-slate-100 text-slate-600">
                                    @if(!empty($specs['processor']))
                                        <div><span class="text-slate-400 block font-semibold">Processor:</span> {{ $specs['processor'] }}</div>
                                    @endif
                                    @if(!empty($specs['ram_gb']))
                                        <div><span class="text-slate-400 block font-semibold">RAM:</span> {{ $specs['ram_gb'] }}GB {{ $specs['ram_type'] ?? '' }}</div>
                                    @endif
                                    @if(!empty($specs['storage_capacity']))
                                        <div><span class="text-slate-400 block font-semibold">Storage:</span> {{ $specs['storage_capacity'] }} {{ $specs['storage_type'] ?? '' }}</div>
                                    @endif
                                    @if(!empty($specs['gpu']))
                                        <div><span class="text-slate-400 block font-semibold">Kartu Grafis:</span> {{ $specs['gpu'] }}</div>
                                    @endif
                                    @if(!empty($specs['screen_size']))
                                        <div><span class="text-slate-400 block font-semibold">Layar:</span> {{ $specs['screen_size'] }} Inci {{ $specs['screen_resolution'] ?? '' }}</div>
                                    @endif
                                    @if(!empty($specs['warranty']))
                                        <div><span class="text-slate-400 block font-semibold">Garansi:</span> {{ $specs['warranty'] }}</div>
                                    @endif
                                    @if($isUsed && !empty($specs['physical_condition']))
                                        <div class="col-span-2 sm:col-span-3 text-amber-800"><span class="text-amber-700 font-semibold">Kondisi Fisik Unit:</span> {{ $specs['physical_condition'] }}</div>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center justify-between text-xs">
                                    <span class="text-slate-500">{{ $item->quantity }} unit &times; Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    <span class="font-extrabold text-sm text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Shipping and Tracking Card (FR-28) --}}
            @if($order->shipment)
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
                    <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-4 flex items-center justify-between">
                        <span>Pengiriman &amp; Ekspedisi</span>
                        <span class="text-xs font-mono uppercase px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700">
                            {{ $order->shipment->courier_code }} - {{ $order->shipment->courier_service }}
                        </span>
                    </h2>

                    {{-- Resi Highlight Banner --}}
                    @if(!empty($order->shipment->tracking_number))
                        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block">Nomor Resi Pengiriman</span>
                                <span class="text-base sm:text-lg font-black font-mono text-emerald-950">{{ $order->shipment->tracking_number }}</span>
                            </div>
                            <button
                                type="button"
                                @click="copyTrackingNumber('{{ $order->shipment->tracking_number }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-emerald-300 text-emerald-800 text-xs font-bold hover:bg-emerald-100 transition shadow-sm w-fit"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="copiedResi ? 'Tersalin!' : 'Salin Nomor Resi'"></span>
                            </button>
                        </div>
                    @else
                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4 text-xs text-slate-600 flex items-center gap-3">
                            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Nomor resi akan muncul di sini setelah paket laptop diserahkan ke kurir ekspedisi.</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-slate-600 pt-2">
                        <div>
                            <span class="text-slate-400 block font-semibold">Nama Penerima:</span>
                            <span class="font-bold text-slate-900">{{ $order->shipment->recipient_name }}</span>
                            <span class="block text-slate-500 font-mono">{{ $order->shipment->recipient_phone }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-semibold">Alamat Lengkap Pengiriman:</span>
                            <span class="text-slate-700 leading-relaxed block">{{ $order->shipment->destination_address }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Notes --}}
            @if(!empty($order->customer_notes))
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-xs font-bold uppercase text-slate-400 mb-2">Catatan Pesanan:</h3>
                    <p class="text-xs text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-100 leading-relaxed">
                        {{ $order->customer_notes }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Right Column: Payment Details & Re-Pay / Cancel Action (Sticky) --}}
        <div class="lg:col-span-4 lg:sticky lg:top-28 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-5">
                <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">Rincian Pembayaran</h2>

                <div class="space-y-2.5 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Subtotal Produk</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Biaya Pengiriman</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Diskon</span>
                            <span class="font-bold text-emerald-600">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="border-t border-slate-200 pt-3 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-900">Total Tagihan</span>
                        <span class="text-lg font-black text-store-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Payment Gateway & Status (FR-30) --}}
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Metode Pembayaran:</span>
                        <span class="font-bold text-slate-800 uppercase">{{ $order->payment->payment_type ?? 'Midtrans Snap' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Status Pembayaran:</span>
                        @if($isPending)
                            <span class="font-bold text-amber-600">Menunggu Pembayaran</span>
                        @elseif($isProcessing || $isShipped || $isCompleted)
                            <span class="font-bold text-emerald-600">Lunas (Settlement)</span>
                        @elseif($isCancelled)
                            <span class="font-bold text-rose-600">Dibatalkan</span>
                        @endif
                    </div>
                    @if(!empty($order->payment?->settlement_time))
                        <div class="flex items-center justify-between text-[11px] text-slate-400">
                            <span>Waktu Pelunasan:</span>
                            <span>{{ \Carbon\Carbon::parse($order->payment->settlement_time)->format('d/m/Y H:i') }} WIB</span>
                        </div>
                    @endif
                </div>

                {{-- Action Buttons --}}
                <div class="pt-2 space-y-2.5">
                    @if($isPending)
                        {{-- Re-Pay Button with Snap Modal --}}
                        @if(!empty($snapToken))
                            <button
                                type="button"
                                onclick="triggerSnapPayment()"
                                class="w-full py-3.5 px-5 rounded-2xl bg-store-primary hover:opacity-95 text-white font-extrabold text-sm shadow-md flex items-center justify-center gap-2 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span>Bayar Sekarang (Midtrans)</span>
                            </button>
                        @endif

                        {{-- Cancel Order Button (FR-31) --}}
                        <button
                            type="button"
                            @click="showCancelModal = true"
                            class="w-full py-2.5 px-4 rounded-xl border border-rose-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition flex items-center justify-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span>Batalkan Pesanan Ini</span>
                        </button>
                    @endif

                    <a
                        href="{{ route('orders.index') }}"
                        class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center justify-center transition"
                    >
                        Kembali ke Riwayat Pesanan
                    </a>
                </div>
            </div>

            {{-- Trust & Warranty Card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-xs text-slate-600 space-y-2">
                <p class="font-bold text-slate-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Garansi Resmi &amp; Toko Dunia Laptop
                </p>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Setiap unit laptop yang dibeli telah melalui 28 titik uji fungsi dan dilindungi jaminan garansi toko serta perlindungan packing kayu gratis.
                </p>
            </div>
        </div>
    </div>

    {{-- Cancel Order Confirmation Modal (FR-31) --}}
    <div
        x-cloak
        x-show="showCancelModal"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <div
            @click.outside="showCancelModal = false"
            class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-200"
        >
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <h3 class="text-base font-extrabold text-slate-900 text-center">Konfirmasi Batalkan Pesanan?</h3>
            <p class="text-xs text-slate-500 text-center mt-1">
                Apakah Anda yakin ingin membatalkan pesanan <strong>{{ $order->order_number }}</strong>? Stok produk laptop akan langsung dikembalikan ke katalog toko.
            </p>

            <form action="{{ route('orders.cancel', $order->order_number) }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembatalan (Opsional):</label>
                    <input
                        type="text"
                        name="reason"
                        placeholder="Contoh: Ingin ganti model laptop / salah pilih kurir"
                        class="w-full text-xs rounded-xl border-slate-300 focus:border-store-primary focus:ring-store-primary"
                    >
                </div>

                <div class="pt-2 flex items-center justify-end gap-2">
                    <button
                        type="button"
                        @click="showCancelModal = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
                    >
                        Kembali
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-sm"
                    >
                        Ya, Batalkan Pesanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(!empty($snapToken) && $isPending)
    {{-- Midtrans Snap Integration for Re-Payment --}}
    <script src="{{ $snapJsUrl }}" data-client-key="{{ $clientKey }}"></script>
    <script>
        function triggerSnapPayment() {
            if (typeof window.snap === 'undefined') {
                alert('Sistem pembayaran Midtrans sedang memuat. Silakan tunggu sesaat atau muat ulang halaman.');
                return;
            }

            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    window.location.href = "{{ route('payment.finish') }}?order_id={{ $order->order_number }}";
                },
                onPending: function(result) {
                    window.location.href = "{{ route('payment.unfinish') }}?order_id={{ $order->order_number }}";
                },
                onError: function(result) {
                    window.location.href = "{{ route('payment.error') }}?order_id={{ $order->order_number }}";
                },
                onClose: function() {
                    // Dialog ditutup pelanggan
                }
            });
        }
    </script>
@endif
@endsection

