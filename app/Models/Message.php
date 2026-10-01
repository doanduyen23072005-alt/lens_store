<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /** Toàn bộ hội thoại giữa 2 người, theo thứ tự thời gian. */
    public function scopeConversation($query, int $a, int $b)
    {
        return $query->where(function ($q) use ($a, $b) {
            $q->where('sender_id', $a)->where('receiver_id', $b);
        })->orWhere(function ($q) use ($a, $b) {
            $q->where('sender_id', $b)->where('receiver_id', $a);
        })->orderBy('created_at');
    }
}