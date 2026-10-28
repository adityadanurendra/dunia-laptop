@extends('layouts.storefront')

@section('title', 'Checkout Pesanan')
@section('meta_description', 'Selesaikan pesanan laptop Anda dengan aman, pilih alamat pengiriman dan kurir terpercaya di Dunia Laptop.')

@section('content')
<div
    x-data="{
        selectedAddressId: {{ $addresses->where('is_default', 1)->first()->id ?? ($addresses->first()->id ?? 'null') }},
        selectedCourier: '{{ $courierOptions[0]['key'] ?? 'jne_REG' }}',
        couriers: @js(collect($courierOptions)->keyBy('key')),
        subtotal: {{ (float) $cart->subtotal }},
        showNewAddressModal: false,

        get shippingCost() {
            return this.couriers[this.selectedCourier]?.shipping_cost || 0;
        },

        get totalAmount() {
            return this.subtotal + this.shippingCost;
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num).toLocaleString('id-ID');
        }
    }"
    class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10"
>
    {{-- Breadcrumbs --}}
    <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-store-primary">Beranda</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}" class="hover:text-store-primary">Keranjang</a>
        <span>/</span>
        <span class="text-slate-900 font-semibold">Checkout</span>
    </nav>

    {{-- Error Alerts --}}
    @if($errors->any())
        <div class="mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 flex items-start gap-3 text-sm text-rose-800">
            <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold">Gagal memproses pesanan:</p>
                <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Left Column: Delivery Address & Shipping Option & Notes --}}
            <div class="lg:col-span-8 space-y-8">
                {{-- Step 1: Shipping Address --}}
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-xl bg-store-primary text-white font-black text-sm flex items-center justify-center">1</span>
                            <h2 class="text-lg font-extrabold text-slate-900">Alamat Pengiriman</h2>
                        </div>
                        <button
                            type="button"
                            @click="showNewAddressModal = true"
                            class="text-xs font-bold text-store-primary hover:underline flex items-center gap-1"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Tambah Alamat Baru</span>
                        </button>
                    </div>

                    <div class="mt-5">
                        @if($addresses->isEmpty())
                            <div class="text-center py-8 px-4 rounded-2xl bg-amber-50/70 border border-amber-200">
                                <svg class="w-12 h-12 text-amber-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="mt-2 text-sm font-bold text-slate-800">Anda belum memiliki alamat pengiriman tersimpan</p>
                                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                    Tambahkan alamat tujuan pengiriman laptop Anda agar kurir dapat mengantarkan paket dengan aman dan tepat waktu.
                                </p>
                                <button
                                    type="button"
                                    @click="showNewAddressModal = true"
                                    class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-store-primary text-white text-xs font-bold shadow hover:opacity-90 transition"
                                >
                                    <span>+ Masukkan Alamat Pengiriman</span>
                                </button>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($addresses as $addr)
                                    <label
                                        class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                                        :class="selectedAddressId == {{ $addr->id }} ? 'border-store-primary bg-store-primary/5 ring-2 ring-store-primary/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                                    >
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <input
                                                    type="radio"
                                                    name="address_id"
                                                    value="{{ $addr->id }}"
                                                    x-model.number="selectedAddressId"
                                                    class="text-store-primary focus:ring-store-primary h-4 w-4"
                                                    required
                                                >
                                                <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-700">
                                                    {{ $addr->label }}
                                                </span>
                                                @if($addr->is_default)
                                                    <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                                        Utama
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-3 text-xs space-y-1 text-slate-600">
                                            <p class="font-bold text-sm text-slate-900">{{ $addr->recipient_name }}</p>
                                            <p class="font-mono text-slate-500">{{ $addr->phone }}</p>
                                            <p class="line-clamp-2 text-slate-700">{{ $addr->address_line }}</p>
                                            <p class="text-slate-500">{{ $addr->city }}, {{ $addr->province }} {{ $addr->postal_code }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Step 2: Courier & Shipping Method --}}
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-xl bg-store-primary text-white font-black text-sm flex items-center justify-center">2</span>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">Pilihan Kurir Ekspedisi</h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Berat total paket laptop: <strong>{{ number_format($cart->total_weight_grams / 1000, 2, ',', '.') }} kg</strong> (termasuk bubble &amp; pelindung)
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3">
                        @foreach($courierOptions as $opt)
                            <label
                                class="flex items-center justify-between p-4 rounded-2xl border-2 cursor-pointer transition select-none"
                                :class="selectedCourier === '{{ $opt['key'] }}' ? 'border-store-primary bg-store-primary/5 ring-2 ring-store-primary/20' : 'border-slate-200 hover:border-slate-300 bg-white'"
                            >
                                <div class="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        name="courier"
                                        value="{{ $opt['key'] }}"
                                        x-model="selectedCourier"
                                        class="text-store-primary focus:ring-store-primary h-4 w-4"
                                        required
                                    >
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $opt['service_title'] }}</p>
                                        <p class="text-xs text-slate-500">Estimasi tiba: <span class="font-semibold text-slate-700">{{ $opt['etd'] }}</span></p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <span class="text-sm font-extrabold text-slate-900">{{ $opt['formatted_cost'] }}</span>
                                    <span class="block text-[10px] text-slate-400">Asuransi toko aktif</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Step 3: Customer Notes --}}
                <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                        <span class="w-7 h-7 rounded-xl bg-slate-800 text-white font-black text-sm flex items-center justify-center">3</span>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">Catatan Pesanan <span class="text-xs font-normal text-slate-400">(Opsional)</span></h2>
                            <p class="text-xs text-slate-500 mt-0.5">Instruksi khusus untuk teknisi penyiapan unit atau kurir pengantar.</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <textarea
                            name="customer_notes"
                            rows="3"
                            placeholder="Contoh: Tolong sekalian partisi harddisk SSD menjadi 2 drive (C: dan D:) atau mohon telepon terlebih dahulu sebelum tiba di rumah."
                            class="w-full rounded-2xl border-slate-300 bg-slate-50 text-sm text-slate-900 focus:bg-white focus:border-store-primary focus:ring-store-primary transition"
                        >{{ old('customer_notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Right Column: Order Summary & Confirmation CTA (Sticky) --}}
            <div class="lg:col-span-4 lg:sticky lg:top-28 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-5">
                    <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3">Ringkasan Pembayaran</h2>

                    {{-- Products Mini Snapshot --}}
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                        @foreach($cart->items as $item)
                            <div class="flex items-center gap-3 text-xs">
                                <div class="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center">
                                    @if($item->image_url)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ $item->product_name }}</p>
                                    <p class="text-slate-500">{{ $item->quantity }}x @ Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                </div>
                                <span class="font-extrabold text-slate-900 shrink-0">
                                    Rp {{ number_format($item->line_total, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-slate-100 pt-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Subtotal Laptop ({{ $cart->total_items_count }} item)</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($cart->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Ongkos Kirim Kurir</span>
                            <span class="font-bold text-slate-900" x-text="formatRupiah(shippingCost)"></span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Diskon Voucher</span>
                            <span class="font-semibold text-emerald-600">Rp 0</span>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-4 flex items-center justify-between">
                        <div>
                            <span class="block text-xs font-bold text-slate-500 uppercase">Total Tagihan:</span>
                            <span class="text-xl font-black text-slate-900" x-text="formatRupiah(totalAmount)"></span>
                        </div>
                    </div>

                    {{-- Submit CTA --}}
                    <div>
                        <button
                            type="submit"
                            :disabled="!selectedAddressId"
                            class="w-full py-4 px-6 rounded-2xl bg-store-primary hover:opacity-95 text-white font-extrabold text-sm shadow-md flex items-center justify-center gap-2 transition disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span>Konfirmasi &amp; Lanjutkan Pembayaran</span>
                        </button>
                        <p class="text-[11px] text-center text-slate-400 mt-2">
                            Dengan mengonfirmasi, stok laptop akan langsung dikunci khusus untuk pesanan Anda.
                        </p>
                    </div>
                </div>

                {{-- Security & Buyer Protection Badge --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm text-xs text-slate-600 space-y-2">
                    <p class="font-bold text-slate-800 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Proteksi Pengiriman Terjamin
                    </p>
                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Seluruh pengiriman laptop telah diproteksi dengan asuransi pengiriman serta packing kayu berlapis gratis tanpa biaya tambahan.
                    </p>
                </div>
            </div>
        </div>
    </form>

    {{-- Inline Add New Address Modal --}}
    <div
        x-cloak
        x-show="showNewAddressModal"
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
    >
        <div
            @click.outside="showNewAddressModal = false"
            class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-200"
        >
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base sm:text-lg font-extrabold text-slate-900">Tambah Alamat Pengiriman Baru</h3>
                <button
                    type="button"
                    @click="showNewAddressModal = false"
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('addresses.store') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Label Alamat</label>
                        <input
                            type="text"
                            name="label"
                            placeholder="Rumah, Kantor, Kost"
                            required
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-store-primary focus:ring-store-primary"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Penerima</label>
                        <input
                            type="text"
                            name="recipient_name"
                            value="{{ Auth::user()->name }}"
                            required
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-store-primary focus:ring-store-primary"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp / Telepon</label>
                    <input
                        type="text"
                        name="phone"
                        value="{{ Auth::user()->phone }}"
                        placeholder="081234567890"
                        required
                        class="w-full text-xs rounded-xl border-slate-300 focus:border-store-primary focus:ring-store-primary"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap (Nama Jalan, No Rumah, RT/RW)</label>
                    <textarea
                        name="address_line"
                        rows="2"
                        required
                        placeholder="Jl. Affandi No. 45, RT 02/RW 05, Caturtunggal, Depok"
                        class="w-full text-xs rounded-xl border-slate-300 focus:border-store-primary focus:ring-store-primary"
                    ></textarea>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kota/Kabupaten</label>
                        <input
                            type="text"
                            name="city"
                            placeholder="Sleman"
                            required
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-store-primary focus:ring-store-primary"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Provinsi</label>
                        <input
                            type="text"
                            name="province"
                            placeholder="D.I. Yogyakarta"
                            required
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-store-primary focus:ring-store-primary"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Pos</label>
                        <input
                            type="text"
                            name="postal_code"
                            placeholder="55281"
                            required
                            class="w-full text-xs rounded-xl border-slate-300 focus:border-store-primary focus:ring-store-primary"
                        >
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input
                        type="checkbox"
                        id="is_default"
                        name="is_default"
                        value="1"
                        checked
                        class="rounded text-store-primary focus:ring-store-primary h-4 w-4"
                    >
                    <label for="is_default" class="text-xs text-slate-700 select-none">Jadikan sebagai alamat pengiriman utama</label>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button
                        type="button"
                        @click="showNewAddressModal = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="px-5 py-2 rounded-xl bg-store-primary hover:opacity-90 text-white text-xs font-extrabold shadow transition"
                    >
                        Simpan Alamat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

