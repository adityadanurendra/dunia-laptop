@extends('layouts.storefront')

@section('title', 'Rincian Pesanan ' . $order->order_number)
@section('meta_description', 'Rincian dan status pembayaran pesanan laptop Anda di Dunia Laptop.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-14">
    {{-- Status Banner Card --}}
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 shadow-sm text-center">
        @if($order->status === 'processing')
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 animate-in zoom-in-75 duration-300">
                <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="inline-block px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider mb-2">
                Pembayaran Berhasil / Sedang Diproses
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pembayaran Anda Terkonfirmasi!</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-lg mx-auto">
                Terima kasih! Notifikasi pembayaran resmi telah diterima dan diverifikasi. Teknisi kami segera menyiapkan unit laptop dan packing kayu untuk pengiriman.
            </p>
        @elseif($order->status === 'cancelled')
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 animate-in zoom-in-75 duration-300">
                <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <span class="inline-block px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-xs font-black uppercase tracking-wider mb-2">
                Pesanan Dibatalkan
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pesanan Telah Dibatalkan</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-lg mx-auto">
                Batas waktu pembayaran telah habis atau pesanan dibatalkan. Stok unit laptop telah dikembalikan ke katalog toko secara otomatis.
            </p>
        @else
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 animate-in zoom-in-75 duration-300">
                <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="inline-block px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-black uppercase tracking-wider mb-2">
                Menunggu Pembayaran
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pesanan Berhasil Dibuat!</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-lg mx-auto">
                Stok laptop Anda telah berhasil diamankan. Silakan selesaikan pembayaran via QRIS, Virtual Account Bank, atau Kartu Kredit melalui tombol di bawah ini.
            </p>
        @endif

        {{-- Order Identifier Card --}}
        <div class="mt-6 inline-flex flex-col sm:flex-row items-center gap-2 sm:gap-6 px-6 py-3.5 rounded-2xl bg-slate-50 border border-slate-200">
            <div>
                <span class="block text-[10px] font-bold uppercase text-slate-400">Nomor Pesanan</span>
                <span class="text-sm sm:text-base font-black font-mono text-slate-900">{{ $order->order_number }}</span>
            </div>
            <div class="hidden sm:block w-px h-8 bg-slate-200"></div>
            <div>
                <span class="block text-[10px] font-bold uppercase text-slate-400">Waktu Pemesanan</span>
                <span class="text-xs sm:text-sm font-semibold text-slate-700">{{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('d M Y, H:i') }} WIB</span>
            </div>
        </div>
    </div>

    {{-- Details Sections --}}
    <div class="mt-8 grid grid-cols-1 md:grid-cols-12 gap-6">
        {{-- Left: Item breakdown with snapshots --}}
        <div class="md:col-span-7 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">Rincian Unit Laptop</h2>

                <div class="mt-4 space-y-4">
                    @foreach($order->items as $item)
                        @php
                            $specs = $item->specs ?? [];
                            $isUsed = ($specs['condition'] ?? 'new') === 'used';
                        @endphp
                        <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50/60 flex items-start gap-3">
                            <div class="w-14 h-14 rounded-xl bg-white border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    @if($isUsed)
                                        <span class="px-1.5 py-0.2 rounded bg-amber-200 text-amber-900 text-[9px] font-black uppercase">Bekas</span>
                                    @else
                                        <span class="px-1.5 py-0.2 rounded bg-emerald-200 text-emerald-900 text-[9px] font-black uppercase">Baru</span>
                                    @endif
                                    <span class="text-[11px] font-mono text-slate-400">SKU: {{ $item->product_sku }}</span>
                                </div>

                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $item->product_name }}</h3>

                                {{-- Snapshot Specs Highlights --}}
                                @if(!empty($specs['processor']) || !empty($specs['ram_gb']))
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $specs['processor'] ?? '' }} &bull; {{ $specs['ram_gb'] ?? '' }}GB RAM &bull; {{ $specs['storage_capacity'] ?? '' }}
                                    </p>
                                @endif

                                <div class="mt-2 flex items-center justify-between text-xs">
                                    <span class="text-slate-500">{{ $item->quantity }}x @ Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    <span class="font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Shipping destination details --}}
            @if($order->shipment)
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
                    <h2 class="text-base font-extrabold text-slate-900 border-b border-slate-100 pb-3">Informasi Pengiriman</h2>

                    <div class="mt-4 text-xs space-y-2 text-slate-600">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-700">Kurir &amp; Layanan:</span>
                            <span class="font-black text-store-primary uppercase">{{ $order->shipment->courier_code }} - {{ $order->shipment->courier_service }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-700">Penerima:</span>
                            <span class="text-slate-900 font-semibold">{{ $order->shipment->recipient_name }} ({{ $order->shipment->recipient_phone }})</span>
                        </div>
                        <div>
                            <span class="font-bold text-slate-700 block mb-0.5">Alamat Tujuan:</span>
                            <p class="p-3 rounded-xl bg-slate-50 text-slate-700 border border-slate-200/70 leading-relaxed">
                                {{ $order->shipment->destination_address }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right: Payment Breakdown & Snap Action Button --}}
        <div class="md:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
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

                {{-- Payment Actions --}}
                <div class="pt-2 space-y-2.5">
                    @if($order->status === 'pending')
                        <button
                            type="button"
                            id="pay-button"
                            onclick="triggerSnapPayment()"
                            class="w-full py-4 px-5 rounded-2xl bg-store-primary hover:opacity-95 text-white font-extrabold text-sm shadow-md flex items-center justify-center gap-2 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span>Bayar Sekarang (Midtrans Snap)</span>
                        </button>
                        <p class="text-[11px] text-center text-slate-400">
                            Mendukung QRIS (GoPay, OVO, ShopeePay), BCA/Mandiri/BRI VA, dan Kartu Kredit.
                        </p>
                    @elseif($order->status === 'processing')
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 text-center font-bold">
                            Pembayaran Berhasil. Pesanan sedang dipersiapkan teknisi toko.
                        </div>
                    @elseif($order->status === 'cancelled')
                        <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 text-center font-bold">
                            Pesanan ini telah kedaluwarsa atau dibatalkan.
                        </div>
                    @endif

                    <a
                        href="{{ route('home') }}"
                        class="w-full py-3 px-5 rounded-2xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs flex items-center justify-center transition"
                    >
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>

            {{-- Trust info --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-xs text-slate-600 space-y-2">
                <p class="font-bold text-slate-800 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Gerbang Pembayaran Terverifikasi
                </p>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Transaksi diproses secara aman menggunakan enkripsi Midtrans Snap berstandar internasional PCI-DSS Level 1.
                </p>
            </div>
        </div>
    </div>
</div>

@if(!empty($snapToken) && $order->status === 'pending')
    {{-- Midtrans Snap Script --}}
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
                    // Modal ditutup tanpa menyelesaikan pembayaran
                }
            });
        }
    </script>
@endif
@endsection
