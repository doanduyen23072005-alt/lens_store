<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ShippingFeeService
{
    public function __construct(private GHNService $ghn) {}

    /** Tổng trọng lượng (gram) của giỏ hàng trong session. */
    public function cartWeight(array $cart): int
    {
        $weight = 0;

        foreach ($cart as $item) {
            $w = (int) ($item['weight'] ?? 0);
            $weight += ($w > 0 ? $w : 200) * (int) $item['quantity'];
        }

        return $weight > 0 ? $weight : 300;
    }

    /** Gọi GHN lấy phí ship (VND). Trả về null nếu tuyến không hỗ trợ. */
    public function quote(int $toDistrictId, string $toWardCode, int $weight): ?int
    {
        $res = $this->ghn->calculateFee([
            'service_type_id'  => 2,
            'from_district_id' => (int) config('services.ghn.from_district_id'),
            'to_district_id'   => $toDistrictId,
            'to_ward_code'     => $toWardCode,
            'weight'           => $weight,
            'length'           => 15,
            'width'            => 15,
            'height'           => 10,
        ]);

        if (($res['code'] ?? 0) === 200 && isset($res['data']['total'])) {
            return (int) $res['data']['total'];
        }

        Log::warning('GHN quote failed', [
            'district' => $toDistrictId,
            'ward'     => $toWardCode,
            'weight'   => $weight,
            'res'      => $res,
        ]);

        return null;
    }
}