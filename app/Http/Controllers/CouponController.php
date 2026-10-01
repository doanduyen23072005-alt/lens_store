<?php
// app/Http/Controllers/CouponController.php
namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * Áp mã giảm giá.
     * Nếu request gửi kèm 'subtotal' (vd. từ trang thanh toán, chỉ tính trên các món đã chọn)
     * thì kiểm tra/tính giảm giá trên số đó; ngược lại dùng tổng cả giỏ hàng.
     */
    public function apply(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'max:50']]);

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))->first();

        if (! $coupon) {
            return $this->respond($request, false, 'Mã giảm giá không tồn tại.');
        }

        $cart         = session('cart', []);
        $cartSubtotal = (float) array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart));
        $subtotal     = $request->filled('subtotal') ? (float) $request->subtotal : $cartSubtotal;

        $check = $coupon->checkEligibility(Auth::user(), $subtotal);

        if (! $check['ok']) {
            return $this->respond($request, false, $check['message']);
        }

        session(['coupon_code' => $coupon->code]);

        return $this->respond($request, true, $check['message'], [
            'code'     => $coupon->code,
            'discount' => $coupon->calculateDiscount($subtotal),
        ]);
    }

    /** Bỏ mã giảm giá đang áp dụng. */
    public function remove(Request $request)
    {
        session()->forget('coupon_code');

        return $this->respond($request, true, 'Đã bỏ mã giảm giá.');
    }

    private function respond(Request $request, bool $ok, string $message, array $extra = [])
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge(['ok' => $ok, 'message' => $message], $extra));
        }

        return $ok ? back()->with('success', $message) : back()->with('error', $message);
    }
}
