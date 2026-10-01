<?php
// app/Models/Coupon.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount',
        'usage_limit', 'used_count', 'per_user_limit',
        'starts_at', 'expires_at', 'is_active', 'description',
    ];

    protected $casts = [
        'value'             => 'decimal:2',
        'min_order_amount'  => 'decimal:2',
        'max_discount'      => 'decimal:2',
        'usage_limit'       => 'integer',
        'used_count'        => 'integer',
        'per_user_limit'    => 'integer',
        'starts_at'         => 'datetime',
        'expires_at'        => 'datetime',
        'is_active'         => 'boolean',
    ];

    public static function booted(): void
    {
        static::saving(fn (Coupon $coupon) => $coupon->code = Str::upper(trim($coupon->code)));
    }

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /** Nhãn tiếng Việt cho loại giảm giá */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'percent' ? 'Phần trăm' : 'Số tiền cố định';
    }

    /** Hiển thị giá trị giảm giá ngắn gọn */
    public function getValueLabelAttribute(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format((float) $this->value, 1), '0'), '.') . '%'
            : number_format((float) $this->value, 0, ',', '.') . ' đ';
    }

    /**
     * Kiểm tra mã có dùng được cho khách hàng và tổng tiền này không.
     * Trả về ['ok' => bool, 'message' => string].
     */
    public function checkEligibility(?User $user, float $subtotal): array
    {
        if (! $this->is_active) {
            return ['ok' => false, 'message' => 'Mã giảm giá không còn hiệu lực.'];
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return ['ok' => false, 'message' => 'Mã giảm giá chưa đến ngày áp dụng.'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['ok' => false, 'message' => 'Mã giảm giá đã hết hạn.'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['ok' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        if ($subtotal < (float) $this->min_order_amount) {
            return ['ok' => false, 'message' => 'Đơn hàng cần tối thiểu ' . number_format((float) $this->min_order_amount, 0, ',', '.') . ' đ để dùng mã này.'];
        }

        if ($user && $this->per_user_limit !== null) {
            $usedByUser = $this->usages()->where('user_id', $user->id)->count();
            if ($usedByUser >= $this->per_user_limit) {
                return ['ok' => false, 'message' => 'Bạn đã dùng hết lượt cho mã giảm giá này.'];
            }
        }

        return ['ok' => true, 'message' => 'Áp dụng mã giảm giá thành công.'];
    }

    /** Số tiền được giảm cho một mức tạm tính, đã áp trần và không vượt quá tạm tính. */
    public function calculateDiscount(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        if ($this->type === 'percent' && $this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }

        return round(min($discount, $subtotal), 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', Carbon::now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now()));
    }
}
