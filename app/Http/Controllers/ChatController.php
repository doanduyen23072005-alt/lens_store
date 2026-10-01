<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /** Gửi tin nhắn tới admin. */
    public function send(Request $request)
    {
        $content = trim((string) $request->input('message'));

        if ($content === '') {
            return response()->json(['error' => 'Nội dung tin nhắn không được để trống'], 400);
        }

        if (mb_strlen($content) > 2000) {
            return response()->json(['error' => 'Tin nhắn quá dài (tối đa 2000 ký tự)'], 400);
        }

        $adminId = $this->adminId();

        if (!$adminId) {
            return response()->json(['error' => 'Hiện chưa có nhân viên hỗ trợ.'], 503);
        }

        $message = Message::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $adminId,
            'content'     => $content,
            'is_read'     => false,
        ]);

        return response()->json($message);
    }

    /** Lịch sử hội thoại giữa khách và admin. */
    public function getMessages()
    {
        $adminId = $this->adminId();

        if (!$adminId) {
            return response()->json([]);
        }

        // Danh dau da doc cac tin admin gui cho minh
        Message::where('sender_id', $adminId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::conversation(Auth::id(), $adminId)->get();

        return response()->json($messages);
    }

    /** Số tin chưa đọc, dùng cho chấm đỏ trên nút chat. */
    public function unreadCount()
    {
        $count = Message::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /** ID của admin nhận tin. Lấy admin đầu tiên trong hệ thống. */
    private function adminId(): ?int
    {
        return User::where('role', 'admin')->value('id');
    }
}