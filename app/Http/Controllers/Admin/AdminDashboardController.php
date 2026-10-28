<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display a summary of backoffice operational metrics.
     */
    public function index(Request $request): View
    {
        // 1. Total valid sales revenue (processing, shipped, completed or paid settlement)
        $validSales = (float) DB::table('orders')
            ->whereIn('status', ['processing', 'shipped', 'completed'])
            ->sum('total_amount');

        // 2. Count of new/actionable orders (pending or processing)
        $newOrdersCount = DB::table('orders')
            ->whereIn('status', ['pending', 'processing'])
            ->count();

        $pendingOrdersCount = DB::table('orders')
            ->where('status', 'pending')
            ->count();

        $processingOrdersCount = DB::table('orders')
            ->where('status', 'processing')
            ->count();

        $completedOrdersCount = DB::table('orders')
            ->where('status', 'completed')
            ->count();

        // 3. Count of products with critical low stock (stock <= 2)
        $lowStockCount = DB::table('products')
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->where('stock', '<=', 2)
            ->count();

        // 4. Total active published products
        $totalProductsCount = DB::table('products')
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->count();

        // 5. Recent orders summary with customer & payment details
        $recentOrders = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.total_amount',
                'orders.status as order_status',
                'orders.created_at',
                'users.name as customer_name',
                'users.email as customer_email',
                'payments.transaction_status as payment_status',
                'payments.payment_type'
            )
            ->orderBy('orders.id', 'desc')
            ->limit(6)
            ->get();

        // 6. Products that need restocking (stock <= 5)
        $lowStockProducts = DB::table('products')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->whereNull('products.deleted_at')
            ->where('products.stock', '<=', 5)
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.condition',
                'products.stock',
                'products.price',
                'brands.name as brand_name'
            )
            ->orderBy('products.stock', 'asc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'validSales' => $validSales,
            'newOrdersCount' => $newOrdersCount,
            'pendingOrdersCount' => $pendingOrdersCount,
            'processingOrdersCount' => $processingOrdersCount,
            'completedOrdersCount' => $completedOrdersCount,
            'lowStockCount' => $lowStockCount,
            'totalProductsCount' => $totalProductsCount,
            'recentOrders' => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}

