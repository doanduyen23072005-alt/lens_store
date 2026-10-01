<?php
// app/Notifications/PromotionNotification.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PromotionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $subject,
        private string $body,
        private ?string $couponCode = null,
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->greeting('Xin chào ' . $notifiable->name . ',');

        foreach (explode("\n", trim($this->body)) as $line) {
            if ($line !== '') {
                $mail->line($line);
            }
        }

        if ($this->couponCode) {
            $mail->line('Mã ưu đãi của bạn: **' . $this->couponCode . '**');
        }

        return $mail
            ->action('Mua sắm ngay', route('home'))
            ->line('Hẹn gặp lại bạn tại Lens Store!');
    }
}
