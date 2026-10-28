<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Store\ProductCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductCatalogController::class, 'index'])->name('catalog.index');
Route::get('/category/{categorySlug}', [ProductCatalogController::class, 'category'])->name('catalog.category');
Route::get('/products/{slug}', [ProductCatalogController::class, 'show'])->name('catalog.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:super_admin,admin,staff'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
