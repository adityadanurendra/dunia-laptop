@extends('layouts.admin')

@section('header', 'Edit Laptop')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Edit Laptop: {{ $product->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5 font-mono">SKU: {{ $product->sku }} &bull; Slug: {{ $product->slug }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('catalog.show', $product->slug) }}" target="_blank"
                class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span>Lihat di Toko</span>
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Section 1: Informasi Dasar -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>1. Informasi Dasar Produk</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Laptop <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">SKU Produk <span class="text-rose-500">*</span></label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                        class="w-full text-xs font-mono uppercase rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kondisi Laptop <span class="text-rose-500">*</span></label>
                    <select name="condition" required class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        <option value="new" {{ old('condition', $product->condition) === 'new' ? 'selected' : '' }}>Baru (New / Segel)</option>
                        <option value="used" {{ old('condition', $product->condition) === 'used' ? 'selected' : '' }}>Bekas (Second Berkualitas)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori <span class="text-rose-500">*</span></label>
                    <select name="category_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Merek / Brand <span class="text-rose-500">*</span></label>
                    <select name="brand_id" required class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="price" value="{{ old('price', (int)$product->price) }}" min="0" step="1000" required
                        class="w-full text-xs font-mono font-bold rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Unit <span class="text-rose-500">*</span></label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Berat (Gram) <span class="text-rose-500">*</span></label>
                        <input type="number" name="weight_grams" value="{{ old('weight_grams', $product->weight_grams) }}" min="100" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Status Publikasi <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                        <option value="published" {{ old('status', $product->status) === 'published' ? 'selected' : '' }}>Dipublikasikan (Tampil di Toko)</option>
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft (Sembunyikan Sementara)</option>
                        <option value="archived" {{ old('status', $product->status) === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Section 2: Spesifikasi Hardware Lengkap -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
                <span>2. Spesifikasi Hardware Laptop</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Processor / CPU <span class="text-rose-500">*</span></label>
                    <input type="text" name="processor" value="{{ old('processor', $specs->processor ?? '') }}" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kapasitas RAM (GB) <span class="text-rose-500">*</span></label>
                        <input type="number" name="ram_gb" value="{{ old('ram_gb', $specs->ram_gb ?? 16) }}" min="2" max="256" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe RAM</label>
                        <input type="text" name="ram_type" value="{{ old('ram_type', $specs->ram_type ?? 'DDR5') }}"
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kapasitas Storage <span class="text-rose-500">*</span></label>
                        <input type="text" name="storage_capacity" value="{{ old('storage_capacity', $specs->storage_capacity ?? '512GB') }}" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipe Storage</label>
                        <input type="text" name="storage_type" value="{{ old('storage_type', $specs->storage_type ?? 'NVMe PCIe SSD') }}"
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kartu Grafis / GPU <span class="text-rose-500">*</span></label>
                    <input type="text" name="gpu" value="{{ old('gpu', $specs->gpu ?? '') }}" required
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Ukuran Layar (Inci) <span class="text-rose-500">*</span></label>
                        <input type="number" name="screen_size" value="{{ old('screen_size', $specs->screen_size ?? 14.0) }}" step="0.1" min="10" max="25" required
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Resolusi & Refresh Rate</label>
                        <input type="text" name="screen_resolution" value="{{ old('screen_resolution', $specs->screen_resolution ?? 'FHD IPS') }}"
                            class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sistem Operasi (OS)</label>
                    <input type="text" name="operating_system" value="{{ old('operating_system', $specs->operating_system ?? 'Windows 11 Home Original') }}"
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Garansi Unit</label>
                    <input type="text" name="warranty" value="{{ old('warranty', $specs->warranty ?? 'Garansi Toko 1 Bulan') }}"
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Section 3: Kondisi Khusus Laptop Bekas -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>3. Kondisi Khusus Unit (Terutama Laptop Bekas)</span>
                </span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kondisi Fisik / Casing</label>
                    <input type="text" name="physical_condition" value="{{ old('physical_condition', $specs->physical_condition ?? '') }}" placeholder="Contoh: 95% Mulus, lecet halus pemakaian"
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kondisi Baterai & Daya Tahan</label>
                    <input type="text" name="battery_condition" value="{{ old('battery_condition', $specs->battery_condition ?? '') }}" placeholder="Contoh: Battery Health 90%, bertahan 4 jam"
                        class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Section 4: Deskripsi Detail -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                </svg>
                <span>4. Deskripsi Detail & Fitur Unggulan</span>
            </h3>

            <div>
                <textarea name="description" rows="5" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        <!-- Section 5: Galeri Foto Saat Ini & Unggah Foto Baru -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-5">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>5. Galeri Foto Produk</span>
            </h3>

            <!-- Existing Gallery Grid -->
            @if($images->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-2">Foto yang Sudah Ada:</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        @foreach($images as $img)
                            @php
                                $imgSrc = str_starts_with($img->image_path, 'http') ? $img->image_path : asset('storage/' . $img->image_path);
                            @endphp
                            <div class="relative group bg-slate-50 border border-slate-200 rounded-xl overflow-hidden p-2 text-center">
                                <img src="{{ $imgSrc }}" alt="Product Image" class="w-full h-28 object-cover rounded-lg mb-2">
                                
                                @if($img->is_primary)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-600 text-white mb-2 shadow-sm">
                                        Foto Utama
                                    </span>
                                @else
                                    <div class="flex items-center justify-center gap-1.5 mb-2">
                                        <button type="submit" form="set-primary-{{ $img->id }}" class="text-[10px] px-2 py-0.5 rounded bg-slate-200 hover:bg-blue-600 hover:text-white text-slate-700 font-semibold transition-colors">
                                            Jadikan Utama
                                        </button>
                                    </div>
                                @endif

                                <div>
                                    <button type="submit" form="delete-img-{{ $img->id }}" onclick="return confirm('Hapus foto ini?')" class="text-[10px] text-rose-600 hover:text-rose-800 font-bold">
                                        Hapus Foto
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Upload Additional Images -->
            <div class="pt-3 border-t border-slate-100">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tambah Foto Baru ke Galeri:</label>
                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-2 cursor-pointer">
                <p class="text-[11px] text-slate-400 mt-1">
                    Pilih satu atau lebih file foto untuk ditambahkan ke galeri produk ini.
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition-all transform active:scale-95">
                Simpan Perubahan
            </button>
        </div>
    </form>

    <!-- Hidden standalone forms for Image Primary and Delete -->
    @foreach($images as $img)
        <form id="set-primary-{{ $img->id }}" method="POST" action="{{ route('admin.products.images.primary', [$product->id, $img->id]) }}" class="hidden">
            @csrf
        </form>
        <form id="delete-img-{{ $img->id }}" method="POST" action="{{ route('admin.products.images.destroy', [$product->id, $img->id]) }}" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
</div>
@endsection

