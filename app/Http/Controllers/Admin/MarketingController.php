<?php
// app/Http/Controllers/Admin/MarketingController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use App\Notifications\PromotionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MarketingController extends Controller
{
    /** Form soạn email marketing/thông báo gửi cho khách hàng. */
    public function compose()
    {
        $coupons        = Coupon::active()->orderByDesc('id')->get();
        $customerCount  = User::where('role', 'customer')->whereNotNull('email_verified_at')->count();

        return view('admin.marketing.compose', compact('coupons', 'customerCount'));
    }

    /** Gửi email tới toàn bộ khách hàng đã xác thực email. */
    public function send(Request $request)
    {
        $data = $request->validate([
            'subject'     => ['required', 'string', 'max:150'],
            'body'        => ['required', 'string', 'max:3000'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ], [], ['subject' => 'tiêu đề', 'body' => 'nội dung']);

        $customers = User::where('role', 'customer')->whereNotNull('email_verified_at')->get();

        if ($customers->isEmpty()) {
            return back()->withInput()->with('error', 'Chưa có khách hàng nào đã xác thực email để gửi.');
        }

        try {
            Notification::send($customers, new PromotionNotification(
                $data['subject'],
                $data['body'],
                $data['coupon_code'] ?? null
            ));
        } catch (\Throwable $e) {
            Log::error('Gui email marketing that bai', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Có lỗi khi gửi email. Vui lòng thử lại.');
        }

        return back()->with('success', 'Đã gửi email tới '.$customers->count().' khách hàng.');
    }
}
