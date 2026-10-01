<?php
// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'subtotal',
        'total_price',
        'status',
        'payment_method',
        'shipping_status',
        'paid_at',
        // Khuyến mãi
        'coupon_id',
        'coupon_code',
        'discount_amount',
        // GHN
        'ghn_order_code',
        'ghn_total_fee',
        'to_province_id',
        'to_district_id',
        'to_ward_code',
    ];

    protected $casts = [
        'subtotal'       => 'decimal:2',
        'total_price'    => 'decimal:2',
        'discount_amount'=> 'decimal:2',
        'ghn_total_fee'  => 'integer',
        'to_province_id' => 'integer',
        'to_district_id' => 'integer',
        'paid_at'        => 'datetime',
    ];

    // ==========================================
    // QUAN HỆ
    // ==========================================

    /** Một đơn hàng thuộc về một người dùng */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Một đơn hàng có nhiều mục sản phẩm */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Lịch sử giao dịch thanh toán (COD / MoMo) */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    /** Giao dịch gần nhất — dùng hiển thị trạng thái thanh toán mới nhất */
    public function latestTransaction(): HasOne
    {
        return $this->hasOne(PaymentTransaction::class)->latestOfMany();
    }

    /** Mã giảm giá đã áp dụng cho đơn này (nếu có) */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    // ==========================================
    // SCOPE
    // ==========================================

    /** Đơn của người dùng đang đăng nhập */
    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /** Đơn chưa thanh toán xong */
    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', ['paid', 'cancelled']);
    }

    // ==========================================
    // HELPER
    // ==========================================

    /** Đã thanh toán online chưa — quyết định cod_amount gửi GHN */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /** Đã đẩy vận đơn sang GHN chưa */
    public function hasShipment(): bool
    {
        return !empty($this->ghn_order_code);
    }

    /** Còn hủy được không */
    public function isCancellable(): bool
    {
        return in_array($this->shipping_status, ['not_shipped', 'ready_to_pick'], true)
            && $this->status !== 'cancelled';
    }

    /** Khách được yêu cầu hoàn hàng khi đơn đã giao thành công */
    public function isReturnable(): bool
    {
        return $this->shipping_status === 'delivered';
    }

    /** Đơn MoMo chưa trả tiền — hiện nút "Thanh toán lại" */
    public function canRetryPayment(): bool
    {
        return $this->payment_method === 'momo'
            && !in_array($this->status, ['paid', 'cancelled'], true);
    }

    // ==========================================
    // ACCESSOR
    // ==========================================

    /** Nhãn tiếng Việt cho trạng thái giao hàng GHN */
    public function getShippingLabelAttribute(): string
    {
        return match ($this->shipping_status) {
            'not_shipped'   => 'Chưa gửi hàng',
            'processing'    => 'Đang tạo vận đơn',
            'ready_to_pick' => 'Chờ lấy hàng',
            'picking'       => 'Đang lấy hàng',
            'picked'        => 'Đã lấy hàng',
            'storing'       => 'Đang ở kho',
            'transporting'  => 'Đang trung chuyển',
            'sorting'       => 'Đang phân loại',
            'delivering'    => 'Đang giao hàng',
            'delivered'     => 'Giao thành công',
            'delivery_fail' => 'Giao không thành công',
            'return'        => 'Đang hoàn hàng',
            'returned'      => 'Đã hoàn hàng',
            'cancel'        => 'Đã hủy vận đơn',
            default         => $this->shipping_status,
        };
    }

    /** Nhãn tiếng Việt cho trạng thái thanh toán */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'        => 'Chờ thanh toán',
            'paid'           => 'Đã thanh toán',
            'cod_ordered'    => 'COD - thu khi nhận hàng',
            'cod_paid'       => 'COD - đã thu tiền',
            'failed'         => 'Thanh toán thất bại',
            'cancelled'      => 'Đã hủy',
            'refund_pending' => 'Chờ hoàn tiền',
            'refunded'       => 'Đã hoàn tiền',
            default          => $this->status,
        };
    }

    /** Màu badge Bootstrap cho trạng thái thanh toán — dùng ở trang đơn hàng của khách */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid', 'cod_paid', 'refunded' => 'success',
            'failed', 'cancelled'          => 'danger',
            default                        => 'warning', // pending, cod_ordered, refund_pending
        };
    }

    /** Tên hình thức thanh toán */
    public function getPaymentLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'cod'   => 'Thanh toán khi nhận hàng',
            'momo'  => 'Ví MoMo',
            default => $this->payment_method,
        };
    }

    /** Tổng số sản phẩm trong đơn */
    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}