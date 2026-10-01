<?php
// app/Notifications/OrderPlacedNotification.php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(private Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject('Xác nhận đơn hàng #' . $order->id . ' — Lens Store')
            ->greeting('Cảm ơn ' . $order->name . ' đã đặt hàng!')
            ->line('Đơn hàng #' . $order->id . ' của bạn đã được ghi nhận thành công.');

        foreach ($order->items as $item) {
            $mail->line('• ' . ($item->product->name ?? 'Sản phẩm') . ' × ' . $item->quantity
                . ' — ' . number_format($item->price * $item->quantity, 0, ',', '.') . ' đ');
        }

        if ((float) $order->discount_amount > 0) {
            $mail->line('Mã giảm giá ' . $order->coupon_code . ': -' . number_format($order->discount_amount, 0, ',', '.') . ' đ');
        }

        return $mail
            ->line('Tổng thanh toán: ' . number_format($order->total_price, 0, ',', '.') . ' đ')
            ->line('Hình thức thanh toán: ' . $order->payment_label)
            ->line('Giao đến: ' . $order->address)
            ->action('Xem chi tiết đơn hàng', route('order.show', $order->id))
            ->line('Cảm ơn bạn đã tin tưởng Lens Store!');
    }
}
