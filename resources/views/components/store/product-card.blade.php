@props(['product'])

@php
    $imageUrl = \App\Services\Store\StorefrontDataService::resolveImageUrl($product->primary_image ?? null);
    $isUsed = ($product->condition ?? 'new') === 'used';
    $inStock = ((int) ($product->stock ?? 0)) > 0;
    $formattedPrice = 'Rp ' . number_format((float) $product->price, 0, ',', '.');
@endphp

<article class="group bg-white rounded-2xl border border-slate-200/90 hover:border-store-primary/50 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col overflow-hidden">
    {{-- Image & Badges --}}
    <a href="{{ route('catalog.show', $product->slug) }}" class="relative aspect-[4/3] bg-gradient-to-br from-slate-100 via-slate-50 to-slate-200/70 overflow-hidden block">
        @if($imageUrl)
            <img
                src="{{ $imageUrl }}"
                alt="{{ $product->name }}"
                loading="lazy"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            >
        @else
            {{-- Sleek SVG Laptop Fallback Placeholder --}}
            <div class="w-full h-full flex flex-col items-center justify-center p-6 text-slate-400 group-hover:scale-105 transition-transform duration-500">
                <svg class="w-20 h-20 text-slate-300 group-hover:text-store-primary/60 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.3" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="mt-2 text-xs font-bold tracking-widest uppercase text-slate-400">
                    {{ $product->brand_name ?? 'Laptop' }}
                </span>
            </div>
        @endif

        {{-- Top Badges --}}
        <div class="absolute top-3 left-3 right-3 flex items-center justify-between gap-2 pointer-events-none">
            @if($isUsed)
                <span class="px-2.5 py-1 rounded-lg bg-amber-500 text-slate-950 text-[11px] font-extrabold uppercase tracking-wide shadow-sm">
                    Bekas
                </span>
            @else
                <span class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-extrabold uppercase tracking-wide shadow-sm">
                    Baru
                </span>
            @endif

            @if(!empty($product->brand_name))
                <span class="px-2.5 py-1 rounded-lg bg-slate-900/80 backdrop-blur text-white text-[11px] font-semibold">
                    {{ $product->brand_name }}
                </span>
            @endif
        </div>

        {{-- Out of Stock Overlay --}}
        @if(!$inStock)
            <div class="absolute inset-0 bg-slate-900/55 backdrop-blur-[1px] flex items-center justify-center">
                <span class="px-3.5 py-1.5 rounded-xl bg-rose-600 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg">
                    Stok Habis
                </span>
            </div>
        @endif
    </a>

    {{-- Card Body --}}
    <div class="p-4 sm:p-5 flex-1 flex flex-col">
        <div class="flex items-center justify-between gap-2 text-xs text-slate-500 mb-1.5">
            <span class="font-medium text-store-primary truncate">{{ $product->category_name ?? 'Laptop' }}</span>
            <span class="font-mono text-[11px] text-slate-400 truncate">{{ $product->sku }}</span>
        </div>

        <h3 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-store-primary transition line-clamp-2 leading-snug mb-3">
            <a href="{{ route('catalog.show', $product->slug) }}">
                {{ $product->name }}
            </a>
        </h3>

        {{-- Quick Hardware Spec Chips --}}
        @if(!empty($product->ram_gb) || !empty($product->storage_capacity) || !empty($product->screen_size))
            <div class="flex flex-wrap gap-1.5 mb-3">
                @if(!empty($product->ram_gb))
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold">
                        RAM {{ $product->ram_gb }}GB
                    </span>
                @endif
                @if(!empty($product->storage_capacity))
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold">
                        {{ $product->storage_capacity }} SSD
                    </span>
                @endif
                @if(!empty($product->screen_size))
                    <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold">
                        {{ rtrim(rtrim((string) $product->screen_size, '0'), '.') }}"
                    </span>
                @endif
            </div>
        @endif

        @if($isUsed && !empty($product->physical_condition))
            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200/70 rounded-lg px-2.5 py-1.5 mb-3 line-clamp-1">
                Kondisi: {{ $product->physical_condition }}
            </p>
        @endif

        {{-- Price & Stock Footer --}}
        <div class="mt-auto pt-3 border-t border-slate-100 flex items-end justify-between gap-2">
            <div>
                <p class="text-[11px] font-medium text-slate-400">Harga</p>
                <p class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                    {{ $formattedPrice }}
                </p>
            </div>

            <div class="text-right">
                @if($inStock)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold {{ (int) $product->stock <= 3 ? 'text-amber-600' : 'text-emerald-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ (int) $product->stock <= 3 ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
                        Stok: {{ $product->stock }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600">
                        Habis
                    </span>
                @endif
            </div>
        </div>
    </div>
</article>

