@extends('layouts.storefront')

@php
    $isUsed = ($product->condition ?? 'new') === 'used';
    $stock = (int) ($product->stock ?? 0);
    $inStock = $stock > 0;
    $formattedPrice = 'Rp ' . number_format((float) $product->price, 0, ',', '.');
    $weightKg = number_format(((int) ($product->weight_grams ?? 1500)) / 1000, 2, ',', '.');

    $galleryItems = [];
    if (isset($product->images) && count($product->images) > 0) {
        foreach ($product->images as $idx => $imgObj) {
            $galleryItems[] = [
                'url' => \App\Services\Store\StorefrontDataService::resolveImageUrl($imgObj->image_path ?? null),
                'path' => $imgObj->image_path ?? '',
                'label' => $imgObj->is_primary ? 'Tampak Utama' : 'Sudut #' . ($idx + 1),
            ];
        }
    } else {
        $galleryItems[] = [
            'url' => \App\Services\Store\StorefrontDataService::resolveImageUrl($product->primary_image ?? null),
            'path' => $product->primary_image ?? '',
            'label' => 'Tampak Utama',
        ];
    }

    $waAskText = rawurlencode("Halo Admin Dunia Laptop, saya ingin menanyakan ketersediaan unit {$product->name} (SKU: {$product->sku}).");
    $waBaseUrl = $siteSettings['whatsapp_url'] ?? '#';
    $waProductUrl = $waBaseUrl !== '#' ? "{$waBaseUrl}?text={$waAskText}" : '#';
@endphp

