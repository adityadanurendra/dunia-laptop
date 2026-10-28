@extends('layouts.storefront')

@section('title', 'Keranjang Belanja')
@section('meta_description', 'Kelola produk laptop pilihan Anda di keranjang belanja Dunia Laptop.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-store-primary">Beranda</a>
        <span>/</span>
        <span class="text-slate-900 font-semibold">Keranjang Belanja</span>
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

    @if(session('error') || $errors->any())
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 flex items-start gap-3 text-sm text-rose-800">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="font-bold">Terjadi kendala pada keranjang belanja:</p>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs">
                    @if(session('error'))
                        <li>{{ session('error') }}</li>
                    @endif
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($cart->items->isEmpty())
        {{-- Empty Cart State --}}
        <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-16 text-center max-w-2xl mx-auto shadow-sm">
            <div class="w-20 h-20 rounded-3xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Keranjang Belanja Anda Kosong</h1>
            <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">
                Belum ada produk laptop yang Anda pilih. Jelajahi katalog laptop baru dan bekas bergaransi kami sekarang!
            </p>
            <div class="mt-8">
                <a
                    href="{{ route('catalog.index') }}"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-store-primary hover:opacity-90 text-white font-extrabold text-sm shadow-md transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>Mulai Cari Laptop</span>
                </a>
            </div>
        </div>
    @else
        {{-- Active Cart Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Left Side: Items List --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Header info --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                    <div>
                        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Keranjang Belanja</h1>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $cart->total_items_count }} unit laptop dalam keranjang</p>
                    </div>

                    <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Kosongkan Keranjang</span>
                        </button>
                    </form>
                </div>

                {{-- Stock Issue Warning Banner --}}
                @if($cart->has_stock_issues)
                    <div class="rounded-2xl bg-amber-50 border border-amber-300 p-4 flex items-start gap-3 text-sm text-amber-900">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <p class="font-bold">Perubahan Ketersediaan Stok Terdeteksi!</p>
                            <p class="text-xs text-amber-800 mt-0.5">
                                Beberapa unit laptop di keranjang Anda telah habis atau kuantitasnya melebihi sisa stok aktual. Harap sesuaikan kuantitas atau hapus item bertanda merah sebelum melanjutkan ke Checkout.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Cart Items Cards --}}
                <div class="space-y-4">
                    @foreach($cart->items as $item)
                        @php
                            $isUsed = ($item->product_condition ?? 'new') === 'used';
                        @endphp
                        <div class="bg-white rounded-2xl border {{ !$item->is_valid ? 'border-rose-300 ring-1 ring-rose-300/50' : 'border-slate-200' }} p-4 sm:p-5 shadow-sm transition">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                {{-- Thumbnail --}}
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden shrink-0 flex items-center justify-center">
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
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        @if($isUsed)
                                            <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase">Bekas</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase">Baru (BNIB)</span>
                                        @endif
                                        <span class="text-xs font-semibold text-slate-500">{{ $item->brand_name }} &bull; SKU: {{ $item->product_sku }}</span>
                                    </div>

                                    <h3 class="text-sm sm:text-base font-bold text-slate-900 truncate">
                                        <a href="{{ route('catalog.show', $item->product_slug) }}" class="hover:text-store-primary transition">
                                            {{ $item->product_name }}
                                        </a>
                                    </h3>

                                    <p class="text-xs sm:text-sm font-extrabold text-store-primary mt-1">
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                        <span class="text-[11px] font-normal text-slate-400">/ unit</span>
                                    </p>

                                    {{-- Real-time stock status --}}
                                    <div class="mt-2 text-xs">
                                        @if($item->is_out_of_stock)
                                            <span class="inline-flex items-center gap-1 font-bold text-rose-600">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                Stok Habis / Unit Tidak Aktif
                                            </span>
                                        @elseif($item->is_stock_exceeded)
                                            <span class="inline-flex items-center gap-1 font-bold text-amber-600">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                Sisa stok hanya {{ $item->max_available }} unit!
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-slate-500">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Tersedia: {{ $item->max_available }} unit
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Quantity & Line Total & Delete Action --}}
                                <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                                    {{-- Subtotal --}}
                                    <div class="text-left sm:text-right">
                                        <span class="block text-[10px] font-bold uppercase text-slate-400">Subtotal:</span>
                                        <span class="text-sm sm:text-base font-extrabold text-slate-900">
                                            Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    {{-- Quantity Selector Form --}}
                                    <form action="{{ route('cart.update', $item->item_id) }}" method="POST" class="inline-flex items-center rounded-xl border border-slate-300 bg-slate-50">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            name="quantity"
                                            value="{{ max(0, $item->quantity - 1) }}"
                                            class="px-2.5 py-1 text-slate-600 hover:text-slate-900 font-bold text-sm"
                                            title="Kurangi"
                                        >-</button>
                                        <input
                                            type="number"
                                            name="quantity"
                                            value="{{ $item->quantity }}"
                                            min="1"
                                            max="{{ max(1, $item->max_available) }}"
                                            onchange="this.form.submit()"
                                            class="w-12 border-0 bg-transparent text-center text-xs sm:text-sm font-bold text-slate-900 focus:ring-0 p-0"
                                        >
                                        <button
                                            type="submit"
                                            name="quantity"
                                            value="{{ $item->quantity + 1 }}"
                                            {{ $item->quantity >= $item->max_available ? 'disabled' : '' }}
                                            class="px-2.5 py-1 text-slate-600 hover:text-slate-900 font-bold text-sm disabled:opacity-30 disabled:cursor-not-allowed"
                                            title="Tambah"
                                        >+</button>
                                    </form>

                                    {{-- Delete Button --}}
                                    <form action="{{ route('cart.destroy', $item->item_id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-slate-400 hover:text-rose-600 text-xs flex items-center gap-1 transition" title="Hapus item">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            <span class="sm:hidden">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="pt-2 flex items-center justify-between">
                    <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-store-primary hover:underline">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Lanjutkan Belanja Laptop</span>
                    </a>
                </div>
            </div>

            {{-- Right Side: Order Summary Sidebar (Sticky) --}}
            <div class="lg:col-span-4 lg:sticky lg:top-28 space-y-5">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-5">
                    <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3">Ringkasan Pesanan</h2>

                    <div class="space-y-3 text-sm">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Total Item</span>
                            <span class="font-bold text-slate-900">{{ $cart->total_items_count }} unit</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Perkiraan Berat</span>
                            <span class="font-semibold text-slate-700">{{ number_format($cart->total_weight_grams / 1000, 2, ',', '.') }} kg</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal Produk</span>
                            <span class="text-base font-extrabold text-slate-900">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 border border-slate-200/80 p-3.5 text-xs text-slate-500 space-y-1.5">
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Termasuk bubble wrap tebal &amp; packing kayu aman.</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Biaya pengiriman kurir akan dihitung di halaman Checkout.</span>
                        </div>
                    </div>

                    {{-- Checkout CTA --}}
                    <div>
                        @if($cart->can_checkout)
                            <a
                                href="{{ route('checkout.index') }}"
                                class="w-full py-4 px-6 rounded-2xl bg-store-primary hover:opacity-95 text-white font-extrabold text-sm shadow-md flex items-center justify-center gap-2 transition"
                            >
                                <span>Lanjut ke Pembayaran</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @else
                            <button
                                type="button"
                                disabled
                                class="w-full py-4 px-6 rounded-2xl bg-slate-200 text-slate-400 font-extrabold text-sm cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <span>Lanjut ke Pembayaran</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </button>
                            <p class="text-[11px] text-center text-rose-600 font-semibold mt-2">
                                Harap perbaiki atau hapus produk yang stoknya habis/melebihi batas sebelum checkout.
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Guarantee Card --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-xs text-slate-600 space-y-2">
                    <p class="font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-store-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Jaminan Transaksi Aman &amp; Bergaransi
                    </p>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Setiap laptop melalui proses pengujian 28 titik uji fungsi sebelum dikirim dan dilindungi garansi resmi toko.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

