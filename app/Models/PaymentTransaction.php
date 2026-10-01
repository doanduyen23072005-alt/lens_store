<?php
// app/Models/PaymentTransaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'gateway',           // cod | momo
        'gateway_order_id',  // orderId gửi sang cổng thanh toán
        'transaction_id',    // transId cổng thanh toán trả về
        'amount',
        'status',            // pending | initiated | paid | failed
        'result_code',
        'message',
        'request_payload',
        'response_payload',
        'paid_at',
    ];

    /**
     * Chỉ định cách chuyển đổi kiểu dữ liệu khi đọc/ghi DB.
     * request_payload và response_payload là cột JSON nên cast sang array
     * để dùng trực tiếp như mảng PHP, không phải json_decode thủ công.
     */
    protected function casts(): array
    {
        return [
            'amount'           => 'decimal:2',
            'request_payload'  => 'array',
            'response_payload' => 'array',
            'paid_at'          => 'datetime',
        ];
    }

    // ==========================================
    // QUAN HỆ
    // ==========================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ==========================================
    // SCOPE
    // ==========================================

    public function scopeMomo($query)
    {
        return $query->where('gateway', 'momo');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    // ==========================================
    // HELPER & ACCESSOR
    // ==========================================

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Nhãn tiếng Việt cho trạng thái giao dịch */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'        => 'Chờ xử lý',
            'initiated'      => 'Đã chuyển sang cổng thanh toán',
            'paid'           => 'Đã thanh toán',
            'failed'         => 'Thất bại',
            'refund_pending' => 'Chờ hoàn tiền',
            'refunded'       => 'Đã hoàn tiền',
            default          => $this->status,
        };
    }

    /** Màu badge Bootstrap cho trạng thái giao dịch — dùng ở lịch sử giao dịch phía khách hàng */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid', 'refunded' => 'success',
            'failed'           => 'danger',
            default            => 'warning', // pending, initiated, refund_pending
        };
    }

    /** Tên cổng thanh toán hiển thị */
    public function getGatewayLabelAttribute(): string
    {
        return match ($this->gateway) {
            'cod'   => 'COD',
            'momo'  => 'MoMo',
            default => strtoupper($this->gateway),
        };
    }
}