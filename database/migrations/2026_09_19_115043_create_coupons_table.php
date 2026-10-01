<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // percent | fixed
            $table->decimal('value', 12, 2); // % (0-100) hoac so tien co dinh
            $table->decimal('min_order_amount', 15, 2)->default(0);
            $table->decimal('max_discount', 15, 2)->nullable(); // tran giam gia cho loai percent
            $table->unsignedInteger('usage_limit')->nullable(); // null = khong gioi han tong luot dung
            $table->unsignedInteger('used_count')->default(0);
            $table->unsignedInteger('per_user_limit')->nullable(); // null = khong gioi han moi khach
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
