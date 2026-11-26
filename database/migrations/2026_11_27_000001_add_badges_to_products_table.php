<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'is_best_seller')) {
                $table->boolean('is_best_seller')->default(false)->index()->after('is_active');
            }
            if (! Schema::hasColumn('products', 'is_new')) {
                $table->boolean('is_new')->default(false)->index()->after('is_best_seller');
            }
        });

        // Isi label awal pada data katalog yang sudah ada agar beranda & katalog langsung konsisten,
        // dan admin dapat bebas mengubahnya kapan saja dari dashboard.
        if (DB::table('products')->where('is_active', true)->exists()) {
            $bestIds = DB::table('products')
                ->where('is_active', true)
                ->orderBy('id')
                ->limit(8)
                ->pluck('id')
                ->all();

            if (! empty($bestIds)) {
                DB::table('products')->whereIn('id', $bestIds)->update(['is_best_seller' => true]);
            }

            $newIds = DB::table('products')
                ->where('is_active', true)
                ->orderByDesc('id')
                ->limit(4)
                ->pluck('id')
                ->all();

            if (! empty($newIds)) {
                DB::table('products')->whereIn('id', $newIds)->update(['is_new' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = array_values(array_filter(
                ['is_best_seller', 'is_new'],
                fn (string $col) => Schema::hasColumn('products', $col)
            ));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
