<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $resolvedTitle = $title ?? trim($__env->yieldContent('title'));
        $siteTitle = $siteSettings['site_title'] ?? config('app.name', 'Dunia Laptop');
        $pageTitle = $resolvedTitle !== '' ? "{$resolvedTitle} | {$siteTitle}" : $siteTitle;
        $pageDescription = $metaDescription ?? trim($__env->yieldContent('meta_description')) ?: ($siteSettings['site_tagline'] ?? '');
        $primaryRgb = $siteSettings['theme_primary_rgb'] ?? '37 99 235';
        $accentRgb = $siteSettings['theme_accent_rgb'] ?? '14 165 233';
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --color-store-primary: {{ $primaryRgb }};
            --color-store-accent: {{ $accentRgb }};
        }
    </style>
</head>
<body
    x-data="{
        mobileMenuOpen: false,
        toastVisible: false,
        toastMessage: '',
        toastTimer: null,
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => { this.toastVisible = false; }, 3800);
        }
    }"
    @store-toast.window="showToast($event.detail.message)"
    class="font-sans antialiased bg-slate-50 text-slate-900 min-h-screen flex flex-col"
>
    {{-- Top Information Bar --}}
    <div class="bg-slate-900 text-slate-300 text-xs border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2 truncate">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="font-medium text-slate-200 truncate">{{ $siteSettings['site_tagline'] ?? 'Pusat Laptop Baru & Bekas Bergaransi' }}</span>
            </div>
            <div class="hidden md:flex items-center gap-5 text-slate-400">
                @if(!empty($siteSettings['operating_hours']))
                    <span class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-store-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $siteSettings['operating_hours'] }}
                    </span>
                @endif
                @if(!empty($siteSettings['contact_whatsapp']))
                    <a href="{{ $siteSettings['whatsapp_url'] ?? '#' }}" target="_blank" rel="noopener" class="flex items-center gap-1.5 hover:text-white transition">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        WA: {{ $siteSettings['contact_whatsapp'] }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Storefront Navbar --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-4">
                {{-- Brand Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 group">
                    <div class="w-10 h-10 rounded-xl bg-store-primary text-white flex items-center justify-center shadow-sm group-hover:opacity-95 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <span class="block text-lg sm:text-xl font-extrabold tracking-tight text-slate-900">
                            {{ Str::before($siteSettings['site_title'] ?? 'Dunia Laptop', ' -') }}
                        </span>
                        <span class="hidden sm:block text-[11px] font-medium text-slate-500">
                            Baru & Bekas Bergaransi
                        </span>
                    </div>
                </a>

                {{-- Global Search Bar (Desktop) --}}
                <form action="{{ route('catalog.index') }}" method="GET" class="hidden md:flex flex-1 max-w-xl mx-4">
                    <div class="relative w-full flex">
                        <input
                            type="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nama laptop, seri ROG/ThinkPad/MacBook, atau SKU..."
                            class="w-full rounded-l-xl border-slate-300 bg-slate-50 focus:bg-white pl-4 pr-3 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-store-primary focus:ring-store-primary transition"
                        >
                        <button
                            type="submit"
                            class="px-5 rounded-r-xl bg-store-primary hover:opacity-90 text-white text-sm font-semibold flex items-center gap-1.5 transition shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span>Cari</span>
                        </button>
                    </div>
                </form>

                {{-- Right Actions: Cart + User Account + Mobile Menu --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    {{-- Cart Icon --}}
                    @if(Route::has('cart.index'))
                        <a
                            href="{{ route('cart.index') }}"
                            class="relative inline-flex items-center justify-center p-2.5 rounded-xl text-slate-700 hover:text-store-primary hover:bg-slate-100 transition"
                            title="Keranjang Belanja"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @if(($cartCount ?? 0) > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[20px] h-5 px-1.5 rounded-full bg-store-primary text-white text-[11px] font-bold flex items-center justify-center">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </a>
                    @else
                        <button
                            type="button"
                            @click="$dispatch('store-toast', { message: 'Halaman Keranjang Belanja segera tersedia pada tahap Checkout.' })"
                            class="relative inline-flex items-center justify-center p-2.5 rounded-xl text-slate-700 hover:text-store-primary hover:bg-slate-100 transition"
                            title="Keranjang Belanja"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            @if(($cartCount ?? 0) > 0)
                                <span class="absolute -top-0.5 -right-0.5 min-w-[20px] h-5 px-1.5 rounded-full bg-store-primary text-white text-[11px] font-bold flex items-center justify-center">
                                    {{ $cartCount }}
                                </span>
                            @endif
                        </button>
                    @endif

                    {{-- Auth Menu --}}
                    @auth
                        <div x-data="{ userOpen: false }" class="relative">
                            <button
                                type="button"
                                @click="userOpen = !userOpen"
                                @click.outside="userOpen = false"
                                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 hover:border-slate-300 bg-white text-sm font-semibold text-slate-700 transition"
                            >
                                <span class="w-7 h-7 rounded-full bg-store-primary/15 text-store-primary font-bold flex items-center justify-center text-xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden sm:inline max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div
                                x-cloak
                                x-show="userOpen"
                                x-transition
                                class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-slate-200 py-2 z-50"
                            >
                                <div class="px-4 py-2 border-b border-slate-100">
                                    <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                                </div>

                                @if(Auth::user()->isSuperAdmin() || Auth::user()->isAdmin() || Auth::user()->isStaff())
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-store-primary hover:bg-slate-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                        </svg>
                                        Dashboard Operasional
                                    </a>
                                @endif

                                @if(Route::has('orders.index'))
                                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                        Riwayat Pesanan
                                    </a>
                                @endif

                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                    Profil Akun
                                </a>

                                <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-100 mt-1 pt-1">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50">
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="hidden sm:flex items-center gap-2">
                            <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:text-store-primary hover:bg-slate-100 transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-xl text-sm font-semibold bg-store-primary text-white hover:opacity-90 shadow-sm transition">
                                Daftar
                            </a>
                        </div>
                    @endauth

                    {{-- Mobile Hamburger --}}
                    <button
                        type="button"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-xl text-slate-700 hover:bg-slate-100"
                        aria-label="Buka Navigasi"
                    >
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <svg x-cloak x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Desktop Category & Quick Links Bar --}}
            <nav class="hidden md:flex items-center justify-between border-t border-slate-100 py-2.5 text-sm">
                <div class="flex items-center gap-6">
                    <a
                        href="{{ route('home') }}"
                        class="font-semibold transition {{ request()->routeIs('home') ? 'text-store-primary' : 'text-slate-600 hover:text-store-primary' }}"
                    >
                        Beranda
                    </a>
                    <a
                        href="{{ route('catalog.index') }}"
                        class="font-semibold transition {{ request()->routeIs('catalog.index') && !request('condition') ? 'text-store-primary' : 'text-slate-600 hover:text-store-primary' }}"
                    >
                        Semua Katalog
                    </a>
                    <a
                        href="{{ route('catalog.index', ['condition' => 'new']) }}"
                        class="inline-flex items-center gap-1.5 font-semibold transition {{ request('condition') === 'new' ? 'text-emerald-600' : 'text-slate-600 hover:text-emerald-600' }}"
                    >
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Laptop Baru
                    </a>
                    <a
                        href="{{ route('catalog.index', ['condition' => 'used']) }}"
                        class="inline-flex items-center gap-1.5 font-semibold transition {{ request('condition') === 'used' ? 'text-amber-600' : 'text-slate-600 hover:text-amber-600' }}"
                    >
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Laptop Bekas Berkualitas
                    </a>

                    @if(isset($navCategories) && $navCategories->isNotEmpty())
                        <div x-data="{ catOpen: false }" class="relative">
                            <button
                                type="button"
                                @click="catOpen = !catOpen"
                                @click.outside="catOpen = false"
                                class="inline-flex items-center gap-1 font-semibold text-slate-600 hover:text-store-primary transition"
                            >
                                <span>Kategori</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div
                                x-cloak
                                x-show="catOpen"
                                x-transition
                                class="absolute left-0 mt-2 w-64 rounded-2xl bg-white shadow-xl border border-slate-200 py-2 z-50"
                            >
                                @foreach($navCategories as $navCat)
                                    <a
                                        href="{{ route('catalog.category', $navCat->slug) }}"
                                        class="flex items-center justify-between px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-store-primary"
                                    >
                                        <span>{{ $navCat->name }}</span>
                                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">
                                            {{ $navCat->products_count }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="text-xs font-medium text-slate-500">
                    Garansi Resmi &amp; Inspeksi 25 Titik Unit Bekas
                </div>
            </nav>
        </div>

        {{-- Mobile Expandable Menu --}}
        <div x-cloak x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-5 space-y-4">
            <form action="{{ route('catalog.index') }}" method="GET" class="flex">
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari laptop atau SKU..."
                    class="w-full rounded-l-xl border-slate-300 text-sm focus:border-store-primary focus:ring-store-primary"
                >
                <button type="submit" class="px-4 rounded-r-xl bg-store-primary text-white text-sm font-semibold">
                    Cari
                </button>
            </form>

            <div class="grid grid-cols-2 gap-2 text-sm font-semibold">
                <a href="{{ route('home') }}" class="px-3 py-2 rounded-xl bg-slate-50 text-slate-800">Beranda</a>
                <a href="{{ route('cart.index') }}" class="px-3 py-2 rounded-xl bg-slate-50 text-slate-800 flex items-center justify-between">
                    <span>Keranjang</span>
                    @if(($cartCount ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full bg-store-primary text-white text-xs font-bold">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('catalog.index', ['condition' => 'new']) }}" class="px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700">Laptop Baru</a>
                <a href="{{ route('catalog.index', ['condition' => 'used']) }}" class="px-3 py-2 rounded-xl bg-amber-50 text-amber-800">Laptop Bekas</a>
            </div>

            @if(isset($navCategories) && $navCategories->isNotEmpty())
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kategori</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($navCategories as $navCat)
                            <a href="{{ route('catalog.category', $navCat->slug) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 text-xs font-medium text-slate-700">
                                {{ $navCat->name }} ({{ $navCat->products_count }})
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @guest
                <div class="flex gap-2 pt-2 border-t border-slate-100">
                    <a href="{{ route('login') }}" class="flex-1 text-center py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="flex-1 text-center py-2.5 rounded-xl bg-store-primary text-white text-sm font-semibold">
                        Daftar Akun
                    </a>
                </div>
            @endguest
        </div>
    </header>

    {{-- Session Flash Banner --}}
    @if(session('success') || session('error') || session('info'))
        <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm font-medium text-rose-800">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('info'))
                <div class="rounded-xl bg-sky-50 border border-sky-200 px-4 py-3 text-sm font-medium text-sky-800">
                    {{ session('info') }}
                </div>
            @endif
        </div>
    @endif

    {{-- Main Page Content --}}
    <main class="flex-1">
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    {{-- Storefront Footer --}}
    <footer class="bg-slate-950 text-slate-300 border-t border-slate-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                {{-- Column 1: Store Identity --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-store-primary text-white flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-extrabold text-white">
                            {{ $siteSettings['company_name'] ?? 'PT Dunia Laptop Indonesia' }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        {{ $siteSettings['site_tagline'] ?? 'Pusat Laptop Terbaik, Bergaransi Resmi & Toko di Indonesia' }}
                    </p>
                    <div class="flex flex-wrap gap-2 pt-1">
                        <span class="px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-xs text-emerald-400 font-medium">
                            100% Original
                        </span>
                        <span class="px-2.5 py-1 rounded-md bg-slate-900 border border-slate-800 text-xs text-sky-400 font-medium">
                            Midtrans Secure Payment
                        </span>
                    </div>
                </div>

                {{-- Column 2: Belanja --}}
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4">Eksplorasi Katalog</h3>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        <li><a href="{{ route('catalog.index') }}" class="hover:text-white transition">Semua Laptop</a></li>
                        <li><a href="{{ route('catalog.index', ['condition' => 'new']) }}" class="hover:text-white transition">Laptop Baru Bergaransi Resmi</a></li>
                        <li><a href="{{ route('catalog.index', ['condition' => 'used']) }}" class="hover:text-white transition">Laptop Bekas Lolos QC</a></li>
                        <li><a href="{{ route('catalog.index', ['sort' => 'price_asc']) }}" class="hover:text-white transition">Harga Termurah</a></li>
                    </ul>
                </div>

                {{-- Column 3: Kategori Populer --}}
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4">Kategori Pilihan</h3>
                    <ul class="space-y-2.5 text-sm text-slate-400">
                        @if(isset($navCategories) && $navCategories->isNotEmpty())
                            @foreach($navCategories->take(5) as $footerCat)
                                <li>
                                    <a href="{{ route('catalog.category', $footerCat->slug) }}" class="hover:text-white transition">
                                        {{ $footerCat->name }}
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li><a href="{{ route('catalog.index') }}" class="hover:text-white transition">Katalog Produk</a></li>
                        @endif
                    </ul>
                </div>

                {{-- Column 4: Kontak & Lokasi Toko --}}
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white mb-4">Kontak &amp; Showroom</h3>
                    <ul class="space-y-3 text-sm text-slate-400">
                        @if(!empty($siteSettings['store_address']))
                            <li class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-store-accent shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $siteSettings['store_address'] }}</span>
                            </li>
                        @endif
                        @if(!empty($siteSettings['contact_whatsapp']))
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <a href="{{ $siteSettings['whatsapp_url'] ?? '#' }}" target="_blank" rel="noopener" class="hover:text-white transition">
                                    {{ $siteSettings['contact_whatsapp'] }} (WhatsApp)
                                </a>
                            </li>
                        @endif
                        @if(!empty($siteSettings['contact_email']))
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-store-accent shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:{{ $siteSettings['contact_email'] }}" class="hover:text-white transition">
                                    {{ $siteSettings['contact_email'] }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800/80 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ $siteSettings['company_name'] ?? 'PT Dunia Laptop Indonesia' }}. Seluruh hak cipta dilindungi.</p>
                <p>Transaksi Online Aman dengan Midtrans Snap &amp; Pengiriman Berasuransi.</p>
            </div>
        </div>
    </footer>

    {{-- Interactive Toast Notification --}}
    <div
        x-cloak
        x-show="toastVisible"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
        class="fixed bottom-6 right-6 z-50 max-w-sm rounded-2xl bg-slate-900 text-white px-4 py-3.5 shadow-2xl border border-slate-700 flex items-center gap-3"
    >
        <span class="w-2.5 h-2.5 rounded-full bg-store-accent shrink-0"></span>
        <p class="text-sm font-medium leading-snug" x-text="toastMessage"></p>
    </div>

    {{-- Chatbot Widget Hook (Phase 8) --}}
    @includeIf('components.chatbot-widget')
</body>
</html>

