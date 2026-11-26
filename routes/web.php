<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
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
    Route::post('/checkout', 'processCheckout')->name('checkout.process');
    Route::get('/pesanan', 'orders')->name('orders.index');
    Route::get('/pesanan/{orderCode}', 'orderShow')->name('order.show');
    Route::post('/pesanan/{orderCode}/selesai', 'completeOrder')->name('order.complete');
    Route::post('/pesanan/{orderCode}/ulasan', 'storeReview')->name('order.review');
    Route::get('/bantuan/{topic?}', 'help')->name('help');
    Route::get('/api/cart-sync', 'cartSync')->name('api.cart.sync');
    Route::get('/storage/{path}', 'publicFile')->where('path', '.*')->name('storage.public');
});

Route::get('/dashboard', function () {
    $orders = \App\Models\Order::with('items.product:id,nama,slug')
        ->where('user_id', auth()->id())
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
