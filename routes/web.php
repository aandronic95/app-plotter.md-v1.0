<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::get('products/{slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// Cart routes
Route::get('cart', function () {
    return Inertia::render('Cart');
})->name('cart.index');
Route::post('cart/add', [CartController::class, 'add'])->name('cart.add');
Route::put('cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::get('cart/data', [CartController::class, 'index'])->name('cart.data');

// Order routes
Route::get('checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::post('orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
