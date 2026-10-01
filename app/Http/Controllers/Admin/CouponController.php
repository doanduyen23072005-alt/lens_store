<?php
// app/Http/Controllers/Admin/CouponController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::withCount('usages')->latest()->paginate(20);

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Đã tạo mã giảm giá.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validated($request, $coupon->id);

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Đã cập nhật mã giảm giá.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return back()->with('success', 'Đã xoá mã giảm giá.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code'              => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($ignoreId)],
            'type'              => ['required', Rule::in(['percent', 'fixed'])],
            'value'             => ['required', 'numeric', 'min:0.01'],
            'min_order_amount'  => ['nullable', 'numeric', 'min:0'],
            'max_discount'      => ['nullable', 'numeric', 'min:0'],
            'usage_limit'       => ['nullable', 'integer', 'min:1'],
            'per_user_limit'    => ['nullable', 'integer', 'min:1'],
            'starts_at'         => ['nullable', 'date'],
            'expires_at'        => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active'         => ['nullable', 'boolean'],
            'description'       => ['nullable', 'string', 'max:255'],
        ], [], [
            'code' => 'mã', 'type' => 'loại giảm giá', 'value' => 'giá trị',
        ]);

        if ($data['type'] === 'percent' && $data['value'] > 100) {
            $data['value'] = 100;
        }

        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;
        $data['is_active']        = $request->boolean('is_active');

        return $data;
    }
}
