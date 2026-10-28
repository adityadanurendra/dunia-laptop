<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\ProductManagementController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Payment\MidtransWebhookController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Store\AddressController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\CustomerOrderController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\ProductCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductCatalogController::class, 'index'])->name('catalog.index');
Route::get('/category/{categorySlug}', [ProductCatalogController::class, 'category'])->name('catalog.category');
Route::get('/products/{slug}', [ProductCatalogController::class, 'show'])->name('catalog.show');

// Shopping Cart Routes (Guests & Authenticated Customers)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/items/{id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{id}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:super_admin,admin,staff'])->prefix('admin')->name('admin.')->group(function () {
    // 7.1 Dashboard Ringkasan
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // 7.4 Manajemen Produk Laptop (Staff, Admin, Super Admin)
    Route::get('/products', [ProductManagementController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductManagementController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductManagementController::class, 'store'])->name('products.store');
    Route::get('/products/{id}/edit', [ProductManagementController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ProductManagementController::class, 'update'])->name('products.update');
    Route::patch('/products/{id}/quick-update', [ProductManagementController::class, 'quickUpdate'])->name('products.quick-update');
    Route::delete('/products/{id}', [ProductManagementController::class, 'destroy'])->name('products.destroy');
    Route::delete('/products/{id}/images/{imageId}', [ProductManagementController::class, 'deleteImage'])->name('products.images.destroy');
    Route::post('/products/{id}/images/{imageId}/primary', [ProductManagementController::class, 'setPrimaryImage'])->name('products.images.primary');

    // Akses Khusus Admin & Super Admin (Staff dibatasi)
    Route::middleware('role:super_admin,admin')->group(function () {
        // 7.2 Manajemen Pesanan & Pengiriman
        Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders.index');
        Route::get('/orders/{orderNumber}', [OrderManagementController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{orderNumber}/status', [OrderManagementController::class, 'updateStatus'])->name('orders.update-status');
        Route::patch('/orders/{orderNumber}/shipment', [OrderManagementController::class, 'updateShipment'])->name('orders.update-shipment');

        // 7.3 Laporan Penjualan
        Route::get('/reports/sales', [SalesReportController::class, 'index'])->name('reports.sales');
        Route::get('/reports/sales/print', [SalesReportController::class, 'print'])->name('reports.sales.print');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customer Shipping Addresses Management
    Route::get('/addresses', [AddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{id}', [AddressController::class, 'update'])->name('addresses.update');
    Route::post('/addresses/{id}/default', [AddressController::class, 'setDefault'])->name('addresses.default');
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');

    // Transactional Checkout Routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');

    // Customer Order History & Cancellation
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{orderNumber}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');

    // Order Payment & Snap Modal
    Route::get('/orders/{orderNumber}/payment', [PaymentController::class, 'show'])->name('payment.show');
});

// Midtrans Payment Callbacks (Redirects from Snap Dialog)
Route::get('/payment/finish', [PaymentController::class, 'finish'])->name('payment.finish');
Route::get('/payment/unfinish', [PaymentController::class, 'unfinish'])->name('payment.unfinish');
Route::get('/payment/error', [PaymentController::class, 'error'])->name('payment.error');

// Midtrans Official Asynchronous Webhook (CSRF Exempt in bootstrap/app.php)
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle'])->name('midtrans.notification');

require __DIR__.'/auth.php';
