<?php

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Database\Seeders\StoreVisualSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests() || ! Schema::hasColumn('products', 'bahan')) {
            return;
        }

        if (Product::count() === 0 || Category::count() === 0) {
            (new ProductSeeder)->run();
        }

        if (HeroSlide::count() === 0) {
            (new StoreVisualSeeder)->run();
        }
    }

    public function down(): void
    {
        // Data awal toko sengaja tidak dihapus saat rollback agar tidak merusak data produksi.
    }
};
