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
            if (! Schema::hasColumn('products', 'bahan')) {
                $table->string('bahan', 40)->nullable()->index()->after('motif');
            }
            if (! Schema::hasColumn('products', 'teknik')) {
                $table->string('teknik', 40)->nullable()->index()->after('bahan');
            }
            if (! Schema::hasColumn('products', 'gender')) {
                $table->string('gender', 20)->nullable()->index()->after('teknik');
            }
        });

        // Isi atribut awal bahan, teknik & model pada produk yang sudah ada agar filter langsung berfungsi
        if (Schema::hasTable('products')) {
            $products = DB::table('products')
                ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
                ->select('products.id', 'products.nama', 'products.harga', 'products.bahan', 'products.teknik', 'products.gender', 'categories.slug as cat_slug')
                ->get();

            foreach ($products as $p) {
                $updates = [];
                $lower = mb_strtolower((string) $p->nama);

                if ($p->bahan === null) {
                    $updates['bahan'] = match (true) {
                        str_contains($lower, 'atbm') || (int) $p->harga >= 1000000 => 'atbm',
                        str_contains($lower, 'viscose') || str_contains($lower, 'selendang') || str_contains($lower, 'syal') || str_contains($lower, 'dress') || str_contains($lower, 'blouse') => 'viscose',
                        str_contains($lower, 'doby') || str_contains($lower, 'panjang') || str_contains($lower, 'set ') => 'doby',
                        default => 'katun',
                    };
                }

                if ($p->teknik === null) {
                    $updates['teknik'] = match (true) {
                        str_contains($lower, 'tulis') || (int) $p->harga >= 450000 => 'tulis',
                        (int) $p->harga >= 270000 => 'kombinasi',
                        default => 'cap',
                    };
                }

                if ($p->gender === null) {
                    $updates['gender'] = match (true) {
                        str_contains($lower, 'kemeja') || str_contains($lower, 'sarung') || str_contains($lower, 'pria') => 'pria',
                        str_contains($lower, 'blouse') || str_contains($lower, 'dress') || str_contains($lower, 'hijab') || str_contains($lower, 'selendang') || str_contains($lower, 'wanita') => 'wanita',
                        default => 'unisex',
                    };
                }

                if (! empty($updates)) {
                    DB::table('products')->where('id', $p->id)->update($updates);
                }
            }
        }

        // Default pengaturan metode pembayaran (Transfer & DP 50% aktif, QRIS & COD bisa ON/OFF dari admin)
        if (! app()->runningUnitTests() && Schema::hasTable('site_settings')) {
            $defaults = [
                'pay_transfer_enabled' => '1',
                'pay_dp50_enabled' => '1',
                'pay_qris_enabled' => '0',
                'pay_cod_enabled' => '0',
                'pay_dp50_note' => 'Khusus pesanan bernominal besar, seragam, atau custom. Transfer DP 50% terlebih dahulu dan pelunasan dilakukan sebelum pengiriman.',
            ];

            foreach ($defaults as $key => $val) {
                $exists = DB::table('site_settings')->where('key', $key)->exists();
                if (! $exists) {
                    DB::table('site_settings')->insert([
                        'key' => $key,
                        'value' => $val,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = array_values(array_filter(
                ['bahan', 'teknik', 'gender'],
                fn (string $col) => Schema::hasColumn('products', $col)
            ));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
