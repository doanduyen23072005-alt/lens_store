<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();               // Mã phân loại: PRIME, ZOOM...
            $table->string('name');                             // Tên phân loại
            $table->string('slug')->unique();                   // Đường dẫn thân thiện
            $table->text('description')->nullable();            // Mô tả
            $table->unsignedInteger('sort_order')->default(0);  // Thứ tự hiển thị
            $table->boolean('is_active')->default(true);        // Trạng thái ẩn/hiện
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};