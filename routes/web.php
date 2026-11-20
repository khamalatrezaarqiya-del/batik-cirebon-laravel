<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Storefront (Batik Cirebon)
|--------------------------------------------------------------------------
*/
Route::controller(ShopController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/katalog', 'catalog')->name('catalog');
    Route::get('/produk/{slug}', 'show')->name('product.detail');
    Route::get('/keranjang', 'cart')->name('cart');
    Route::get('/checkout', 'checkoutPage')->name('checkout');
    Route::post('/checkout', 'processCheckout')->name('checkout.process');
    Route::get('/pesanan/{orderCode}', 'orderShow')->name('order.show');
    Route::get('/bantuan/{topic?}', 'help')->name('help');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
