<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_name');
            $table->string('phone');
            $table->text('shipping_address');
            $table->text('note')->nullable();
            $table->enum('payment_method', ['transfer', 'ewallet', 'cod'])->default('transfer');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('shipping_cost')->default(0);
            $table->unsignedInteger('grand_total');
            $table->enum('status', ['baru', 'diproses', 'dikirim', 'selesai', 'batal'])->default('baru');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
