<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug'  => 've-chung-toi',
                'title' => 'Về chúng tôi',
                'meta_description' => 'Lens Store - cửa hàng ống kính máy ảnh chính hãng.',
                'content' => "Lens Store là cửa hàng chuyên cung cấp ống kính máy ảnh chính hãng cho nhiếp ảnh gia và người yêu nhiếp ảnh tại Việt Nam.\n\n"
                    . "Chúng tôi cam kết 100% hàng chính hãng, có đầy đủ giấy tờ bảo hành, và đội ngũ tư vấn am hiểu sản phẩm để giúp bạn chọn được ống kính phù hợp nhất với nhu cầu chụp ảnh của mình.\n\n"
                    . "Với nhiều năm kinh nghiệm trong ngành, Lens Store tự hào là điểm đến tin cậy của hàng nghìn khách hàng trên khắp cả nước.",
            ],
            [
                'slug'  => 'lien-he',
                'title' => 'Liên hệ',
                'meta_description' => 'Thông tin liên hệ Lens Store.',
                'content' => "Chúng tôi luôn sẵn sàng hỗ trợ bạn.\n\n"
                    . "Địa chỉ: 123 Đường Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh\n"
                    . "Hotline: 1900 6868\n"
                    . "Email: hotro@lensstore.vn\n"
                    . "Giờ làm việc: 8:00 - 21:00 tất cả các ngày trong tuần\n\n"
                    . "Bạn cũng có thể chat trực tiếp với chúng tôi qua khung chat hỗ trợ ở góc màn hình.",
            ],
            [
                'slug'  => 'chinh-sach-bao-hanh-doi-tra',
                'title' => 'Chính sách bảo hành & đổi trả',
                'meta_description' => 'Chính sách bảo hành và đổi trả sản phẩm tại Lens Store.',
                'content' => "1. Bảo hành\nTất cả ống kính bán tại Lens Store được bảo hành chính hãng 12 tháng kể từ ngày mua hàng.\n\n"
                    . "2. Đổi trả\nQuý khách được đổi trả trong vòng 7 ngày nếu sản phẩm còn nguyên vẹn, đầy đủ phụ kiện, tem bảo hành và chưa qua sử dụng, trong các trường hợp: sản phẩm lỗi do nhà sản xuất, giao sai mẫu/sai số lượng.\n\n"
                    . "3. Quy trình\nLiên hệ hotline hoặc khung chat hỗ trợ để được hướng dẫn gửi trả sản phẩm. Chi phí vận chuyển đổi trả do lỗi từ Lens Store sẽ được hoàn lại.",
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page + ['is_published' => true]);
        }
    }
}
