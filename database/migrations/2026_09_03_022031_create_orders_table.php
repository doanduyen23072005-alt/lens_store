<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('address');
    $table->string('phone');

    $table->decimal('subtotal', 15, 2)->default(0);       // tien hang, chua ship
    $table->decimal('total_price', 15, 2);                // tien hang + phi ship
    $table->string('status')->default('pending');         // pending, paid, cod_ordered, failed, cancelled
    $table->string('payment_method', 20)->default('cod'); // cod | momo
    $table->string('shipping_status')->default('not_shipped');
    $table->timestamp('paid_at')->nullable();

    // GHN
    $table->string('ghn_order_code')->nullable()->index();
    $table->integer('ghn_total_fee')->default(0);
    $table->integer('to_province_id')->nullable();
    $table->integer('to_district_id')->nullable();
    $table->string('to_ward_code')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
