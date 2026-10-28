<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Services\Payment\MidtransService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected MidtransService $midtransService
    ) {}

    /**
     * Display a paginated listing of customer orders with status filters.
     */
    public function index(Request $request): View
    {
        $userId = (int) Auth::id();
        $statusFilter = $request->query('status');

        $orders = $this->orderService->getCustomerOrders($userId, $statusFilter, 10);

        $statusCounts = DB::table('orders')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalCount = array_sum($statusCounts);

        return view('store.orders.index', [
            'orders' => $orders,
            'currentStatus' => $statusFilter,
            'statusCounts' => $statusCounts,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Display the specified order details with snapshots, shipping, and payment status.
     */
    public function show(Request $request, string $orderNumber): View
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

        return view('store.orders.show', [
            'order' => $order,
            'snapToken' => $snapData['token'] ?? null,
            'snapJsUrl' => $snapData['snap_js_url'] ?? $this->midtransService->getSnapJsUrl(),
            'clientKey' => $snapData['client_key'] ?? $this->midtransService->getClientKey(),
        ]);
    }

    /**
     * Cancel a pending order requested by the customer.
     */
    public function cancel(Request $request, string $orderNumber): RedirectResponse
    {
        $userId = (int) Auth::id();
        $reason = $request->input('reason');

        try {
            $this->orderService->cancelOrderByCustomer($orderNumber, $userId, $reason);

            return redirect()->back()->with('success', 'Pesanan ' . $orderNumber . ' berhasil dibatalkan dan stok laptop telah dikembalikan ke toko.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }
}

