<?php
// app/Models/Category.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'slug', 'description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /** Tự sinh slug từ tên nếu người dùng để trống */
    public static function booted(): void
    {
        static::saving(function (Category $category) {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
            $category->code = Str::upper($category->code);
        });
    }

    /** Một phân loại có nhiều sản phẩm */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}