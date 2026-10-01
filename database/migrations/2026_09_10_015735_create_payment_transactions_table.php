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
    Schema::create('payment_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        $table->string('gateway');                              // cod | momo
        $table->string('gateway_order_id')->nullable()->index(); // orderId gui sang MoMo
        $table->string('transaction_id')->nullable()->index();   // transId MoMo tra ve
        $table->decimal('amount', 15, 2);
        $table->string('status')->default('pending');            // pending|initiated|paid|failed
        $table->integer('result_code')->nullable();
        $table->string('message')->nullable();
        $table->json('request_payload')->nullable();
        $table->json('response_payload')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->timestamps();

        $table->unique(['gateway', 'gateway_order_id']);
        $table->index(['order_id', 'status']);
    });
}
};
