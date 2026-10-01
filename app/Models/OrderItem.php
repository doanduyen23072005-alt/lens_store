<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price'    => 'decimal:2',
    ];

    // Một mục sản phẩm thuộc về một đơn hàng
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Một mục sản phẩm liên kết với 1 sản phẩm
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Thành tiền của dòng này
    public function getSubtotalAttribute(): float
    {
        return (float) $this->price * $this->quantity;
    }
}