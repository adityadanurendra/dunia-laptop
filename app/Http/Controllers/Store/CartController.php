<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    /**
     * Display the shopping cart page.
     */
    public function index(Request $request): View
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $cart = $this->cartService->getCartDetails($userId, $sessionId);

        return view('store.cart.index', [
            'cart' => $cart,
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'buy_now' => ['nullable', 'boolean'],
        ]);

        $userId = Auth::id();
        $sessionId = $request->session()->getId();
        $quantity = (int) ($validated['quantity'] ?? 1);
        $buyNow = filter_var($validated['buy_now'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $result = $this->cartService->addItem($userId, $sessionId, (int) $validated['product_id'], $quantity);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Laptop berhasil ditambahkan ke keranjang.',
                'data' => $result,
            ]);
        }

        if ($buyNow) {
            return redirect()->route('checkout.index');
        }

        return redirect()->route('cart.index')->with('success', 'Laptop berhasil ditambahkan ke keranjang belanja.');
    }

    /**
     * Update the quantity of a specific cart item.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $result = $this->cartService->updateItemQuantity(
            $userId,
            $sessionId,
            $id,
            (int) $validated['quantity']
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kuantitas keranjang berhasil diperbarui.',
                'data' => $result,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Keranjang belanja berhasil diperbarui.');
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $this->cartService->removeItem($userId, $sessionId, $id);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus dari keranjang.',
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    /**
     * Empty the entire shopping cart.
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $userId = Auth::id();
        $sessionId = $request->session()->getId();

        $this->cartService->clearCart($userId, $sessionId);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Keranjang berhasil dikosongkan.',
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Keranjang belanja Anda telah dikosongkan.');
    }
}

