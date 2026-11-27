<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Models\Order;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Storefront (Batik Daffa)
|--------------------------------------------------------------------------
*/
Route::controller(ShopController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/katalog', 'catalog')->name('catalog');
    Route::get('/produk/{slug}', 'show')->name('product.detail');
    Route::get('/keranjang', 'cart')->name('cart');
    Route::get('/checkout', 'checkoutPage')->name('checkout');
    Route::post('/checkout', 'processCheckout')
        ->middleware('throttle:checkout')
        ->name('checkout.process');
    Route::get('/pesanan', 'orders')
        ->middleware('throttle:order-lookup')
        ->name('orders.index');
    Route::get('/pesanan/{orderCode}', 'orderShow')
        ->middleware('throttle:order-lookup')
        ->name('order.show');
    Route::post('/pesanan/{orderCode}/verifikasi', 'verifyGuestOrder')
        ->middleware('throttle:order-verify')
        ->name('order.verify');
    Route::post('/pesanan/{orderCode}/selesai', 'completeOrder')
        ->middleware('throttle:order-mutation')
        ->name('order.complete');
    Route::post('/pesanan/{orderCode}/ulasan', 'storeReview')
        ->middleware('throttle:order-mutation')
        ->name('order.review');
    Route::get('/bantuan/{topic?}', 'help')->name('help');
    Route::get('/tentang', 'about')->name('about');
    Route::get('/api/cart-sync', 'cartSync')
        ->middleware('throttle:cart-sync')
        ->name('api.cart.sync');
    Route::get('/storage/{path}', 'publicFile')->where('path', '.*')->name('storage.public');
});

Route::get('/admin-nota/{order:id}', [ShopController::class, 'orderNota'])
    ->middleware('auth:admin')
    ->name('admin.orders.nota');

Route::get('/dashboard', function () {
    $orders = Order::with('items.product:id,nama,slug')
        ->where('user_id', auth('web')->id())
        ->latest()
        ->limit(5)
        ->get();

    return Inertia::render('Dashboard', [
        'orders' => $orders,
    ]);
})->middleware(['auth:web', 'verified'])->name('dashboard');

Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
