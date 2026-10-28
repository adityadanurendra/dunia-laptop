<?php

namespace App\Services\Cart;

use App\Services\Store\StorefrontDataService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    /**
     * Retrieve or create a cart ID for the authenticated user or guest session.
     */
    public function getOrCreateCartId(?int $userId, ?string $sessionId): int
    {
        if ($userId !== null) {
            $cart = DB::table('carts')->where('user_id', $userId)->first();
            if ($cart) {
                return (int) $cart->id;
            }

            // If user has a guest cart session matching, claim it
            if (! empty($sessionId)) {
                $sessionCart = DB::table('carts')
                    ->where('session_id', $sessionId)
                    ->whereNull('user_id')
                    ->first();

                if ($sessionCart) {
                    DB::table('carts')->where('id', $sessionCart->id)->update([
                        'user_id' => $userId,
                        'session_id' => null,
                        'updated_at' => now(),
                    ]);

                    return (int) $sessionCart->id;
                }
            }

            return (int) DB::table('carts')->insertGetId([
                'user_id' => $userId,
                'session_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (empty($sessionId)) {
            throw new \InvalidArgumentException('Sesi tidak valid untuk mengelola keranjang.');
        }

        $sessionCart = DB::table('carts')->where('session_id', $sessionId)->first();
        if ($sessionCart) {
            return (int) $sessionCart->id;
        }

        return (int) DB::table('carts')->insertGetId([
            'user_id' => null,
            'session_id' => $sessionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Add a product to the cart with real-time stock validation.
     */
    public function addItem(?int $userId, ?string $sessionId, int $productId, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);

        $product = DB::table('products')
            ->where('id', $productId)
            ->whereNull('deleted_at')
            ->first();

        if (! $product || $product->status !== 'published') {
            throw ValidationException::withMessages([
                'product' => 'Produk laptop tidak ditemukan atau sedang tidak aktif.',
            ]);
        }

        if ((int) $product->stock < 1) {
            throw ValidationException::withMessages([
                'stock' => "Maaf, stok laptop '{$product->name}' sedang kosong.",
            ]);
        }

        $cartId = $this->getOrCreateCartId($userId, $sessionId);

        $existingItem = DB::table('cart_items')
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();

        $currentQty = $existingItem ? (int) $existingItem->quantity : 0;
        $targetQty = $currentQty + $quantity;

        if ($targetQty > (int) $product->stock) {
            $available = (int) $product->stock;
            throw ValidationException::withMessages([
                'quantity' => "Kuantitas melebihi stok yang tersedia (tersedia: {$available} unit).",
            ]);
        }

        if ($existingItem) {
            DB::table('cart_items')
                ->where('id', $existingItem->id)
                ->update([
                    'quantity' => $targetQty,
                    'updated_at' => now(),
                ]);
            $cartItemId = (int) $existingItem->id;
        } else {
            $cartItemId = (int) DB::table('cart_items')->insertGetId([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $targetQty,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [
            'cart_id' => $cartId,
            'cart_item_id' => $cartItemId,
            'product_id' => $productId,
            'product_name' => $product->name,
            'quantity' => $targetQty,
            'total_items_count' => $this->getItemCount($userId, $sessionId),
        ];
    }

    /**
     * Update the quantity of an item in the cart, or remove if <= 0.
     */
    public function updateItemQuantity(?int $userId, ?string $sessionId, int $cartItemId, int $quantity): array
    {
        $cartId = $this->getOrCreateCartId($userId, $sessionId);

        $cartItem = DB::table('cart_items')
            ->where('id', $cartItemId)
            ->where('cart_id', $cartId)
            ->first();

        if (! $cartItem) {
            throw ValidationException::withMessages([
                'item' => 'Item keranjang tidak ditemukan.',
            ]);
        }

        if ($quantity <= 0) {
            $this->removeItem($userId, $sessionId, $cartItemId);

            return [
                'action' => 'deleted',
                'cart_id' => $cartId,
                'cart_item_id' => $cartItemId,
                'total_items_count' => $this->getItemCount($userId, $sessionId),
            ];
        }

        $product = DB::table('products')
            ->where('id', $cartItem->product_id)
            ->whereNull('deleted_at')
            ->first();

        if (! $product || $product->status !== 'published') {
            throw ValidationException::withMessages([
                'product' => 'Produk laptop tidak lagi tersedia.',
            ]);
        }

        if ($quantity > (int) $product->stock) {
            $available = (int) $product->stock;
            throw ValidationException::withMessages([
                'quantity' => "Kuantitas ({$quantity}) melebihi stok yang tersedia (tersedia: {$available} unit).",
            ]);
        }

        DB::table('cart_items')
            ->where('id', $cartItemId)
            ->update([
                'quantity' => $quantity,
                'updated_at' => now(),
            ]);

        return [
            'action' => 'updated',
            'cart_id' => $cartId,
            'cart_item_id' => $cartItemId,
            'quantity' => $quantity,
            'unit_price' => (float) $product->price,
            'subtotal' => (float) ($product->price * $quantity),
            'total_items_count' => $this->getItemCount($userId, $sessionId),
        ];
    }

    /**
     * Remove a single item from the cart.
     */
    public function removeItem(?int $userId, ?string $sessionId, int $cartItemId): void
    {
        $cartId = $this->getOrCreateCartId($userId, $sessionId);

        DB::table('cart_items')
            ->where('id', $cartItemId)
            ->where('cart_id', $cartId)
            ->delete();
    }

    /**
     * Clear all items from the cart.
     */
    public function clearCart(?int $userId, ?string $sessionId): void
    {
        if ($userId === null && empty($sessionId)) {
            return;
        }

        $query = DB::table('carts');
        if ($userId !== null) {
            $query->where('user_id', $userId);
        } else {
            $query->where('session_id', $sessionId);
        }

        $cart = $query->first();
        if ($cart) {
            DB::table('cart_items')->where('cart_id', $cart->id)->delete();
        }
    }

    /**
     * Get total quantity of items in the cart.
     */
    public function getItemCount(?int $userId, ?string $sessionId): int
    {
        if ($userId === null && empty($sessionId)) {
            return 0;
        }

        $query = DB::table('cart_items')
            ->join('carts', 'cart_items.cart_id', '=', 'carts.id');

        if ($userId !== null) {
            $query->where('carts.user_id', $userId);
        } else {
            $query->where('carts.session_id', $sessionId);
        }

        return (int) $query->sum('cart_items.quantity');
    }

    /**
     * Retrieve complete cart details with product metadata, real-time stock status, and subtotals.
     */
    public function getCartDetails(?int $userId, ?string $sessionId): object
    {
        $emptyResult = (object) [
            'cart_id' => null,
            'items' => collect(),
            'total_items_count' => 0,
            'total_unique_items' => 0,
            'total_weight_grams' => 0,
            'total_weight_kg' => 1,
            'subtotal' => 0.0,
            'has_stock_issues' => false,
            'can_checkout' => false,
        ];

        if ($userId === null && empty($sessionId)) {
            return $emptyResult;
        }

        $cartQuery = DB::table('carts');
        if ($userId !== null) {
            $cartQuery->where('user_id', $userId);
        } else {
            $cartQuery->where('session_id', $sessionId);
        }

        $cart = $cartQuery->first();
        if (! $cart) {
            return $emptyResult;
        }

        $rawItems = DB::table('cart_items')
            ->join('products', 'cart_items.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('product_images', function ($join) {
                $join->on('products.id', '=', 'product_images.product_id')
                    ->where('product_images.is_primary', '=', 1);
            })
            ->where('cart_items.cart_id', $cart->id)
            ->select([
                'cart_items.id as item_id',
                'cart_items.cart_id',
                'cart_items.product_id',
                'cart_items.quantity',
                'cart_items.created_at as added_at',
                'products.name as product_name',
                'products.slug as product_slug',
                'products.sku as product_sku',
                'products.condition as product_condition',
                'products.price as unit_price',
                'products.stock as current_stock',
                'products.weight_grams',
                'products.status as product_status',
                'products.deleted_at',
                'brands.name as brand_name',
                'product_images.image_path as primary_image',
            ])
            ->orderBy('cart_items.id', 'desc')
            ->get();

        $totalCount = 0;
        $totalWeight = 0;
        $subtotal = 0.0;
        $hasStockIssues = false;

        $items = $rawItems->map(function ($item) use (&$totalCount, &$totalWeight, &$subtotal, &$hasStockIssues) {
            $qty = (int) $item->quantity;
            $stock = (int) $item->current_stock;
            $isPublished = $item->product_status === 'published' && $item->deleted_at === null;

            $isOutOfStock = ! $isPublished || $stock <= 0;
            $isStockExceeded = ! $isOutOfStock && $qty > $stock;
            $isValid = ! $isOutOfStock && ! $isStockExceeded;

            if (! $isValid) {
                $hasStockIssues = true;
            }

            $linePrice = (float) $item->unit_price;
            $lineTotal = $linePrice * $qty;
            $lineWeight = ((int) ($item->weight_grams ?: 1500)) * $qty;

            $totalCount += $qty;
            $totalWeight += $lineWeight;
            $subtotal += $lineTotal;

            $item->unit_price = $linePrice;
            $item->line_total = $lineTotal;
            $item->image_url = StorefrontDataService::resolveImageUrl($item->primary_image);
            $item->is_out_of_stock = $isOutOfStock;
            $item->is_stock_exceeded = $isStockExceeded;
            $item->is_valid = $isValid;
            $item->max_available = max(0, $stock);

            return $item;
        });

        $chargeableWeightKg = max(1, (int) ceil($totalWeight / 1000));
        $canCheckout = $items->isNotEmpty() && ! $hasStockIssues;

        return (object) [
            'cart_id' => (int) $cart->id,
            'items' => $items,
            'total_items_count' => $totalCount,
            'total_unique_items' => $items->count(),
            'total_weight_grams' => $totalWeight,
            'total_weight_kg' => $chargeableWeightKg,
            'subtotal' => $subtotal,
            'has_stock_issues' => $hasStockIssues,
            'can_checkout' => $canCheckout,
        ];
    }

    /**
     * Merge guest cart items into customer cart after login or registration.
     */
    public function mergeSessionCartToUser(int $userId, string $sessionId): void
    {
        if (empty($sessionId)) {
            return;
        }

        DB::transaction(function () use ($userId, $sessionId) {
            $guestCart = DB::table('carts')
                ->where('session_id', $sessionId)
                ->whereNull('user_id')
                ->first();

            if (! $guestCart) {
                return;
            }

            $userCart = DB::table('carts')->where('user_id', $userId)->first();

            if (! $userCart) {
                // Point guest cart to user
                DB::table('carts')->where('id', $guestCart->id)->update([
                    'user_id' => $userId,
                    'session_id' => null,
                    'updated_at' => now(),
                ]);

                return;
            }

            // Both exist: merge items into user cart
            $guestItems = DB::table('cart_items')->where('cart_id', $guestCart->id)->get();

            foreach ($guestItems as $gItem) {
                $userItem = DB::table('cart_items')
                    ->where('cart_id', $userCart->id)
                    ->where('product_id', $gItem->product_id)
                    ->first();

                $product = DB::table('products')->where('id', $gItem->product_id)->first();
                $maxStock = $product ? (int) $product->stock : 99;

                if ($userItem) {
                    $newQty = min($maxStock, (int) $userItem->quantity + (int) $gItem->quantity);
                    DB::table('cart_items')->where('id', $userItem->id)->update([
                        'quantity' => max(1, $newQty),
                        'updated_at' => now(),
                    ]);
                } else {
                    $insertQty = min($maxStock, (int) $gItem->quantity);
                    if ($insertQty > 0) {
                        DB::table('cart_items')->insert([
                            'cart_id' => $userCart->id,
                            'product_id' => $gItem->product_id,
                            'quantity' => $insertQty,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            // Clean up guest cart
            DB::table('cart_items')->where('cart_id', $guestCart->id)->delete();
            DB::table('carts')->where('id', $guestCart->id)->delete();
        });
    }
}

