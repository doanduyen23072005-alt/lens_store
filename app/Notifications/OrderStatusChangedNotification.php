<?php
// app/Notifications/OrderStatusChangedNotification.php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    /** @param string $context 'shipping' (mặc định) hoặc 'payment' — quyết định nội dung email. */
    public function __construct(private Order $order, private string $context = 'shipping')
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return $this->context === 'payment' ? $this->paymentMail() : $this->shippingMail();
    }

    private function shippingMail(): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject('Cập nhật đơn hàng #' . $order->id . ' — ' . $order->shipping_label)
            ->greeting('Xin chào ' . $order->name . ',')
            ->line('Đơn hàng #' . $order->id . ' của bạn vừa được cập nhật trạng thái vận chuyển:')
            ->line('**' . $order->shipping_label . '**');

        if ($order->shipping_status === 'delivered') {
            $mail->line('Cảm ơn bạn đã mua hàng tại Lens Store! Đừng quên để lại đánh giá cho sản phẩm nhé.');
        } elseif ($order->status === 'cancelled') {
            $mail->line('Nếu đơn hàng đã thanh toán, số tiền sẽ được hoàn lại theo chính sách của Lens Store.');
        }

        if ($order->ghn_order_code) {
            $mail->line('Mã vận đơn: ' . $order->ghn_order_code);
        }

        return $mail
            ->action('Xem chi tiết đơn hàng', route('order.show', $order->id))
            ->line('Cảm ơn bạn đã tin tưởng Lens Store!');
    }

    private function paymentMail(): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject('Cập nhật thanh toán đơn hàng #' . $order->id . ' — ' . $order->status_label)
            ->greeting('Xin chào ' . $order->name . ',')
            ->line('Đơn hàng #' . $order->id . ' của bạn vừa được cập nhật trạng thái thanh toán:')
            ->line('**' . $order->status_label . '**');

        if ($order->status === 'refund_pending') {
            $mail->line('Chúng tôi đang xử lý hoàn tiền cho đơn hàng này, vui lòng đợi trong vài ngày làm việc.');
        } elseif ($order->status === 'refunded') {
            $mail->line('Số tiền đã được hoàn lại cho bạn. Cảm ơn bạn đã kiên nhẫn chờ đợi!');
        }

        return $mail
            ->action('Xem chi tiết đơn hàng', route('order.show', $order->id))
            ->line('Cảm ơn bạn đã tin tưởng Lens Store!');
    }
}
