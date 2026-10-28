@extends('layouts.admin')

@section('header', 'Detail Pesanan ' . $order->order_number)

@section('content')
<div class="space-y-6">
    <!-- Header with Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-xl font-black text-slate-900 tracking-tight font-mono">
                        {{ $order->order_number }}
                    </h2>
                    @php
                        $orderBadge = match($order->status) {
                            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                            'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
                            default => 'bg-amber-100 text-amber-800 border-amber-200',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $orderBadge }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Dibuat pada {{ \Carbon\Carbon::parse($order->created_at)->translatedFormat('l, d F Y - H:i') }} WIB
                </p>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Order Details & Customer Snapshot (2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Items Purchased Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900">Rincian Laptop yang Dipesan</h3>
                    <span class="text-xs text-slate-500">{{ $items->count() }} item</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($items as $item)
                        @php
                            $specs = is_string($item->snapshot_specs) ? json_decode($item->snapshot_specs, true) : $item->snapshot_specs;
                        @endphp
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row items-start justify-between gap-4">
                            <div class="space-y-1">
                                <h4 class="text-sm font-bold text-slate-900">{{ $item->product_name }}</h4>
                                <div class="flex items-center gap-2 text-xs text-slate-500 font-mono">
                                    <span>SKU: {{ $item->product_sku }}</span>
                                    <span>&bull;</span>
                                    <span>Qty: <strong>{{ $item->quantity }} unit</strong></span>
                                </div>
                                @if(!empty($specs) && is_array($specs))
                                    <div class="mt-2 text-[11px] bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-slate-600 space-y-1">
                                        <div class="font-bold text-slate-700">Spesifikasi Snapshot:</div>
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-0.5">
                                            @if(!empty($specs['processor'])) <div>&bull; CPU: {{ $specs['processor'] }}</div> @endif
                                            @if(!empty($specs['ram_gb'])) <div>&bull; RAM: {{ $specs['ram_gb'] }} GB {{ $specs['ram_type'] ?? '' }}</div> @endif
                                            @if(!empty($specs['storage_capacity'])) <div>&bull; Storage: {{ $specs['storage_capacity'] }} {{ $specs['storage_type'] ?? '' }}</div> @endif
                                            @if(!empty($specs['gpu'])) <div>&bull; GPU: {{ $specs['gpu'] }}</div> @endif
                                            @if(!empty($specs['condition'])) <div>&bull; Kondisi: {{ ucfirst($specs['condition']) }}</div> @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="text-left sm:text-right flex-shrink-0">
                                <div class="text-xs text-slate-400">@ Rp {{ number_format($item->unit_price, 0, ',', '.') }}</div>
                                <div class="text-sm font-black text-slate-900 mt-0.5">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Financial Summary Footer -->
                <div class="p-5 bg-slate-50/70 border-t border-slate-100 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal Produk:</span>
                        <span class="font-semibold">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Pengiriman Kurir:</span>
                        <span class="font-semibold">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Diskon Voucher:</span>
                            <span class="font-semibold">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-black text-slate-900">
                        <span>Total Pembayaran:</span>
                        <span class="text-blue-600 font-mono text-base">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer & Shipping Destination Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Informasi Pelanggan & Alamat Pengiriman</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 font-medium block">Akun Pemesan</span>
                        <p class="font-bold text-slate-900 mt-0.5">{{ $order->customer_name }}</p>
                        <p class="text-slate-600">{{ $order->customer_email }}</p>
                        <p class="text-slate-600">{{ $order->customer_phone ?? '-' }}</p>
                    </div>

                    @if($shipment)
                        <div>
                            <span class="text-slate-400 font-medium block">Penerima & Alamat Tujuan</span>
                            <p class="font-bold text-slate-900 mt-0.5">{{ $shipment->recipient_name }} ({{ $shipment->recipient_phone }})</p>
                            <p class="text-slate-600 mt-1 leading-relaxed">{{ $shipment->destination_address }}</p>
                        </div>
                    @endif
                </div>

                @if($order->customer_notes)
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/60 text-xs text-amber-900">
                        <strong class="font-bold block mb-0.5">Catatan Khusus dari Pembeli:</strong>
                        <span>{{ $order->customer_notes }}</span>
                    </div>
                @endif
            </div>

            <!-- Payment Details Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Status Pembayaran Midtrans</h3>
                @if($payment)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div>
                            <span class="text-slate-400 block">Status Transaksi</span>
                            <span class="font-bold mt-0.5 inline-block text-slate-900 capitalize">{{ $payment->transaction_status }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Metode Pembayaran</span>
                            <span class="font-bold mt-0.5 inline-block text-slate-900 capitalize">{{ str_replace('_', ' ', $payment->payment_type ?? '-') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Waktu Pembayaran</span>
                            <span class="font-bold mt-0.5 inline-block text-slate-900">
                                {{ $payment->settlement_time ? \Carbon\Carbon::parse($payment->settlement_time)->translatedFormat('d M Y, H:i') : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Ref Transaksi</span>
                            <span class="font-mono text-[11px] text-slate-700 block truncate" title="{{ $payment->transaction_reference ?? '-' }}">
                                {{ $payment->transaction_reference ?? '-' }}
                            </span>
                        </div>
                    </div>
                @else
                    <p class="text-xs text-slate-500">Belum ada catatan pembayaran untuk pesanan ini.</p>
                @endif
            </div>
        </div>

        <!-- Right: Operational Controls Form (1 Column) -->
        <div class="space-y-6">
            <!-- Order Status Update Form -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Perbarui Status Pesanan</span>
                </h3>

                <form method="POST" action="{{ route('admin.orders.update-status', $order->order_number) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status Operasional</label>
                        <select name="status" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending (Menunggu Pembayaran)</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing (Sedang Diproses Toko)</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped (Telah Diserahkan ke Kurir)</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed (Pesanan Selesai)</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled (Batalkan Pesanan)</option>
                        </select>
                    </div>

                    @if($order->status !== 'cancelled')
                        <p class="text-[11px] text-amber-700 bg-amber-50 p-2.5 rounded-xl border border-amber-200/60 leading-relaxed">
                            <strong>Perhatian:</strong> Mengubah status menjadi <em>Cancelled</em> akan secara otomatis mengembalikan stok laptop ke inventaris database.
                        </p>
                    @endif

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition-colors">
                        Simpan Status Pesanan
                    </button>
                </form>
            </div>

            <!-- Shipment & Courier Tracking Management Form -->
            @if($shipment)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                        <span>Kelola Pengiriman & Resi Kurir</span>
                    </h3>

                    <form method="POST" action="{{ route('admin.orders.update-shipment', $order->order_number) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Kurir</label>
                                <input type="text" name="courier_code" value="{{ old('courier_code', $shipment->courier_code) }}" required
                                    class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 uppercase">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Layanan</label>
                                <input type="text" name="courier_service" value="{{ old('courier_service', $shipment->courier_service) }}" required
                                    class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor Resi Pengiriman</label>
                            <input type="text" name="tracking_number" value="{{ old('tracking_number', $shipment->tracking_number) }}" placeholder="Contoh: JNE0123456789"
                                class="w-full text-xs font-mono rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500 uppercase">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pengiriman</label>
                            <select name="shipment_status" class="w-full text-xs rounded-xl border-slate-200 focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending" {{ $shipment->status === 'pending' ? 'selected' : '' }}>Pending (Menunggu Penjemputan)</option>
                                <option value="picked_up" {{ $shipment->status === 'picked_up' ? 'selected' : '' }}>Picked Up (Sudah Diambil Kurir)</option>
                                <option value="in_transit" {{ $shipment->status === 'in_transit' ? 'selected' : '' }}>In Transit (Dalam Pengiriman)</option>
                                <option value="delivered" {{ $shipment->status === 'delivered' ? 'selected' : '' }}>Delivered (Paket Diterima Pembeli)</option>
                                <option value="returned" {{ $shipment->status === 'returned' ? 'selected' : '' }}>Returned (Dikembalikan ke Penjual)</option>
                            </select>
                        </div>

                        <div class="text-[11px] text-slate-500 space-y-1 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                            <div>Waktu Kirim: <strong>{{ $shipment->shipped_at ? \Carbon\Carbon::parse($shipment->shipped_at)->translatedFormat('d M Y, H:i') : 'Belum dikirim' }}</strong></div>
                            <div>Waktu Diterima: <strong>{{ $shipment->delivered_at ? \Carbon\Carbon::parse($shipment->delivered_at)->translatedFormat('d M Y, H:i') : 'Belum sampai' }}</strong></div>
                        </div>

                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold shadow-md shadow-purple-600/30 transition-colors">
                            Simpan Resi & Pengiriman
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
