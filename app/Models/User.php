<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Laravel 10 dùng thuộc tính $casts.
    // (Laravel 11/12 mới dùng phương thức casts(): array)
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /** Người dùng có phải quản trị viên không */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Nhãn tiếng Việt của vai trò */
    public function getRoleLabelAttribute(): string
    {
        return $this->isAdmin() ? 'Quản trị viên' : 'Khách hàng';
    }
    public function orders()
{
    return $this->hasMany(Order::class);
}

    // ==========================================
    // KHÁCH HÀNG THÂN THIẾT
    // ==========================================

    /** Ngưỡng điểm cho từng hạng thành viên */
    public const LOYALTY_TIERS = [
        'Kim Cương' => 5000,
        'Vàng'      => 2000,
        'Bạc'       => 500,
        'Đồng'      => 0,
    ];

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    /** Hạng thành viên hiện tại, tính theo tổng điểm tích luỹ */
    public function getLoyaltyTierAttribute(): string
    {
        foreach (self::LOYALTY_TIERS as $tier => $threshold) {
            if ($this->loyalty_points >= $threshold) {
                return $tier;
            }
        }

        return 'Đồng';
    }

    /** Số điểm còn thiếu để lên hạng kế tiếp, null nếu đã ở hạng cao nhất */
    public function getPointsToNextTierAttribute(): ?int
    {
        $thresholds = array_reverse(self::LOYALTY_TIERS, true);

        foreach ($thresholds as $threshold) {
            if ($this->loyalty_points < $threshold) {
                return $threshold - $this->loyalty_points;
            }
        }

        return null;
    }

    /** Cộng điểm thưởng và ghi lại lịch sử — dùng khi đơn hàng giao thành công. */
    public function awardLoyaltyPoints(int $points, string $reason, ?Order $order = null): void
    {
        if ($points === 0) {
            return;
        }

        $this->increment('loyalty_points', $points);

        $this->loyaltyTransactions()->create([
            'order_id' => $order?->id,
            'points'   => $points,
            'reason'   => $reason,
        ]);
    }
}