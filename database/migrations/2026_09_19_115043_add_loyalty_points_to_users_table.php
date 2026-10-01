<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tổng điểm tích luỹ trọn đời — dùng để xếp hạng thành viên (Đồng/Bạc/Vàng/Kim Cương).
            $table->unsignedInteger('loyalty_points')->default(0)->after('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('loyalty_points');
        });
    }
};
