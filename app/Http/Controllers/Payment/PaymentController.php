<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
        protected OrderService $orderService
    ) {}

    /**
     * Display order review & payment trigger page.
     */
    public function show(Request $request, string $orderNumber): View|RedirectResponse
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

    /**
     * Callback when customer completes the Snap payment dialog.
     * Note: Per FR-24, status will not change to processing until webhook notification is verified.
     */
    public function finish(Request $request): RedirectResponse
    {
        $orderNumber = $request->query('order_id');

        if (! empty($orderNumber)) {
            return redirect()->route('checkout.success', $orderNumber)->with(
                'info',
                'Pembayaran telah dikirim. Kami sedang menunggu konfirmasi notifikasi resmi dari gerbang pembayaran Midtrans.'
            );
        }

        return redirect()->route('home')->with('info', 'Pembayaran telah diselesaikan.');
    }

    /**
     * Callback when customer closes Snap modal before completing payment.
     */
    public function unfinish(Request $request): RedirectResponse
    {
        $orderNumber = $request->query('order_id');

        if (! empty($orderNumber)) {
            return redirect()->route('checkout.success', $orderNumber)->with(
                'warning',
                'Pembayaran belum diselesaikan. Anda dapat membuka kembali jendela pembayaran menggunakan tombol di bawah ini.'
            );
        }

        return redirect()->route('home');
    }

    /**
     * Callback when payment provider encounters an error.
     */
    public function error(Request $request): RedirectResponse
    {
        $orderNumber = $request->query('order_id');

        if (! empty($orderNumber)) {
            return redirect()->route('checkout.success', $orderNumber)->with(
                'error',
                'Terjadi kesalahan atau kendala pada transaksi pembayaran. Silakan pilih kembali metode pembayaran Anda.'
            );
        }

        return redirect()->route('home');
    }
}

