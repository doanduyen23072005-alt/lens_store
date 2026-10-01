<?php
// database/seeders/AdminUserSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Tài khoản admin: đọc từ biến môi trường, không ghi mật khẩu trong code
        $adminEmail    = trim((string) config('seeding.admin.email'));
        $adminPassword = (string) config('seeding.admin.password');

        if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($adminPassword) < 12) {
            throw new RuntimeException('Set SEED_ADMIN_EMAIL and SEED_ADMIN_PASSWORD (at least 12 characters).');
        }

        // firstOrCreate: chỉ tạo khi chưa có, không đặt lại mật khẩu mỗi lần seed
        User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => config('seeding.admin.name'),
                'password'          => Hash::make($adminPassword),
                'role'              => 'admin',
                'email_verified_at' => now(),  // đánh dấu đã xác thực sẵn
            ]
        );

        // Tài khoản khách mẫu: chỉ tạo khi có khai báo biến môi trường
        $customerEmail    = trim((string) config('seeding.customer.email'));
        $customerPassword = (string) config('seeding.customer.password');

        if ($customerEmail !== '' && $customerPassword !== '') {
            User::firstOrCreate(
                ['email' => $customerEmail],
                [
                    'name'              => 'Khách hàng',
                    'password'          => Hash::make($customerPassword),
                    'role'              => 'customer',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}