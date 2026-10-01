<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();            // Mã sản phẩm
            $table->string('name');                          // Tên ống kính
            $table->foreignId('category_id')                 // Phân loại sản phẩm
                  ->constrained('categories')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->text('description')->nullable();         // Mô tả
            $table->decimal('price', 15, 2)->default(0);     // Giá (VND)
            $table->unsignedInteger('quantity')->default(0); // Số lượng tồn
            $table->string('image')->nullable();             // Đường dẫn ảnh
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};