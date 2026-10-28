<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\ShipmentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    /**
     * Display a paginated listing of customer orders with filters.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');
        $paymentStatus = $request->query('payment_status');

        // Status counts for interactive tabs
        $statusCounts = [
            'all' => DB::table('orders')->whereNull('deleted_at')->count(),
            'pending' => DB::table('orders')->whereNull('deleted_at')->where('status', 'pending')->count(),
            'processing' => DB::table('orders')->whereNull('deleted_at')->where('status', 'processing')->count(),
            'shipped' => DB::table('orders')->whereNull('deleted_at')->where('status', 'shipped')->count(),
            'completed' => DB::table('orders')->whereNull('deleted_at')->where('status', 'completed')->count(),
            'cancelled' => DB::table('orders')->whereNull('deleted_at')->where('status', 'cancelled')->count(),
        ];

        $query = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('shipments', 'orders.id', '=', 'shipments.order_id')
            ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
            ->whereNull('orders.deleted_at')
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.subtotal',
                'orders.shipping_cost',
                'orders.discount_amount',
                'orders.total_amount',
                'orders.status as order_status',
                'orders.created_at',
                'users.name as customer_name',
                'users.email as customer_email',
                'shipments.courier_code',
                'shipments.courier_service',
                'shipments.tracking_number',
                'shipments.status as shipment_status',
                'payments.transaction_status as payment_status',
                'payments.payment_type'
            );

        if ($status && in_array($status, ['pending', 'processing', 'shipped', 'completed', 'cancelled'])) {
            $query->where('orders.status', $status);
        }

        if ($paymentStatus) {
            $query->where('payments.transaction_status', $paymentStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('orders.order_number', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('shipments.tracking_number', 'like', "%{$search}%");
            });
        }

        $orders = $query->orderBy('orders.id', 'desc')->paginate(15)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statusCounts' => $statusCounts,
            'currentStatus' => $status,
            'search' => $search,
            'paymentStatus' => $paymentStatus,
        ]);
    }

    /**
     * Display the specified order details and fulfillment tools.
     */
    public function show(string $orderNumber): View
    {
        $order = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.order_number', $orderNumber)
            ->whereNull('orders.deleted_at')
            ->select(
                'orders.*',
                'users.name as customer_name',
                'users.email as customer_email',
                'users.phone as customer_phone'
            )
            ->first();

        if (! $order) {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        $items = DB::table('order_items')
            ->where('order_id', $order->id)
            ->get();

        $shipment = DB::table('shipments')
            ->where('order_id', $order->id)
            ->first();

        $payment = DB::table('payments')
            ->where('order_id', $order->id)
            ->first();

        return view('admin.orders.show', [
            'order' => $order,
            'items' => $items,
            'shipment' => $shipment,
            'payment' => $payment,
        ]);
    }

    /**
     * Update the processing status of the order.
     */
    public function updateStatus(Request $request, string $orderNumber): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $newStatus = $validated['status'];

        DB::transaction(function () use ($orderNumber, $newStatus) {
            $order = DB::table('orders')
                ->where('order_number', $orderNumber)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if (! $order) {
                abort(404, 'Pesanan tidak ditemukan.');
            }

            // If changing to cancelled from an uncancelled state, atomically restore stock
            if ($newStatus === 'cancelled' && $order->status !== 'cancelled') {
                $items = DB::table('order_items')->where('order_id', $order->id)->get();
                foreach ($items as $item) {
                    DB::table('products')
                        ->where('id', $item->product_id)
                        ->increment('stock', $item->quantity);
                }
            }

            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now(),
                ]);
        });

        return redirect()->route('admin.orders.show', $orderNumber)
            ->with('success', "Status pesanan {$orderNumber} berhasil diperbarui menjadi {$newStatus}.");
    }

    /**
     * Update shipment details (courier, tracking number, shipment status).
     */
    public function updateShipment(Request $request, string $orderNumber): RedirectResponse
    {
        $validated = $request->validate([
            'courier_code' => 'required|string|max:30',
            'courier_service' => 'required|string|max:50',
            'tracking_number' => 'nullable|string|max:100',
            'shipment_status' => 'required|in:pending,picked_up,in_transit,delivered,returned',
        ]);

        DB::transaction(function () use ($orderNumber, $validated) {
            $order = DB::table('orders')
                ->where('order_number', $orderNumber)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if (! $order) {
                abort(404, 'Pesanan tidak ditemukan.');
            }

            $shipment = DB::table('shipments')
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();

            if (! $shipment) {
                abort(404, 'Data pengiriman pesanan tidak ditemukan.');
            }

            $shipmentUpdate = [
                'courier_code' => strtoupper($validated['courier_code']),
                'courier_service' => $validated['courier_service'],
                'tracking_number' => $validated['tracking_number'] ? trim($validated['tracking_number']) : null,
                'status' => $validated['shipment_status'],
                'updated_at' => now(),
            ];

            // Set timestamps when status moves
            if (in_array($validated['shipment_status'], ['picked_up', 'in_transit']) && empty($shipment->shipped_at)) {
                $shipmentUpdate['shipped_at'] = now();
            }

            if ($validated['shipment_status'] === 'delivered' && empty($shipment->delivered_at)) {
                $shipmentUpdate['delivered_at'] = now();
            }

            DB::table('shipments')->where('id', $shipment->id)->update($shipmentUpdate);

            // Synchronize order status
            if ($validated['shipment_status'] === 'delivered' && $order->status !== 'completed') {
                DB::table('orders')->where('id', $order->id)->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);
            } elseif (in_array($validated['shipment_status'], ['picked_up', 'in_transit']) && $order->status === 'processing') {
                DB::table('orders')->where('id', $order->id)->update([
                    'status' => 'shipped',
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.orders.show', $orderNumber)
            ->with('success', "Informasi pengiriman dan resi untuk pesanan {$orderNumber} berhasil disimpan.");
    }
}

