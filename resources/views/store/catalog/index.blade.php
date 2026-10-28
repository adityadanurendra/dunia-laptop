@extends('layouts.storefront')

@php
    $headingTitle = 'Katalog Laptop Baru & Bekas';
    if (!empty($activeCategory)) {
        $headingTitle = $activeCategory->name;
    } elseif ($filters['condition'] === 'new') {
        $headingTitle = 'Katalog Laptop Baru Bergaransi Resmi';
    } elseif ($filters['condition'] === 'used') {
        $headingTitle = 'Katalog Laptop Bekas Berkualitas';
    }

    $hasActiveFilters = $filters['search'] !== ''
        || $filters['condition'] !== ''
        || $filters['category'] !== ''
        || $filters['brand'] !== ''
        || $filters['min_price'] !== ''
        || $filters['max_price'] !== ''
        || $filters['in_stock'];
@endphp

@section('title', $headingTitle)

@section('content')
    {{-- Page Header & Breadcrumbs --}}
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-3">
                <a href="{{ route('home') }}" class="hover:text-store-primary">Beranda</a>
                <span>/</span>
                <a href="{{ route('catalog.index') }}" class="hover:text-store-primary">Katalog</a>
                @if(!empty($activeCategory))
                    <span>/</span>
                    <span class="text-slate-900 font-semibold">{{ $activeCategory->name }}</span>
                @endif
            </nav>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $headingTitle }}
                    </h1>
                    @if(!empty($activeCategory->description))
                        <p class="mt-1.5 text-sm text-slate-500 max-w-2xl">{{ $activeCategory->description }}</p>
                    @elseif($filters['search'] !== '')
                        <p class="mt-1.5 text-sm text-slate-500">
                            Menampilkan hasil pencarian untuk kata kunci <span class="font-bold text-slate-800">"{{ $filters['search'] }}"</span>
                        </p>
                    @else
                        <p class="mt-1.5 text-sm text-slate-500">
                            Bandingkan spesifikasi, kondisi fisik, dan harga terbaik sesuai kebutuhan kerja maupun gaming Anda.
                        </p>
                    @endif
                </div>

                <div class="text-xs sm:text-sm font-semibold text-slate-500">
                    Total <span class="text-slate-900 font-extrabold">{{ $products->total() }}</span> produk ditemukan
                </div>
            </div>
        </div>
    </div>

    {{-- Main Catalog Layout --}}
    <div x-data="{ mobileFilterOpen: false }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Mobile Filter Trigger --}}
        <div class="lg:hidden mb-4 flex items-center justify-between gap-3">
            <button
                type="button"
                @click="mobileFilterOpen = !mobileFilterOpen"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-300 text-sm font-bold text-slate-800 shadow-sm"
            >
                <svg class="w-4 h-4 text-store-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <span>Filter Katalog</span>
            </button>

            @if($hasActiveFilters)
                <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-rose-600 hover:underline">
                    Reset Filter
                </a>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Filter Sidebar --}}
            <aside
                :class="mobileFilterOpen ? 'block' : 'hidden lg:block'"
                class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 p-5 shadow-sm lg:sticky lg:top-28"
            >
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <h2 class="text-base font-extrabold text-slate-900">Filter Pencarian</h2>
                    @if($hasActiveFilters)
                        <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-rose-600 hover:underline">
                            Reset
                        </a>
                    @endif
                </div>

                <form action="{{ route('catalog.index') }}" method="GET" class="space-y-6">
                    <input type="hidden" name="sort" value="{{ $filters['sort'] }}">

                    {{-- Keyword Search --}}
                    <div>
                        <label for="filter-search" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                            Kata Kunci / SKU
                        </label>
                        <input
                            id="filter-search"
                            type="text"
                            name="search"
                            value="{{ $filters['search'] }}"
                            placeholder="Nama laptop, SKU, spesifikasi..."
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-store-primary focus:ring-store-primary"
                        >
                    </div>

                    {{-- Condition Filter --}}
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Kondisi Unit</p>
                        <div class="grid grid-cols-3 gap-1.5">
                            <label class="cursor-pointer">
                                <input type="radio" name="condition" value="" class="peer sr-only" {{ $filters['condition'] === '' ? 'checked' : '' }}>
                                <span class="block text-center px-2 py-2 rounded-xl border text-xs font-bold transition peer-checked:bg-store-primary peer-checked:text-white peer-checked:border-store-primary border-slate-200 text-slate-600 hover:bg-slate-50">
                                    Semua
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="condition" value="new" class="peer sr-only" {{ $filters['condition'] === 'new' ? 'checked' : '' }}>
                                <span class="block text-center px-2 py-2 rounded-xl border text-xs font-bold transition peer-checked:bg-emerald-600 peer-checked:text-white peer-checked:border-emerald-600 border-slate-200 text-slate-600 hover:bg-slate-50">
                                    Baru
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="condition" value="used" class="peer sr-only" {{ $filters['condition'] === 'used' ? 'checked' : '' }}>
                                <span class="block text-center px-2 py-2 rounded-xl border text-xs font-bold transition peer-checked:bg-amber-500 peer-checked:text-slate-950 peer-checked:border-amber-500 border-slate-200 text-slate-600 hover:bg-slate-50">
                                    Bekas
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Category Filter --}}
                    <div>
                        <label for="filter-category" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                            Kategori Laptop
                        </label>
                        <select
                            id="filter-category"
                            name="category"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-store-primary focus:ring-store-primary"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ $filters['category'] === $cat->slug ? 'selected' : '' }}>
                                    {{ $cat->name }} ({{ $cat->products_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Brand Filter --}}
                    <div>
                        <label for="filter-brand" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                            Merek / Brand
                        </label>
                        <select
                            id="filter-brand"
                            name="brand"
                            class="w-full rounded-xl border-slate-300 text-sm focus:border-store-primary focus:ring-store-primary"
                        >
                            <option value="">Semua Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->slug }}" {{ $filters['brand'] === $brand->slug ? 'selected' : '' }}>
                                    {{ $brand->name }} ({{ $brand->products_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Price Range Filter --}}
                    <div x-data="{ minP: '{{ $filters['min_price'] }}', maxP: '{{ $filters['max_price'] }}' }">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Rentang Harga (Rp)</p>
                        <div class="space-y-2.5">
                            <div>
                                <input
                                    type="number"
                                    name="min_price"
                                    x-model="minP"
                                    min="0"
                                    step="100000"
                                    placeholder="Harga Minimum (cth: 5000000)"
                                    class="w-full rounded-xl border-slate-300 text-sm focus:border-store-primary focus:ring-store-primary"
                                >
                            </div>
                            <div>
                                <input
                                    type="number"
                                    name="max_price"
                                    x-model="maxP"
                                    min="0"
                                    step="100000"
                                    placeholder="Harga Maksimum (cth: 20000000)"
                                    class="w-full rounded-xl border-slate-300 text-sm focus:border-store-primary focus:ring-store-primary"
                                >
                            </div>
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                <button type="button" @click="minP = ''; maxP = '10000000'" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] font-semibold text-slate-700 transition">
                                    &lt; 10 Juta
                                </button>
                                <button type="button" @click="minP = '10000000'; maxP = '20000000'" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] font-semibold text-slate-700 transition">
                                    10 - 20 Juta
                                </button>
                                <button type="button" @click="minP = '20000000'; maxP = ''" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] font-semibold text-slate-700 transition">
                                    &gt; 20 Juta
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Stock Availability Checkbox --}}
                    <div class="pt-1">
                        <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="in_stock"
                                value="1"
                                {{ $filters['in_stock'] ? 'checked' : '' }}
                                class="rounded border-slate-300 text-store-primary focus:ring-store-primary"
                            >
                            <span class="text-sm font-semibold text-slate-700">Stok Tersedia Saja</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-store-primary hover:opacity-95 text-white text-sm font-bold shadow-sm transition"
                    >
                        Terapkan Filter
                    </button>
                </form>
            </aside>

            {{-- Main Product Grid Column --}}
            <div class="lg:col-span-9 space-y-6">
                {{-- Sort Bar & Active Filter Chips --}}
                <div class="bg-white rounded-2xl border border-slate-200 px-4 sm:px-5 py-3.5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2">
                        @if($filters['condition'] !== '')
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                                Kondisi: {{ $filters['condition'] === 'new' ? 'Baru' : 'Bekas' }}
                            </span>
                        @endif
                        @if(!empty($activeCategory))
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-store-primary/10 text-xs font-bold text-store-primary">
                                Kategori: {{ $activeCategory->name }}
                            </span>
                        @endif
                        @if(!empty($activeBrand))
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 text-xs font-bold text-slate-700">
                                Brand: {{ $activeBrand->name }}
                            </span>
                        @endif
                        @if($filters['in_stock'])
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-50 text-xs font-bold text-emerald-700">
                                Ready Stock
                            </span>
                        @endif
                        @if(!$hasActiveFilters)
                            <span class="text-xs sm:text-sm text-slate-500 font-medium">
                                Menampilkan koleksi katalog laptop aktif
                            </span>
                        @endif
                    </div>

                    <form action="{{ url()->current() }}" method="GET" class="flex items-center gap-2 shrink-0">
                        @foreach(request()->except(['sort', 'page']) as $qKey => $qVal)
                            @if(is_scalar($qVal) && $qVal !== '')
                                <input type="hidden" name="{{ $qKey }}" value="{{ $qVal }}">
                            @endif
                        @endforeach

                        <label for="catalog-sort" class="text-xs font-bold text-slate-500 whitespace-nowrap">Urutkan:</label>
                        <select
                            id="catalog-sort"
                            name="sort"
                            onchange="this.form.submit()"
                            class="rounded-xl border-slate-300 text-sm font-semibold text-slate-800 py-1.5 pl-3 pr-8 focus:border-store-primary focus:ring-store-primary"
                        >
                            <option value="latest" {{ $filters['sort'] === 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="price_asc" {{ $filters['sort'] === 'price_asc' ? 'selected' : '' }}>Harga Termurah</option>
                            <option value="price_desc" {{ $filters['sort'] === 'price_desc' ? 'selected' : '' }}>Harga Termahal</option>
                        </select>
                    </form>
                </div>

                {{-- Product Cards Grid --}}
                @if($products->isEmpty())
                    <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-sm">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-900">Tidak Ada Laptop yang Cocok</h3>
                        <p class="mt-1.5 text-sm text-slate-500 max-w-md mx-auto">
                            Coba sesuaikan kata kunci pencarian, perluas rentang harga, atau bersihkan filter aktif untuk melihat pilihan unit lainnya.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('catalog.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-store-primary text-white text-sm font-bold shadow-sm hover:opacity-95 transition">
                                Tampilkan Semua Laptop
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <x-store.product-card :product="$product" />
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if($products->hasPages())
                        <div class="pt-4">
                            {{ $products->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

