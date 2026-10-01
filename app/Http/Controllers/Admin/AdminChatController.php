<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminChatController extends Controller
{
    /** Danh sách khách đã từng nhắn tin, kèm số tin chưa đọc. */
    public function getUsers()
    {
        $adminId = Auth::id();

        // Lay id doi phuong cua moi tin nhan lien quan den admin
        $userIds = Message::where('sender_id', $adminId)
            ->orWhere('receiver_id', $adminId)
            ->get(['sender_id', 'receiver_id'])
            ->map(fn ($m) => $m->sender_id == $adminId ? $m->receiver_id : $m->sender_id)
            ->unique()
            ->reject(fn ($id) => $id == $adminId)
            ->values();

        if ($userIds->isEmpty()) {
            return response()->json([]);
        }

        // Dem tin chua doc va thoi diem tin cuoi cua tung khach
        $unread = Message::where('receiver_id', $adminId)
            ->where('is_read', false)
            ->select('sender_id', DB::raw('COUNT(*) as total'))
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $lastAt = Message::where('sender_id', $adminId)
            ->orWhere('receiver_id', $adminId)
            ->select(
                DB::raw("CASE WHEN sender_id = {$adminId} THEN receiver_id ELSE sender_id END as partner_id"),
                DB::raw('MAX(created_at) as last_at')
            )
            ->groupBy('partner_id')
            ->pluck('last_at', 'partner_id');

        $users = User::whereIn('id', $userIds)
            ->select('id', 'name', 'email')
            ->get()
            ->map(function ($u) use ($unread, $lastAt) {
                $u->unread  = (int) ($unread[$u->id] ?? 0);
                $u->last_at = $lastAt[$u->id] ?? null;
                return $u;
            })
            ->sortByDesc('last_at')
            ->values();

        return response()->json($users);
    }

    /** Lịch sử hội thoại với một khách. */
    public function getMessages(int $userId)
    {
        $adminId = Auth::id();

        // Danh dau da doc
        Message::where('sender_id', $userId)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::with('sender:id,name')
            ->conversation($adminId, $userId)
            ->get();

        return response()->json($messages);
    }

    /** Admin trả lời khách. */
    public function send(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $data['user_id'],
            'content'     => trim($data['message']),
            'is_read'     => false,   // khach chua doc
        ]);

        return response()->json($message);
    }

    /** Tổng số tin chưa đọc, cho chấm đỏ trên nút chat của admin. */
    public function unreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
}