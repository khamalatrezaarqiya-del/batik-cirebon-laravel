<?php

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_motif_check');
            DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_payment_method_check');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('motif', 40)->default('lainnya')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 40)->default('transfer')->change();
        });

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
        // Kolom string tetap dipertahankan agar data motif pesisiran (singaan) & pembayaran (dp50) tetap aman.
    }
};
