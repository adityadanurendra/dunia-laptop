<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cartService,
        protected OrderService $orderService,
        protected MidtransService $midtransService
    ) {}

    /**
     * Display the checkout page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $userId = (int) Auth::id();
        $sessionId = $request->session()->getId();

        // Merge any guest cart items if present
        $this->cartService->mergeSessionCartToUser($userId, $sessionId);

        $cart = $this->cartService->getCartDetails($userId, $sessionId);

        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjang belanja Anda masih kosong. Silakan pilih produk terlebih dahulu.');
        }

        if ($cart->has_stock_issues) {
            return redirect()->route('cart.index')->with('error', 'Terdapat produk dengan stok habis atau melebihi batas di keranjang Anda. Harap sesuaikan kuantitas terlebih dahulu.');
        }

        $addresses = DB::table('addresses')
            ->where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $courierOptions = $this->orderService->getCourierOptions($cart->total_weight_grams);

        return view('store.checkout.index', [
            'cart' => $cart,
            'addresses' => $addresses,
            'courierOptions' => $courierOptions,
        ]);
    }

    /**
     * Process checkout and create order inside ACID transaction with pessimistic row-locking.
     */
    public function process(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address_id' => ['required', 'integer'],
            'courier' => ['required', 'string'],
            'customer_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $userId = (int) Auth::id();
        $courierParts = explode('_', (string) $validated['courier'], 2);
        $courierCode = $courierParts[0] ?? 'jne';
        $courierService = $courierParts[1] ?? 'REG';

        try {
            $order = $this->orderService->createOrderFromCart(
                $userId,
                (int) $validated['address_id'],
                $courierCode,
                $courierService,
                $validated['customer_notes'] ?? null
            );

            return redirect()->route('checkout.success', $order->order_number)
                ->with('success', 'Pesanan Anda berhasil dibuat dan stok produk telah diamankan!');
        } catch (ValidationException $e) {
            return redirect()->back()->withInput()->withErrors($e->errors());
        }
    }

    /**
     * Display the order creation success page.
     */
    public function success(Request $request, string $orderNumber): View
    {
        $userId = (int) Auth::id();
        $order = $this->orderService->getOrderDetails($orderNumber, $userId);

        if (! $order) {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        $snapData = [];
        if ($order->status === 'pending') {
            $snapData = $this->midtransService->createSnapTransaction($order);
        }

        return view('store.checkout.success', [
            'order' => $order,
            'snapToken' => $snapData['token'] ?? null,
            'snapJsUrl' => $snapData['snap_js_url'] ?? $this->midtransService->getSnapJsUrl(),
            'clientKey' => $snapData['client_key'] ?? $this->midtransService->getClientKey(),
        ]);
    }
}

