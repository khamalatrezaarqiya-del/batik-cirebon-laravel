<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status', 32)->default('pending')->index()->after('payment_method');
            }
            if (! Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('orders', 'stock_restored_at')) {
                $table->timestamp('stock_restored_at')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('orders', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->unique()->after('order_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'idempotency_key')) {
                $table->dropUnique(['idempotency_key']);
            }
            $cols = array_values(array_filter(
                ['payment_status', 'paid_at', 'stock_restored_at', 'idempotency_key'],
                fn (string $col) => Schema::hasColumn('orders', $col)
            ));
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
