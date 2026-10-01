<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class GHNOrderService
{
    public function __construct(private GHNService $ghn) {}

    /** Tạo vận đơn nếu đơn chưa có. Trả về true nếu thành công. */
    public function push(Order $order, bool $isPaid = false): bool
    {
        if (!empty($order->ghn_order_code)) {
            return true;
        }

        if (empty($order->to_district_id) || empty($order->to_ward_code)) {
            Log::warning('Order thieu dia chi GHN', ['order_id' => $order->id]);
            return false;
        }

        $res = $this->create($order, $isPaid);

        if (($res['code'] ?? 0) !== 200 || empty($res['data']['order_code'])) {
            Log::error('Tao van don GHN that bai', ['order_id' => $order->id, 'res' => $res]);
            return false;
        }

        $order->update([
            'ghn_order_code'  => $res['data']['order_code'],
            'ghn_total_fee'   => (int) ($res['data']['total_fee'] ?? $order->ghn_total_fee),
            'shipping_status' => 'ready_to_pick',
        ]);

        return true;
    }

    public function create(Order $order, bool $isPaid = false): array
    {
        $items  = [];
        $weight = 0;

        foreach ($order->items as $item) {
            $itemWeight = (int) ($item->product->shipping_weight ?? 200);
            $weight    += $itemWeight * (int) $item->quantity;

            $items[] = [
                'name'     => $item->product->name ?? 'Sản phẩm',
                'quantity' => (int) $item->quantity,
                'price'    => (int) $item->price,
                'weight'   => $itemWeight,
            ];
        }

                return $this->ghn->createOrder([
            'payment_type_id' => 1,   // 1 = shop tra phi ship (da thu cua khach roi)
            'note'            => 'Đơn hàng #' . $order->id,
            'required_note'   => 'KHONGCHOXEMHANG',
            'to_name'         => $order->name,
            'to_phone'        => $order->phone,
            'to_address'      => $order->address,
            'to_ward_code'    => (string) $order->to_ward_code,
            'to_district_id'  => (int) $order->to_district_id,
            'cod_amount'      => $isPaid ? 0 : (int) $order->total_price,
            'insurance_value' => (int) $order->subtotal,
            'weight'          => $weight > 0 ? $weight : 300,
            'length'          => 15,
            'width'           => 15,
            'height'          => 10,
            'service_type_id' => 2,
            'items'           => $items,
        ]);
    }

    public function cancel(Order $order): bool
    {
        if (empty($order->ghn_order_code)) {
            return true;
        }

        $res = $this->ghn->cancelOrder([$order->ghn_order_code]);

        if (($res['code'] ?? 0) === 200) {
            $order->update(['shipping_status' => 'cancel']);
            return true;
        }

        Log::warning('Huy van don GHN that bai', ['order_id' => $order->id, 'res' => $res]);
        return false;
    }
}