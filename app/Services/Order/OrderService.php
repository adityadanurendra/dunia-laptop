<?php

namespace App\Services\Order;

use App\Services\Store\StorefrontDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Standard courier rate catalog for laptop deliveries.
     */
    protected array $courierRates = [
        'jne' => [
            'name' => 'JNE Express',
            'services' => [
                'REG' => ['title' => 'JNE Reguler', 'cost_per_kg' => 25000, 'etd' => '2 - 3 Hari'],
                'YES' => ['title' => 'JNE Yakin Esok Sampai (YES)', 'cost_per_kg' => 45000, 'etd' => '1 Hari (Besok Sampai)'],
            ],
        ],
        'sicepat' => [
            'name' => 'SiCepat Ekspres',
            'services' => [
                'REG' => ['title' => 'SiCepat Reguler', 'cost_per_kg' => 24000, 'etd' => '2 - 3 Hari'],
                'BEST' => ['title' => 'SiCepat BEST (Besok Sampai)', 'cost_per_kg' => 42000, 'etd' => '1 Hari'],
            ],
        ],
        'jnt' => [
            'name' => 'J&T Express',
            'services' => [
                'EZ' => ['title' => 'J&T Express (EZ)', 'cost_per_kg' => 26000, 'etd' => '2 - 3 Hari'],
            ],
        ],
    ];

    /**
     * Get available courier options calculated based on chargeable laptop weight.
     */
    public function getCourierOptions(int $weightGrams): array
    {
        $weightKg = max(1, (int) ceil($weightGrams / 1000));
        $options = [];

        foreach ($this->courierRates as $code => $courier) {
            foreach ($courier['services'] as $serviceCode => $service) {
                $cost = (float) ($service['cost_per_kg'] * $weightKg);
                $options[] = [
                    'key' => "{$code}_{$serviceCode}",
                    'courier_code' => $code,
                    'courier_name' => $courier['name'],
                    'service_code' => $serviceCode,
                    'service_title' => $service['title'],
                    'etd' => $service['etd'],
                    'cost_per_kg' => $service['cost_per_kg'],
                    'shipping_cost' => $cost,
                    'formatted_cost' => 'Rp ' . number_format($cost, 0, ',', '.'),
                ];
            }
        }

        return $options;
    }

    /**
     * Calculate server-side verified shipping cost.
     */
    public function calculateShippingCost(string $courierCode, string $courierService, int $weightGrams): float
    {
        $code = strtolower(trim($courierCode));
        $service = strtoupper(trim($courierService));
        $weightKg = max(1, (int) ceil($weightGrams / 1000));

        if (isset($this->courierRates[$code]['services'][$service])) {
            return (float) ($this->courierRates[$code]['services'][$service]['cost_per_kg'] * $weightKg);
        }

        // Safe fallback rate
        return (float) (25000 * $weightKg);
    }

    /**
     * Generate unique order number (DL-YYYYMMDD-XXXXX).
     */
    public function generateOrderNumber(): string
    {
        do {
            $random = strtoupper(Str::random(5));
            $number = 'DL-' . date('Ymd') . '-' . $random;
            $exists = DB::table('orders')->where('order_number', $number)->exists();
        } while ($exists);

        return $number;
    }

    /**
     * Create order from active customer cart with pessimistic locking (lockForUpdate).
     *
     * @throws ValidationException
     */
    public function createOrderFromCart(
        int $userId,
        int $addressId,
        string $courierCode,
        string $courierService,
        ?string $customerNotes = null
    ): object {
        return DB::transaction(function () use ($userId, $addressId, $courierCode, $courierService, $customerNotes) {
            // 1. Get user cart and cart items
            $cart = DB::table('carts')->where('user_id', $userId)->first();
            if (! $cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang belanja Anda masih kosong.',
                ]);
            }

            $cartItems = DB::table('cart_items')->where('cart_id', $cart->id)->get();
            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang belanja Anda masih kosong.',
                ]);
            }

            // 2. Validate user destination address
            $address = DB::table('addresses')
                ->where('id', $addressId)
                ->where('user_id', $userId)
                ->first();

            if (! $address) {
                throw ValidationException::withMessages([
                    'address_id' => 'Alamat pengiriman tidak valid atau tidak ditemukan.',
                ]);
            }

            // 3. Pessimistic Locking: lock product rows for update to prevent negative stock (FR-18)
            $productIds = $cartItems->pluck('product_id')->toArray();
            $lockedProducts = DB::table('products')
                ->whereIn('id', $productIds)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Fetch technical specs for historical snapshot (FR-16)
            $specifications = DB::table('product_specifications')
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            // 4. Validate stock & build snapshot data
            $subtotal = 0.0;
            $totalWeightGrams = 0;
            $itemsToInsert = [];

            foreach ($cartItems as $cartItem) {
                $product = $lockedProducts->get($cartItem->product_id);

                if (! $product || $product->status !== 'published') {
                    $name = $product ? $product->name : "ID: {$cartItem->product_id}";
                    throw ValidationException::withMessages([
                        'stock' => "Unit laptop '{$name}' sudah tidak aktif atau ditarik dari katalog.",
                    ]);
                }

                if ((int) $product->stock < (int) $cartItem->quantity) {
                    $available = (int) $product->stock;
                    throw ValidationException::withMessages([
                        'stock' => "Stok unit laptop '{$product->name}' tidak mencukupi (tersedia: {$available} unit, Anda meminta: {$cartItem->quantity} unit).",
                    ]);
                }

                $unitPrice = (float) $product->price;
                $lineSubtotal = $unitPrice * (int) $cartItem->quantity;
                $subtotal += $lineSubtotal;
                $totalWeightGrams += ((int) ($product->weight_grams ?: 1500)) * (int) $cartItem->quantity;

                $spec = $specifications->get($cartItem->product_id);
                $snapshotSpecs = [
                    'condition' => $product->condition,
                    'processor' => $spec->processor ?? null,
                    'ram_gb' => $spec->ram_gb ?? null,
                    'ram_type' => $spec->ram_type ?? null,
                    'storage_capacity' => $spec->storage_capacity ?? null,
                    'storage_type' => $spec->storage_type ?? null,
                    'gpu' => $spec->gpu ?? null,
                    'screen_size' => $spec->screen_size ?? null,
                    'screen_resolution' => $spec->screen_resolution ?? null,
                    'physical_condition' => $spec->physical_condition ?? null,
                    'battery_condition' => $spec->battery_condition ?? null,
                    'operating_system' => $spec->operating_system ?? null,
                    'warranty' => $spec->warranty ?? null,
                    'weight_grams' => $product->weight_grams,
                ];

                $itemsToInsert[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $unitPrice,
                    'quantity' => (int) $cartItem->quantity,
                    'subtotal' => $lineSubtotal,
                    'snapshot_specs' => json_encode($snapshotSpecs),
                ];
            }

            // 5. Atomic stock decrement
            foreach ($cartItems as $cartItem) {
                DB::table('products')
                    ->where('id', $cartItem->product_id)
                    ->decrement('stock', (int) $cartItem->quantity);
            }

            // 6. Calculate total payment
            $shippingCost = $this->calculateShippingCost($courierCode, $courierService, $totalWeightGrams);
            $discountAmount = 0.0;
            $totalAmount = $subtotal + $shippingCost - $discountAmount;
            $orderNumber = $this->generateOrderNumber();

            // 7. Save header orders
            $orderId = DB::table('orders')->insertGetId([
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'customer_notes' => ! empty($customerNotes) ? trim($customerNotes) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 8. Save snapshot into order_items
            foreach ($itemsToInsert as &$itemRecord) {
                $itemRecord['order_id'] = $orderId;
                $itemRecord['created_at'] = now();
                $itemRecord['updated_at'] = now();
            }
            DB::table('order_items')->insert($itemsToInsert);

            // 9. Create initial shipment entry
            $destinationAddress = sprintf(
                '%s (%s), %s, %s, %s %s',
                $address->recipient_name,
                $address->phone,
                $address->address_line,
                $address->city,
                $address->province,
                $address->postal_code
            );

            DB::table('shipments')->insert([
                'order_id' => $orderId,
                'courier_code' => strtolower($courierCode),
                'courier_service' => strtoupper($courierService),
                'tracking_number' => null,
                'shipping_cost' => $shippingCost,
                'status' => 'pending',
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->phone,
                'destination_address' => $destinationAddress,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 10. Clean up user cart items
            DB::table('cart_items')->where('cart_id', $cart->id)->delete();

            return DB::table('orders')->where('id', $orderId)->first();
        });
    }

    /**
     * Retrieve complete order details including items, shipment, and spec snapshots.
     */
    public function getOrderDetails(string $orderNumber, ?int $userId = null): ?object
    {
        $query = DB::table('orders')->where('order_number', $orderNumber)->whereNull('deleted_at');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $order = $query->first();
        if (! $order) {
            return null;
        }

        $items = DB::table('order_items')
            ->leftJoin('product_images', function ($join) {
                $join->on('order_items.product_id', '=', 'product_images.product_id')
                    ->where('product_images.is_primary', '=', 1);
            })
            ->where('order_items.order_id', $order->id)
            ->select([
                'order_items.*',
                'product_images.image_path as primary_image',
            ])
            ->get()
            ->map(function ($item) {
                $item->specs = is_string($item->snapshot_specs)
                    ? json_decode($item->snapshot_specs, true)
                    : (array) $item->snapshot_specs;
                $item->image_url = StorefrontDataService::resolveImageUrl($item->primary_image);

                return $item;
            });

        $shipment = DB::table('shipments')->where('order_id', $order->id)->first();
        $payment = DB::table('payments')->where('order_id', $order->id)->orderByDesc('id')->first();

        $order->items = $items;
        $order->shipment = $shipment;
        $order->payment = $payment;

        return $order;
    }

    /**
     * Retrieve paginated orders for a customer with order items and shipment info.
     */
    public function getCustomerOrders(int $userId, ?string $statusFilter = null, int $perPage = 10)
    {
        $query = DB::table('orders')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->orderByDesc('id');

        if (! empty($statusFilter) && in_array($statusFilter, ['pending', 'processing', 'shipped', 'completed', 'cancelled'], true)) {
            $query->where('status', $statusFilter);
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        if ($paginator->isNotEmpty()) {
            $orderIds = $paginator->pluck('id')->toArray();

            $orderItems = DB::table('order_items')
                ->leftJoin('product_images', function ($join) {
                    $join->on('order_items.product_id', '=', 'product_images.product_id')
                        ->where('product_images.is_primary', '=', 1);
                })
                ->whereIn('order_items.order_id', $orderIds)
                ->select([
                    'order_items.*',
                    'product_images.image_path as primary_image',
                ])
                ->get()
                ->map(function ($item) {
                    $item->specs = is_string($item->snapshot_specs)
                        ? json_decode($item->snapshot_specs, true)
                        : (array) $item->snapshot_specs;
                    $item->image_url = StorefrontDataService::resolveImageUrl($item->primary_image);

                    return $item;
                })
                ->groupBy('order_id');

            $shipments = DB::table('shipments')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->keyBy('order_id');

            $payments = DB::table('payments')
                ->whereIn('order_id', $orderIds)
                ->orderByDesc('id')
                ->get()
                ->groupBy('order_id');

            $paginator->getCollection()->transform(function ($order) use ($orderItems, $shipments, $payments) {
                $order->items = $orderItems->get($order->id, collect());
                $order->total_units = $order->items->sum('quantity');
                $order->shipment = $shipments->get($order->id);
                $order->payment = $payments->get($order->id)?->first();

                return $order;
            });
        }

        return $paginator;
    }

    /**
     * Cancel an order requested by the customer with atomic stock restitution.
     *
     * @throws ValidationException
     */
    public function cancelOrderByCustomer(string $orderNumber, int $userId, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($orderNumber, $userId, $reason) {
            $order = DB::table('orders')
                ->where('order_number', $orderNumber)
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if (! $order) {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan tidak ditemukan.',
                ]);
            }

            if ($order->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Hanya pesanan berstatus "Menunggu Pembayaran" yang dapat dibatalkan.',
                ]);
            }

            $noteSuffix = ! empty($reason) ? ' [Dibatalkan Pelanggan: ' . trim($reason) . ']' : ' [Dibatalkan oleh Pelanggan]';
            $updatedNotes = $order->customer_notes ? $order->customer_notes . $noteSuffix : trim($noteSuffix, ' []');

            // 1. Update order status
            DB::table('orders')->where('id', $order->id)->update([
                'status' => 'cancelled',
                'customer_notes' => $updatedNotes,
                'updated_at' => now(),
            ]);

            // 2. Update payment status if exists
            DB::table('payments')
                ->where('order_id', $order->id)
                ->where('transaction_status', 'pending')
                ->update([
                    'transaction_status' => 'cancel',
                    'updated_at' => now(),
                ]);

            // 3. Atomically restore product stock
            $items = DB::table('order_items')->where('order_id', $order->id)->get();
            foreach ($items as $item) {
                if ($item->product_id) {
                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->increment('stock', (int) $item->quantity);
                }
            }

            return true;
        });
    }
}

