<?php
// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'category_id', 'description', 'price', 'quantity', 'image',
        'weight', // gram - dung cho GHN
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'integer',
        'weight'   => 'integer',
    ];

    public static function booted(): void
    {
        static::saving(fn (Product $p) => $p->code = Str::upper($p->code));
    }

    /** Sản phẩm thuộc về một phân loại */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /** Sản phẩm xuất hiện trong nhiều dòng đơn hàng */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Đánh giá của khách hàng về sản phẩm */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /** Khách đã mua sản phẩm này và đã nhận hàng thành công chưa — điều kiện để được đánh giá */
    public function purchasedAndDeliveredBy(int $userId): bool
    {
        return $this->orderItems()
            ->whereHas('order', fn ($query) => $query->where('user_id', $userId)->where('shipping_status', 'delivered'))
            ->exists();
    }

    /** Đường dẫn ảnh, trả về ảnh mặc định nếu chưa có */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /** Giá đã định dạng VND */
    public function getPriceFormattedAttribute(): string
    {
        return number_format((float) $this->price, 0, ',', '.') . ' ₫';
    }

    /** Trạng thái kho */
    public function getStockLabelAttribute(): string
    {
        return match (true) {
            $this->quantity <= 0 => 'Hết hàng',
            $this->quantity < 5  => 'Sắp hết',
            default              => 'Còn hàng',
        };
    }

    /** Trọng lượng an toàn (gram) để gửi GHN, không bao giờ trả 0 */
    public function getShippingWeightAttribute(): int
    {
        return $this->weight > 0 ? (int) $this->weight : 200;
    }
}