@section('title', $product->name)
@section('meta_description', Str::limit(strip_tags((string) $product->description), 155))

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
        {{-- Breadcrumbs --}}
        <nav class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-store-primary">Beranda</a>
            <span>/</span>
            <a href="{{ route('catalog.index') }}" class="hover:text-store-primary">Katalog</a>
            @if(!empty($product->category_slug))
                <span>/</span>
                <a href="{{ route('catalog.category', $product->category_slug) }}" class="hover:text-store-primary">
                    {{ $product->category_name }}
                </a>
            @endif
            <span>/</span>
            <span class="text-slate-900 font-semibold truncate max-w-xs">{{ $product->name }}</span>
        </nav>

        {{-- Top Main Product Card --}}
        <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-8 lg:p-10 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                {{-- Left Column: Multi-Angle Photo Gallery --}}
                <div
                    x-data="{
                        activeIdx: 0,
                        images: @js($galleryItems)
                    }"
                    class="lg:col-span-6 space-y-4"
                >
                    {{-- Main Preview Frame --}}
                    <div class="relative aspect-[4/3] rounded-2xl bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200/70 border border-slate-200/80 overflow-hidden flex items-center justify-center">
                        <template x-if="images[activeIdx] && images[activeIdx].url">
                            <img
                                :src="images[activeIdx].url"
                                alt="{{ $product->name }}"
                                class="w-full h-full object-cover"
                            >
                        </template>

                        <template x-if="!images[activeIdx] || !images[activeIdx].url">
                            <div class="text-center p-8">
                                <svg class="w-28 h-28 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-3 text-sm font-bold text-slate-500">{{ $product->brand_name }} &bull; <span x-text="images[activeIdx]?.label || 'Galeri'"></span></p>
                                <p class="text-xs text-slate-400 font-mono mt-1" x-text="images[activeIdx]?.path || ''"></p>
                            </div>
                        </template>

                        {{-- Condition Floating Badge --}}
                        <div class="absolute top-4 left-4 flex items-center gap-2">
                            @if($isUsed)
                                <span class="px-3 py-1.5 rounded-xl bg-amber-500 text-slate-950 text-xs font-extrabold uppercase tracking-wide shadow">
                                    Unit Bekas Berkualitas
                                </span>
                            @else
                                <span class="px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-extrabold uppercase tracking-wide shadow">
                                    100% Baru (BNIB)
                                </span>
                            @endif
                        </div>

                        @if(!$inStock)
                            <div class="absolute bottom-4 right-4">
                                <span class="px-3.5 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg">
                                    Stok Habis
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Multi-Angle Thumbnails Strip --}}
                    @if(count($galleryItems) > 1)
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-3">
                            @foreach($galleryItems as $idx => $thumb)
                                <button
                                    type="button"
                                    @click="activeIdx = {{ $idx }}"
                                    :class="activeIdx === {{ $idx }} ? 'ring-2 ring-store-primary border-store-primary bg-store-primary/5' : 'border-slate-200 hover:border-slate-300 bg-slate-50'"
                                    class="aspect-[4/3] rounded-xl border overflow-hidden flex flex-col items-center justify-center p-1.5 transition"
                                >
                                    @if(!empty($thumb['url']))
                                        <img src="{{ $thumb['url'] }}" alt="{{ $thumb['label'] }}" class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="mt-1 text-[10px] font-semibold text-slate-600 truncate w-full text-center">
                                            {{ $thumb['label'] }}
                                        </span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Right Column: Product Summary, Used Condition Details, & Purchase Actions --}}
                <div class="lg:col-span-6 flex flex-col">
                    <div class="flex flex-wrap items-center gap-2 text-xs font-bold mb-2">
                        <a href="{{ route('catalog.index', ['brand' => $product->brand_slug]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-800 hover:bg-slate-200 transition">
                            {{ $product->brand_name }}
                        </a>
                        <a href="{{ route('catalog.category', $product->category_slug) }}" class="px-2.5 py-1 rounded-lg bg-store-primary/10 text-store-primary hover:bg-store-primary/20 transition">
                            {{ $product->category_name }}
                        </a>
                        <span class="font-mono text-slate-400 ml-auto">SKU: {{ $product->sku }}</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        {{ $product->name }}
                    </h1>

                    {{-- Price & Stock Status Row --}}
                    <div class="mt-4 pb-5 border-b border-slate-100 flex flex-wrap items-baseline justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Harga Terbaik</p>
                            <p class="text-3xl sm:text-4xl font-extrabold text-store-primary tracking-tight mt-0.5">
                                {{ $formattedPrice }}
                            </p>
                        </div>

                        <div>
                            @if($stock > 5)
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-extrabold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Stok Tersedia ({{ $stock }} unit)
                                </span>
                            @elseif($stock > 0)
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-extrabold">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Stok Terbatas (Sisa {{ $stock }} unit)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-extrabold">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    Stok Habis
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Highlighted Physical & Battery Condition Card for Used Laptops --}}
                    @if($isUsed)
                        <div class="mt-5 rounded-2xl bg-amber-50/90 border border-amber-200 p-4 space-y-2.5">
                            <div class="flex items-center gap-2 text-amber-900 font-extrabold text-sm">
                                <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                <span>Rincian Inspeksi Kondisi Laptop Bekas</span>
                            </div>
                            <dl class="grid grid-cols-1 gap-2 text-xs sm:text-sm">
                                @if(!empty($product->physical_condition))
                                    <div class="flex flex-col sm:flex-row sm:gap-2">
                                        <dt class="font-bold text-amber-900 sm:w-36 shrink-0">Kondisi Fisik:</dt>
                                        <dd class="text-amber-950">{{ $product->physical_condition }}</dd>
                                    </div>
                                @endif
                                @if(!empty($product->battery_condition))
                                    <div class="flex flex-col sm:flex-row sm:gap-2">
                                        <dt class="font-bold text-amber-900 sm:w-36 shrink-0">Kesehatan Baterai:</dt>
                                        <dd class="text-amber-950">{{ $product->battery_condition }}</dd>
                                    </div>
                                @endif
                                @if(!empty($product->warranty))
                                    <div class="flex flex-col sm:flex-row sm:gap-2">
                                        <dt class="font-bold text-amber-900 sm:w-36 shrink-0">Garansi Unit:</dt>
                                        <dd class="text-amber-950 font-semibold">{{ $product->warranty }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @elseif(!empty($product->warranty))
                        <div class="mt-5 rounded-2xl bg-emerald-50/70 border border-emerald-200 px-4 py-3 flex items-center gap-3 text-xs sm:text-sm text-emerald-900">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span><strong>Jaminan Garansi:</strong> {{ $product->warranty }}</span>
                        </div>
                    @endif

                    {{-- Key Hardware Highlights --}}
                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3">
                            <p class="text-[11px] font-bold uppercase text-slate-400">Processor</p>
                            <p class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-1 mt-0.5">{{ $product->processor ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3">
                            <p class="text-[11px] font-bold uppercase text-slate-400">Memori &amp; Storage</p>
                            <p class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-1 mt-0.5">
                                {{ $product->ram_gb ? $product->ram_gb . 'GB RAM' : '-' }} &bull; {{ $product->storage_capacity ?? '-' }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3">
                            <p class="text-[11px] font-bold uppercase text-slate-400">Kartu Grafis (GPU)</p>
                            <p class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-1 mt-0.5">{{ $product->gpu ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 border border-slate-200/70 p-3">
                            <p class="text-[11px] font-bold uppercase text-slate-400">Layar</p>
                            <p class="text-xs sm:text-sm font-bold text-slate-800 line-clamp-1 mt-0.5">
                                {{ $product->screen_size ? rtrim(rtrim((string) $product->screen_size, '0'), '.') . ' Inci' : '-' }} {{ $product->screen_resolution ?? '' }}
                            </p>
                        </div>
                    </div>

                    {{-- Quantity Selector & Action Buttons --}}
                    <div
                        x-data="{
                            qty: 1,
                            maxStock: {{ max(1, $stock) }},
                            decrement() { if (this.qty > 1) this.qty--; },
                            increment() { if (this.qty < this.maxStock) this.qty++; }
                        }"
                        class="mt-6 pt-6 border-t border-slate-100 space-y-4"
                    >
                        @if(Route::has('cart.store'))
                            <form action="{{ route('cart.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <div class="flex items-center gap-4">
                                    <span class="text-sm font-bold text-slate-700">Kuantitas:</span>
                                    <div class="inline-flex items-center rounded-xl border border-slate-300 bg-white">
                                        <button type="button" @click="decrement()" {{ !$inStock ? 'disabled' : '' }} class="px-3.5 py-2 text-slate-600 hover:text-slate-900 disabled:opacity-40 font-bold">-</button>
                                        <input
                                            type="number"
                                            name="quantity"
                                            x-model.number="qty"
                                            min="1"
                                            max="{{ max(1, $stock) }}"
                                            {{ !$inStock ? 'disabled' : '' }}
                                            class="w-14 border-0 text-center text-sm font-bold text-slate-900 focus:ring-0"
                                        >
                                        <button type="button" @click="increment()" {{ !$inStock ? 'disabled' : '' }} class="px-3.5 py-2 text-slate-600 hover:text-slate-900 disabled:opacity-40 font-bold">+</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button
                                        type="submit"
                                        name="buy_now"
                                        value="0"
                                        {{ !$inStock ? 'disabled' : '' }}
                                        class="w-full py-3.5 px-5 rounded-xl border-2 border-store-primary text-store-primary hover:bg-store-primary/5 font-extrabold text-sm flex items-center justify-center gap-2 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span>Tambah ke Keranjang</span>
                                    </button>

                                    <button
                                        type="submit"
                                        name="buy_now"
                                        value="1"
                                        {{ !$inStock ? 'disabled' : '' }}
                                        class="w-full py-3.5 px-5 rounded-xl bg-store-primary hover:opacity-95 text-white font-extrabold text-sm shadow-md flex items-center justify-center gap-2 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                    >
                                        <span>Beli Sekarang</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-bold text-slate-700">Kuantitas:</span>
                                <div class="inline-flex items-center rounded-xl border border-slate-300 bg-white">
                                    <button type="button" @click="decrement()" {{ !$inStock ? 'disabled' : '' }} class="px-3.5 py-2 text-slate-600 hover:text-slate-900 disabled:opacity-40 font-bold">-</button>
                                    <input
                                        type="number"
                                        x-model.number="qty"
                                        min="1"
                                        max="{{ max(1, $stock) }}"
                                        {{ !$inStock ? 'disabled' : '' }}
                                        aria-label="Kuantitas"
                                        class="w-14 border-0 text-center text-sm font-bold text-slate-900 focus:ring-0"
                                    >
                                    <button type="button" @click="increment()" {{ !$inStock ? 'disabled' : '' }} class="px-3.5 py-2 text-slate-600 hover:text-slate-900 disabled:opacity-40 font-bold">+</button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    {{ !$inStock ? 'disabled' : '' }}
                                    @click="$dispatch('store-toast', { message: qty + ' unit {{ addslashes($product->name) }} siap ditambahkan ke keranjang (modul Checkout aktif di Fase 4).' })"
                                    class="w-full py-3.5 px-5 rounded-xl border-2 border-store-primary text-store-primary hover:bg-store-primary/5 font-extrabold text-sm flex items-center justify-center gap-2 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span>Tambah ke Keranjang</span>
                                </button>

                                <button
                                    type="button"
                                    {{ !$inStock ? 'disabled' : '' }}
                                    @click="$dispatch('store-toast', { message: 'Melanjutkan pembelian langsung untuk ' + qty + ' unit (modul Checkout aktif di Fase 4).' })"
                                    class="w-full py-3.5 px-5 rounded-xl bg-store-primary hover:opacity-95 text-white font-extrabold text-sm shadow-md flex items-center justify-center gap-2 transition disabled:opacity-40 disabled:cursor-not-allowed"
                                >
                                    <span>Beli Sekarang</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </button>
                            </div>
                        @endif

                        @if($waProductUrl !== '#')
                            <a
                                href="{{ $waProductUrl }}"
                                target="_blank"
                                rel="noopener"
                                class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center gap-2 transition"
                            >
                                <span>Butuh foto/video fisik detail? Konsultasi Unit via WhatsApp</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Product Description & Full Technical Specifications Table --}}
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Description --}}
            <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm h-fit">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-4">Deskripsi Produk</h2>
                <div class="prose prose-sm prose-slate max-w-none text-slate-600 leading-relaxed">
                    {!! nl2br(e($product->description ?? 'Belum ada deskripsi tambahan untuk produk ini.')) !!}
                </div>
            </div>

            {{-- Full Specifications Table --}}
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900 mb-5">Tabel Spesifikasi Lengkap</h2>

                <div class="overflow-hidden rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <tbody class="divide-y divide-slate-200">
                            <tr class="bg-slate-50/70">
                                <th scope="row" class="w-1/3 px-4 py-3.5 text-left font-bold text-slate-700">Processor (CPU)</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">{{ $product->processor ?? '-' }}</td>
                            </tr>
                            <tr class="bg-white">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Memori (RAM)</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">
                                    {{ $product->ram_gb ? $product->ram_gb . ' GB' : '-' }}
                                    @if(!empty($product->ram_type))
                                        <span class="text-slate-500">({{ $product->ram_type }})</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="bg-slate-50/70">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Penyimpanan (Storage)</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">
                                    {{ $product->storage_capacity ?? '-' }}
                                    @if(!empty($product->storage_type))
                                        <span class="text-slate-500">{{ $product->storage_type }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="bg-white">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Kartu Grafis (GPU)</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">{{ $product->gpu ?? '-' }}</td>
                            </tr>
                            <tr class="bg-slate-50/70">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Layar (Display)</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">
                                    {{ $product->screen_size ? rtrim(rtrim((string) $product->screen_size, '0'), '.') . ' Inci' : '-' }}
                                    @if(!empty($product->screen_resolution))
                                        — {{ $product->screen_resolution }}
                                    @endif
                                </td>
                            </tr>
                            <tr class="bg-white">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Kondisi Unit</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold {{ $isUsed ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $isUsed ? 'Bekas' : 'Baru' }}
                                    </span>
                                    @if(!empty($product->physical_condition))
                                        <span class="ml-1 text-slate-700">— {{ $product->physical_condition }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="bg-slate-50/70">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Baterai</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">{{ $product->battery_condition ?? '-' }}</td>
                            </tr>
                            <tr class="bg-white">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Sistem Operasi (OS)</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">{{ $product->operating_system ?? '-' }}</td>
                            </tr>
                            <tr class="bg-slate-50/70">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Garansi</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">{{ $product->warranty ?? '-' }}</td>
                            </tr>
                            <tr class="bg-white">
                                <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">Bobot Unit</th>
                                <td class="px-4 py-3.5 text-slate-900 font-medium">{{ $weightKg }} kg ({{ $product->weight_grams }} gram)</td>
                            </tr>

                            @if(!empty($product->additional_specs) && is_array($product->additional_specs))
                                @foreach($product->additional_specs as $specKey => $specVal)
                                    <tr class="{{ $loop->odd ? 'bg-slate-50/70' : 'bg-white' }}">
                                        <th scope="row" class="px-4 py-3.5 text-left font-bold text-slate-700">
                                            {{ Str::headline((string) $specKey) }}
                                        </th>
                                        <td class="px-4 py-3.5 text-slate-900 font-medium">
                                            {{ is_array($specVal) ? implode(', ', $specVal) : $specVal }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Related Products --}}
        @if($relatedProducts->isNotEmpty())
            <section class="mt-14">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Laptop Serupa di {{ $product->category_name }}
                    </h2>
                    <a href="{{ route('catalog.category', $product->category_slug) }}" class="text-sm font-bold text-store-primary hover:underline">
                        Lihat Semua &rarr;
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $relProd)
                        <x-store.product-card :product="$relProd" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection

