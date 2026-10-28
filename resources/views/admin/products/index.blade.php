@extends('layouts.admin')

@section('header', 'Katalog & Stok Laptop')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">Katalog & Stok Laptop</h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola informasi laptop baru & bekas, spesifikasi hardware, harga, dan ketersediaan unit</p>
        </div>
        <div>
            <a href="{{ route('admin.products.create') }}" 
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition-all transform active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Laptop Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm space-y-3">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama laptop, SKU, prosesor..." 
                    class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <div>
                <select name="condition" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Kondisi</option>
                    <option value="new" {{ $condition === 'new' ? 'selected' : '' }}>Unit Baru (New)</option>
                    <option value="used" {{ $condition === 'used' ? 'selected' : '' }}>Unit Bekas (Second)</option>
                </select>
            </div>

            <div>
                <select name="category_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="brand_id" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Brand</option>
                    @foreach($brands as $b)
                        <option value="{{ $b->id }}" {{ $brandId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="stock_status" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Semua Stok</option>
                    <option value="low_stock" {{ $stockStatus === 'low_stock' ? 'selected' : '' }}>Menipis (&le; 2)</option>
                    <option value="out_of_stock" {{ $stockStatus === 'out_of_stock' ? 'selected' : '' }}>Stok Habis (0)</option>
                    <option value="in_stock" {{ $stockStatus === 'in_stock' ? 'selected' : '' }}>Tersedia (> 0)</option>
                </select>

                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($products->isEmpty())
            <div class="p-12 text-center text-slate-500">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <h3 class="text-sm font-bold text-slate-800">Tidak ada produk ditemukan</h3>
                <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci atau hapus filter untuk melihat produk lainnya.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4 w-16">Foto</th>
                            <th class="py-3.5 px-4">Laptop & Spesifikasi</th>
                            <th class="py-3.5 px-4">Kategori & Merek</th>
                            <th class="py-3.5 px-4">Kondisi</th>
                            <th class="py-3.5 px-4">Harga & Stok (Pembaruan Cepat)</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($products as $product)
                            @php
                                $img = $primaryImages[$product->id] ?? null;
                                $imgUrl = $img 
                                    ? (str_starts_with($img, 'http') ? $img : asset('storage/' . $img))
                                    : 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=150&q=80';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Image Thumbnail -->
                                <td class="py-3.5 px-4">
                                    <img src="{{ $imgUrl }}" alt="{{ $product->name }}" class="w-12 h-12 rounded-lg object-cover border border-slate-200 bg-slate-100">
                                </td>

                                <!-- Product Name, SKU, Specs -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="font-bold text-slate-900 line-clamp-1 hover:text-blue-600">
                                        <a href="{{ route('admin.products.edit', $product->id) }}">{{ $product->name }}</a>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        SKU: {{ $product->sku }}
                                    </div>
                                    @if($product->processor)
                                        <div class="text-[11px] text-slate-600 mt-1 flex items-center gap-1.5 flex-wrap">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-100 font-medium">{{ $product->processor }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $product->ram_gb }}GB</span>
                                            <span>&bull;</span>
                                            <span>{{ $product->storage_capacity }}</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Category & Brand -->
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $product->brand_name ?? 'Umum' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $product->category_name ?? 'Laptop' }}</div>
                                </td>

                                <!-- Condition -->
                                <td class="py-3.5 px-4">
                                    @if($product->condition === 'new')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Baru (New)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Bekas (Used)
                                        </span>
                                    @endif
                                </td>

                                <!-- Fast Inline Update for Price & Stock -->
                                <td class="py-3.5 px-4">
                                    <form method="POST" action="{{ route('admin.products.quick-update', $product->id) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-slate-400">Rp</span>
                                                <input type="number" name="price" value="{{ (int)$product->price }}" min="0" step="10000" 
                                                    class="w-28 text-xs py-1 px-2 rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-mono font-bold">
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] text-slate-400">Stok:</span>
                                                <input type="number" name="stock" value="{{ $product->stock }}" min="0" 
                                                    class="w-16 text-xs py-1 px-2 rounded-lg border-slate-200 focus:border-blue-500 focus:ring-blue-500 font-bold {{ $product->stock <= 2 ? 'text-rose-600 bg-rose-50 border-rose-200' : '' }}">
                                                <button type="submit" title="Simpan Cepat" class="p-1 rounded bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </td>

                                <!-- Publication Status -->
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $statusBadge = match($product->status) {
                                            'published' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            'archived' => 'bg-rose-100 text-rose-800 border-rose-200',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $statusBadge }}">
                                        {{ ucfirst($product->status) }}
                                    </span>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.products.edit', $product->id) }}" 
                                            class="p-1.5 rounded-lg bg-slate-100 hover:bg-blue-100 text-slate-700 hover:text-blue-700 transition-colors" title="Edit Lengkap">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>

                                        <a href="{{ route('catalog.show', $product->slug) }}" target="_blank"
                                            class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Lihat di Toko">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>

                                        <form method="POST" action="{{ route('admin.products.destroy', $product->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini dari katalog?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-100 text-slate-600 hover:text-rose-600 transition-colors" title="Hapus Produk">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

