<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class StoreStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    #[On('product-created')]
    #[On('product-updated')]
    public function refreshStats(): void
    {
        // Trigger Livewire re-render when QuickAddProductWidget or LatestProductsWidget mutates products
    }

    protected function getStats(): array
    {
        $activeProducts = Product::where('is_active', true)->count();
        $totalProducts = Product::count();
        $lowStock = Product::where('is_active', true)->where('stok', '<=', 3)->count();
        $newOrders = Order::where('status', 'baru')->count();
        $revenue = (int) Order::where('status', '!=', 'batal')->sum('grand_total');
        $paidRevenue = (int) Order::where('payment_status', 'paid')->sum('grand_total');

        $catSummary = Category::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('id')
            ->get()
            ->map(fn (Category $c) => "{$c->nama}: {$c->products_count}")
            ->implode(' · ');

        return [
            Stat::make('Produk Tayang di Web', $activeProducts.' / '.$totalProducts)
                ->description($catSummary ?: 'Belum ada kategori')
                ->icon('heroicon-o-swatch')
                ->color('primary')
                ->url(ProductResource::getUrl('index')),

            Stat::make('Stok Menipis (≤ 3)', $lowStock)
                ->description($lowStock > 0 ? 'Perlu segera diisi ulang' : 'Semua stok aman')
                ->icon('heroicon-o-archive-box')
                ->color($lowStock > 0 ? 'danger' : 'success')
                ->url(ProductResource::getUrl('index')),

            Stat::make('Pesanan Baru', $newOrders)
                ->description($newOrders > 0 ? 'Menunggu konfirmasi admin' : 'Tidak ada antrean baru')
                ->icon('heroicon-o-shopping-bag')
                ->color($newOrders > 0 ? 'warning' : 'gray')
                ->url(OrderResource::getUrl('index')),

            Stat::make('Total Penjualan', 'Rp '.number_format($revenue, 0, ',', '.'))
                ->description('Lunas terverifikasi: Rp '.number_format($paidRevenue, 0, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->url(OrderResource::getUrl('index')),
        ];
    }
}
