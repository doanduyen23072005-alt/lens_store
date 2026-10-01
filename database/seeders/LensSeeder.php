<?php
// database/seeders/LensSeeder.php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class LensSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'PRIME', 'name' => 'Ống kính một tiêu cự', 'sort_order' => 1, 'description' => 'Tiêu cự cố định, khẩu lớn, ảnh sắc nét.'],
            ['code' => 'ZOOM',  'name' => 'Ống kính zoom',        'sort_order' => 2, 'description' => 'Thay đổi tiêu cự linh hoạt cho nhiều bối cảnh.'],
            ['code' => 'MACRO', 'name' => 'Ống kính macro',       'sort_order' => 3, 'description' => 'Chụp cận cảnh tỉ lệ 1:1.'],
            ['code' => 'TELE',  'name' => 'Ống kính tele',        'sort_order' => 4, 'description' => 'Tiêu cự dài cho thể thao và động vật hoang dã.'],
            ['code' => 'WIDE',  'name' => 'Ống kính góc rộng',    'sort_order' => 5, 'description' => 'Phong cảnh, kiến trúc, nội thất.'],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['code' => $data['code']], $data + ['is_active' => true]);
        }

        $products = [
            ['RF50F18',  'Canon RF 50mm f/1.8 STM',            'PRIME', 4290000,  12],
            ['NZ35F18',  'Nikon Z 35mm f/1.8 S',               'PRIME', 17900000, 4],
            ['SEL2470',  'Sony FE 24-70mm f/2.8 GM II',        'ZOOM',  53900000, 3],
            ['SIG105M',  'Sigma 105mm f/2.8 DG DN Macro Art',  'MACRO', 19500000, 6],
            ['TAM70180', 'Tamron 70-180mm f/2.8 Di III VXD',   'TELE',  25900000, 2],
            ['SAM12F2',  'Samyang 12mm f/2.0 NCS CS',          'WIDE',  6890000,  0],
        ];

        foreach ($products as [$code, $name, $catCode, $price, $qty]) {
            Product::updateOrCreate(['code' => $code], [
                'name'        => $name,
                'category_id' => Category::where('code', $catCode)->value('id'),
                'price'       => $price,
                'quantity'    => $qty,
                'description' => 'Hàng chính hãng, bảo hành 12 tháng.',
            ]);
        }
    }
}