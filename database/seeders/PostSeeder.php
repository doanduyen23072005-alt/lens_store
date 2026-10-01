<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'admin')->first();

        $posts = [
            [
                'slug'    => 'cach-chon-ong-kinh-phu-hop-cho-nguoi-moi-bat-dau',
                'title'   => 'Cách chọn ống kính phù hợp cho người mới bắt đầu',
                'excerpt' => 'Bạn mới mua máy ảnh và không biết nên đầu tư ống kính nào trước tiên? Đây là gợi ý dành cho bạn.',
                'content' => "Khi mới bắt đầu chơi ảnh, việc chọn ống kính đầu tay khiến nhiều người bối rối vì có quá nhiều lựa chọn.\n\n"
                    . "Ống kính một tiêu cự (prime lens) như 35mm hoặc 50mm f/1.8 là lựa chọn phổ biến nhờ giá thành hợp lý, chất lượng ảnh sắc nét và khẩu độ lớn giúp xóa phông đẹp.\n\n"
                    . "Nếu bạn cần sự linh hoạt để chụp nhiều thể loại, ống kính zoom đa dụng như 24-70mm f/2.8 sẽ là người bạn đồng hành lý tưởng, dù giá thành cao hơn.\n\n"
                    . "Lời khuyên: hãy xác định thể loại ảnh bạn muốn chụp nhiều nhất (chân dung, phong cảnh, đường phố...) trước khi quyết định đầu tư.",
                'published_at' => Carbon::now()->subDays(10),
            ],
            [
                'slug'    => 'xu-huong-nhiep-anh-2026-ong-kinh-nao-dang-duoc-ua-chuong',
                'title'   => 'Xu hướng nhiếp ảnh 2026: Ống kính nào đang được ưa chuộng?',
                'excerpt' => 'Điểm qua những dòng ống kính được nhiếp ảnh gia tìm mua nhiều nhất trong năm nay.',
                'content' => "Năm 2026 chứng kiến sự lên ngôi của các ống kính macro nhờ trào lưu chụp ảnh sản phẩm và ẩm thực trên mạng xã hội.\n\n"
                    . "Bên cạnh đó, ống kính góc rộng vẫn giữ vững vị thế trong nhóm nhiếp ảnh gia phong cảnh và kiến trúc, đặc biệt là các dòng có khẩu độ lớn phục vụ chụp đêm, chụp dải Ngân Hà.\n\n"
                    . "Ống kính một tiêu cự khẩu lớn (f/1.4, f/1.8) tiếp tục là lựa chọn hàng đầu cho thể loại chân dung nhờ khả năng xóa phông mượt mà và lấy nét nhanh.",
                'published_at' => Carbon::now()->subDays(4),
            ],
            [
                'slug'    => 'huong-dan-bao-quan-ong-kinh-dung-cach',
                'title'   => 'Hướng dẫn bảo quản ống kính đúng cách',
                'excerpt' => 'Bảo quản đúng cách giúp ống kính của bạn luôn bền đẹp và cho chất lượng ảnh tốt nhất theo thời gian.',
                'content' => "Ống kính là khoản đầu tư lớn, vì vậy việc bảo quản đúng cách rất quan trọng.\n\n"
                    . "1. Luôn để ống kính trong tủ chống ẩm với độ ẩm 40-50% để tránh nấm mốc.\n"
                    . "2. Dùng nắp đậy ống kính (lens cap) khi không sử dụng để tránh bụi và trầy xước.\n"
                    . "3. Vệ sinh bề mặt kính bằng khăn microfiber chuyên dụng, tránh dùng khăn giấy thông thường.\n"
                    . "4. Tránh để ống kính tiếp xúc trực tiếp với ánh nắng gắt trong thời gian dài.\n\n"
                    . "Bảo quản tốt không chỉ giữ ống kính bền đẹp mà còn giữ giá trị khi bạn muốn thanh lý hoặc nâng cấp thiết bị sau này.",
                'published_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($posts as $post) {
            Post::updateOrCreate(
                ['slug' => $post['slug']],
                $post + ['user_id' => $author?->id, 'is_published' => true]
            );
        }
    }
}
