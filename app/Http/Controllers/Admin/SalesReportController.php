<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    /**
     * Display the sales report dashboard with date range filter and metrics.
     */
    public function index(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $selectedStatus = $request->query('status', 'valid');

        $statusList = match ($selectedStatus) {
            'completed' => ['completed'],
            'processing' => ['processing'],
            'shipped' => ['shipped'],
            'all' => ['pending', 'processing', 'shipped', 'completed', 'cancelled'],
            default => ['processing', 'shipped', 'completed'], // valid orders
        };

        $startDateTime = "{$startDate} 00:00:00";
        $endDateTime = "{$endDate} 23:59:59";

        // 1. Total revenue
        $totalRevenue = (float) DB::table('orders')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNull('deleted_at')
            ->whereIn('status', $statusList)
            ->sum('total_amount');

        // 2. Total orders count
        $totalOrdersCount = DB::table('orders')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNull('deleted_at')
            ->whereIn('status', $statusList)
            ->count();

        // 3. Total laptop items sold
        $totalItemsSold = (int) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDateTime, $endDateTime])
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', $statusList)
            ->sum('order_items.quantity');

        // 4. Average order value
        $averageOrderValue = $totalOrdersCount > 0 ? ($totalRevenue / $totalOrdersCount) : 0;

        // 5. Top selling laptops leaderboard
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDateTime, $endDateTime])
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', $statusList)
            ->select(
                'order_items.product_name',
                'order_items.product_sku',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_sales')
            )
            ->groupBy('order_items.product_name', 'order_items.product_sku')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // 6. Daily sales breakdown
        $dailySales = DB::table('orders')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNull('deleted_at')
            ->whereIn('status', $statusList)
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('COUNT(id) as orders_count'),
                DB::raw('SUM(total_amount) as daily_total')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('sale_date', 'desc')
            ->get();

        // 7. Paginated transactions ledger
        $ordersList = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
            ->whereBetween('orders.created_at', [$startDateTime, $endDateTime])
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', $statusList)
            ->select(
                'orders.*',
                'users.name as customer_name',
                'users.email as customer_email',
                'payments.transaction_status as payment_status',
                'payments.payment_type'
            )
            ->orderBy('orders.id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.sales', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedStatus' => $selectedStatus,
            'totalRevenue' => $totalRevenue,
            'totalOrdersCount' => $totalOrdersCount,
            'totalItemsSold' => $totalItemsSold,
            'averageOrderValue' => $averageOrderValue,
            'topProducts' => $topProducts,
            'dailySales' => $dailySales,
            'ordersList' => $ordersList,
        ]);
    }

    /**
     * Display a clean, print-ready sales ledger report.
     */
    public function print(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->toDateString());
        $selectedStatus = $request->query('status', 'valid');

        $statusList = match ($selectedStatus) {
            'completed' => ['completed'],
            'processing' => ['processing'],
            'shipped' => ['shipped'],
            'all' => ['pending', 'processing', 'shipped', 'completed', 'cancelled'],
            default => ['processing', 'shipped', 'completed'],
        };

        $startDateTime = "{$startDate} 00:00:00";
        $endDateTime = "{$endDate} 23:59:59";

        $totalRevenue = (float) DB::table('orders')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNull('deleted_at')
            ->whereIn('status', $statusList)
            ->sum('total_amount');

        $totalOrdersCount = DB::table('orders')
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->whereNull('deleted_at')
            ->whereIn('status', $statusList)
            ->count();

        $totalItemsSold = (int) DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDateTime, $endDateTime])
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', $statusList)
            ->sum('order_items.quantity');

        $allOrders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
            ->whereBetween('orders.created_at', [$startDateTime, $endDateTime])
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', $statusList)
            ->select(
                'orders.*',
                'users.name as customer_name',
                'payments.transaction_status as payment_status'
            )
            ->orderBy('orders.id', 'asc')
            ->get();

        return view('admin.reports.print', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedStatus' => $selectedStatus,
            'totalRevenue' => $totalRevenue,
            'totalOrdersCount' => $totalOrdersCount,
            'totalItemsSold' => $totalItemsSold,
            'allOrders' => $allOrders,
        ]);
    }
}

