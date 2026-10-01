<?php
// app/Models/Post.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'content', 'image', 'is_published', 'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /** Tự sinh slug từ tiêu đề, và mặc định ngày đăng là bây giờ nếu để trống */
    public static function booted(): void
    {
        static::saving(function (Post $post) {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            if ($post->is_published && blank($post->published_at)) {
                $post->published_at = Carbon::now();
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Chỉ bài đã đăng và đến ngày hiển thị */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', Carbon::now()));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    /** Tóm tắt hiển thị ở danh sách — dùng excerpt nếu có, không thì cắt từ nội dung */
    public function getExcerptTextAttribute(): string
    {
        return $this->excerpt ?: Str::limit(strip_tags($this->content), 160);
    }
}
