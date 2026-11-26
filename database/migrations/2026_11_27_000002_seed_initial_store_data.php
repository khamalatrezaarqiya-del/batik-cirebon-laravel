<?php

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $needsSeed = Product::count() === 0
            || Category::count() === 0
            || HeroSlide::count() === 0
            || User::where('is_admin', true)->doesntExist();

        if ($needsSeed) {
            (new DatabaseSeeder())->run();
        }
    }

    public function down(): void
    {
        // Data awal toko sengaja tidak dihapus saat rollback agar tidak merusak data produksi.
    }
};
