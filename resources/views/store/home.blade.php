@extends('layouts.storefront')

@section('content')
    @if($sections->isEmpty())
        {{-- Fallback Hero when homepage_sections is not seeded yet --}}
        <section class="relative overflow-hidden bg-slate-900 text-white py-20 lg:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-store-primary/20 border border-store-primary/40 text-sky-300 text-xs font-bold uppercase tracking-wider mb-6">
                    Pusat Laptop Baru &amp; Bekas Bergaransi
                </span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight max-w-3xl mx-auto leading-tight">
                    {{ $siteSettings['site_title'] ?? 'Dunia Laptop Indonesia' }}
                </h1>
                <p class="mt-4 text-base sm:text-lg text-slate-300 max-w-2xl mx-auto">
                    {{ $siteSettings['site_tagline'] ?? 'Temukan laptop kerja, kuliah, dan gaming impian Anda dengan jaminan garansi terpercaya.' }}
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <a href="{{ route('catalog.index') }}" class="px-7 py-3.5 rounded-xl bg-store-primary hover:opacity-95 text-white font-bold text-sm shadow-lg transition">
                        Jelajahi Katalog Laptop
                    </a>
                    <a href="{{ route('catalog.index', ['condition' => 'used']) }}" class="px-7 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 text-white font-bold text-sm transition">
                        Lihat Laptop Bekas Pilihan
                    </a>
                </div>
            </div>
        </section>
    @else
        @foreach($sections as $section)
            @php
                $content = is_array($section->content_data) ? $section->content_data : [];
                $payload = $sectionPayloads[$section->id] ?? collect();
            @endphp

            {{-- 1. HERO BANNER SECTION --}}
            @if($section->section_type === 'hero_banner')
                @php
                    $banners = isset($content['banners']) && is_array($content['banners']) && count($content['banners']) > 0
                        ? array_values($content['banners'])
                        : [[
                            'title' => $section->title ?? 'Promo Spesial Dunia Laptop',
                            'subtitle' => $section->subtitle ?? '',
                            'cta_text' => 'Lihat Katalog',
                            'cta_url' => route('catalog.index'),
                        ]];
                @endphp
                <section
                    x-data="{
                        activeSlide: 0,
                        totalSlides: {{ count($banners) }},
                        next() { this.activeSlide = (this.activeSlide + 1) % this.totalSlides; },
                        prev() { this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides; }
                    }"
                    x-init="if (totalSlides > 1) { setInterval(() => next(), 6500); }"
                    class="relative bg-slate-950 text-white overflow-hidden"
                >
                    {{-- Ambient Decorative Glow --}}
                    <div class="absolute inset-0 pointer-events-none opacity-40">
                        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-store-primary blur-3xl"></div>
                        <div class="absolute -bottom-24 right-0 w-96 h-96 rounded-full bg-store-accent blur-3xl"></div>
                    </div>

                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 lg:py-24">
                        @foreach($banners as $idx => $banner)
                            @php
                                $bannerImgUrl = \App\Services\Store\StorefrontDataService::resolveImageUrl($banner['image'] ?? null);
                            @endphp
                            <div
                                {{ $idx > 0 ? 'x-cloak' : '' }}
                                x-show="activeSlide === {{ $idx }}"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-2"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center"
                            >
                                <div class="lg:col-span-7 space-y-5">
                                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/15 text-xs font-bold uppercase tracking-wider text-sky-300">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        {{ $banner['badge'] ?? $section->title ?? 'Promo Unggulan' }}
                                    </div>

                                    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight text-white">
                                        {{ $banner['title'] ?? $section->title }}
                                    </h1>

                                    <p class="text-base sm:text-lg text-slate-300 max-w-2xl leading-relaxed">
                                        {{ $banner['subtitle'] ?? $section->subtitle }}
                                    </p>

                                    <div class="pt-2 flex flex-wrap items-center gap-3.5">
                                        <a
                                            href="{{ $banner['cta_url'] ?? route('catalog.index') }}"
                                            class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-store-primary hover:opacity-95 text-white font-bold text-sm shadow-lg transition"
                                        >
                                            <span>{{ $banner['cta_text'] ?? 'Belanja Sekarang' }}</span>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                            </svg>
                                        </a>
                                        <a
                                            href="{{ route('catalog.index') }}"
                                            class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/15 border border-white/15 text-white font-semibold text-sm transition"
                                        >
                                            Semua Laptop
                                        </a>
                                    </div>
                                </div>

                                <div class="lg:col-span-5">
                                    <div class="relative rounded-3xl border border-white/15 bg-gradient-to-br from-slate-900/90 via-slate-800/80 to-slate-900/90 p-6 sm:p-8 shadow-2xl overflow-hidden">
                                        @if($bannerImgUrl)
                                            <img src="{{ $bannerImgUrl }}" alt="{{ $banner['title'] ?? 'Promo Banner' }}" class="w-full h-64 object-cover rounded-2xl">
                                        @else
                                            <div class="aspect-[16/10] flex flex-col items-center justify-center text-center p-4">
                                                <div class="w-20 h-20 rounded-2xl bg-store-primary/20 border border-store-primary/40 flex items-center justify-center text-sky-300 mb-4">
                                                    <svg class="w-11 h-11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                                <p class="text-sm font-bold text-white">{{ $section->title }}</p>
                                                <p class="text-xs text-slate-400 mt-1">{{ $section->subtitle }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Slider Controls --}}
                        @if(count($banners) > 1)
                            <div class="mt-8 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @foreach($banners as $idx => $b)
                                        <button
                                            type="button"
                                            @click="activeSlide = {{ $idx }}"
                                            :class="activeSlide === {{ $idx }} ? 'w-8 bg-store-accent' : 'w-2.5 bg-white/30 hover:bg-white/50'"
                                            class="h-2.5 rounded-full transition-all duration-300"
                                            aria-label="Slide {{ $idx + 1 }}"
                                        ></button>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="prev()" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition" aria-label="Slide Sebelumnya">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                    <button type="button" @click="next()" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 text-white transition" aria-label="Slide Berikutnya">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Trust Highlights Strip --}}
                    <div class="border-t border-white/10 bg-slate-900/80">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 grid grid-cols-2 lg:grid-cols-4 gap-4 text-xs sm:text-sm">
                            <div class="flex items-center gap-2.5 text-slate-300">
                                <span class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center shrink-0 font-bold">✓</span>
                                <span>Garansi Resmi &amp; Toko Jelas</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-slate-300">
                                <span class="w-8 h-8 rounded-lg bg-sky-500/15 text-sky-400 flex items-center justify-center shrink-0 font-bold">25</span>
                                <span>25 Titik QC Unit Bekas</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-slate-300">
                                <span class="w-8 h-8 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center shrink-0 font-bold">⚡</span>
                                <span>Pembayaran Otomatis Midtrans</span>
                            </div>
                            <div class="flex items-center gap-2.5 text-slate-300">
                                <span class="w-8 h-8 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 font-bold">🛡</span>
                                <span>Packing Kayu &amp; Asuransi</span>
                            </div>
                        </div>
                    </div>
                </section>

            {{-- 2. CATEGORY SHOWCASE SECTION --}}
            @elseif($section->section_type === 'category_showcase')
                <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                {{ $section->title ?? 'Kategori Pilihan' }}
                            </h2>
                            @if(!empty($section->subtitle))
                                <p class="mt-1.5 text-sm sm:text-base text-slate-500">{{ $section->subtitle }}</p>
                            @endif
                        </div>
                        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-store-primary hover:underline shrink-0">
                            <span>Lihat Semua Kategori</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    @if($payload->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            @foreach($payload as $cat)
                                <a
                                    href="{{ route('catalog.category', $cat->slug) }}"
                                    class="group bg-white rounded-2xl border border-slate-200 hover:border-store-primary p-6 shadow-sm hover:shadow-lg transition-all flex flex-col justify-between"
                                >
                                    <div>
                                        <div class="w-12 h-12 rounded-2xl bg-store-primary/10 text-store-primary group-hover:bg-store-primary group-hover:text-white flex items-center justify-center transition-colors mb-4">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-store-primary transition">
                                            {{ $cat->name }}
                                        </h3>
                                        @if(!empty($cat->description))
                                            <p class="mt-1.5 text-xs sm:text-sm text-slate-500 line-clamp-2 leading-relaxed">
                                                {{ $cat->description }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold">
                                        <span class="text-slate-500">{{ $cat->products_count }} Unit Tersedia</span>
                                        <span class="text-store-primary group-hover:translate-x-1 transition-transform">Telusuri &rarr;</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </section>

            {{-- 3. FEATURED PRODUCTS SECTION --}}
            @elseif($section->section_type === 'featured_products')
                <section class="bg-white border-y border-slate-200/80 py-14 sm:py-16">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-store-primary">Rekomendasi Terbaik</span>
                                <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                    {{ $section->title ?? 'Laptop Unggulan' }}
                                </h2>
                                @if(!empty($section->subtitle))
                                    <p class="mt-1.5 text-sm sm:text-base text-slate-500">{{ $section->subtitle }}</p>
                                @endif
                            </div>
                            <a href="{{ $content['cta_url'] ?? route('catalog.index') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-store-primary hover:underline shrink-0">
                                <span>{{ $content['cta_text'] ?? 'Lihat Semua Produk' }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        @if($payload->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                @foreach($payload as $product)
                                    <x-store.product-card :product="$product" />
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

            {{-- 4. USED LAPTOPS PROMO / HIGHLIGHT SECTION --}}
            @elseif(in_array($section->section_type, ['promo_grid', 'used_laptops'], true))
                <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
                    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-slate-800 text-white p-6 sm:p-10 lg:p-12 shadow-xl border border-slate-800">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                            <div class="lg:col-span-6 space-y-5">
                                @if(!empty($content['badge']))
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-amber-500/20 border border-amber-400/30 text-amber-300 text-xs font-extrabold uppercase tracking-wider">
                                        ★ {{ $content['badge'] }}
                                    </span>
                                @endif

                                <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                                    {{ $section->title ?? 'Laptop Bekas Berkualitas' }}
                                </h2>

                                @if(!empty($section->subtitle))
                                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                                        {{ $section->subtitle }}
                                    </p>
                                @endif

                                @if(!empty($content['highlight_points']) && is_array($content['highlight_points']))
                                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                        @foreach($content['highlight_points'] as $point)
                                            <li class="flex items-center gap-2.5 text-sm text-slate-200 font-medium">
                                                <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0">✓</span>
                                                <span>{{ $point }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                <div class="pt-3">
                                    <a
                                        href="{{ $content['cta_url'] ?? route('catalog.index', ['condition' => 'used']) }}"
                                        class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm shadow-lg transition"
                                    >
                                        <span>{{ $content['cta_text'] ?? 'Eksplorasi Laptop Bekas' }}</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <div class="lg:col-span-6">
                                @if($payload->isNotEmpty())
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                        @foreach($payload as $usedProduct)
                                            <x-store.product-card :product="$usedProduct" />
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded-2xl bg-white/5 border border-white/10 p-8 text-center">
                                        <p class="text-sm text-slate-300 font-medium">Semua unit laptop bekas telah melalui inspeksi 25 titik perangkat keras &amp; baterai.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

            {{-- 5. LATEST PRODUCTS SECTION --}}
            @elseif($section->section_type === 'latest_products')
                <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                        <div>
                            <span class="text-xs font-extrabold uppercase tracking-wider text-store-accent">Katalog Terkini</span>
                            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                {{ $section->title ?? 'Produk Terbaru' }}
                            </h2>
                            @if(!empty($section->subtitle))
                                <p class="mt-1.5 text-sm sm:text-base text-slate-500">{{ $section->subtitle }}</p>
                            @endif
                        </div>
                        <a href="{{ $content['cta_url'] ?? route('catalog.index', ['sort' => 'latest']) }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-store-primary hover:underline shrink-0">
                            <span>{{ $content['cta_text'] ?? 'Lihat Semua' }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    @if($payload->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($payload as $latestProduct)
                                <x-store.product-card :product="$latestProduct" />
                            @endforeach
                        </div>
                    @endif
                </section>

            {{-- 6. FAQ ACCORDION SECTION --}}
            @elseif($section->section_type === 'faq')
                @php
                    $faqItems = isset($content['items']) && is_array($content['items']) ? array_values($content['items']) : [];
                @endphp
                <section class="bg-white border-t border-slate-200/80 py-14 sm:py-16">
                    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center mb-10">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-store-primary">Pusat Informasi</span>
                            <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                                {{ $section->title ?? 'Pertanyaan yang Sering Diajukan' }}
                            </h2>
                            @if(!empty($section->subtitle))
                                <p class="mt-2 text-sm sm:text-base text-slate-500">{{ $section->subtitle }}</p>
                            @endif
                        </div>

                        @if(count($faqItems) > 0)
                            <div x-data="{ openFaq: 0 }" class="space-y-3">
                                @foreach($faqItems as $fIdx => $faq)
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50/60 overflow-hidden">
                                        <button
                                            type="button"
                                            @click="openFaq = openFaq === {{ $fIdx }} ? null : {{ $fIdx }}"
                                            class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 font-bold text-slate-900 hover:text-store-primary transition"
                                        >
                                            <span class="text-sm sm:text-base">{{ $faq['question'] ?? '' }}</span>
                                            <svg
                                                :class="openFaq === {{ $fIdx }} ? 'rotate-180 text-store-primary' : 'text-slate-400'"
                                                class="w-5 h-5 shrink-0 transition-transform duration-200"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                        <div
                                            x-cloak
                                            x-show="openFaq === {{ $fIdx }}"
                                            x-transition
                                            class="px-5 pb-4 text-sm text-slate-600 leading-relaxed border-t border-slate-200/60 pt-3 bg-white"
                                        >
                                            {{ $faq['answer'] ?? '' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        @endforeach
    @endif
@endsection
