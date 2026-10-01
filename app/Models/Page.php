<?php
// app/Models/Page.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'meta_description', 'content', 'is_published'];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    /** Tự sinh slug từ tiêu đề nếu để trống */
    public static function booted(): void
    {
        static::saving(function (Page $page) {
            if (blank($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }
}
