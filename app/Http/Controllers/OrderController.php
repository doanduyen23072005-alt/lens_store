<?php
// app/Http/Controllers/OrderController.php
namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Notifications\OrderPlacedNotification;
use App\Services\GHNOrderService;
use App\Services\GHNService;
use App\Services\ShippingFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // ==========================================
    // 1. AJAX ĐỊA CHỈ & PHÍ VẬN CHUYỂN GHN
    // ==========================================

    public function getProvinces(GHNService $ghn)
    {
        return response()->json($ghn->getProvinces());
    }

    public function getDistricts(int $provinceId, GHNService $ghn)
    {
        return response()->json($ghn->getDistricts($provinceId));
    }

    public function getWards(int $districtId, GHNService $ghn)
    {
        return response()->json($ghn->getWards($districtId));
    }

    public function getShippingFee(Request $request, ShippingFeeService $fee)
    {
        $request->validate([
            'to_district_id' => 'required|integer',
            'to_ward_code'   => 'required|string',
        ]);

        $cart  = $this->selectedCart();
        $total = $fee->quote(
            (int) $request->to_district_id,
            (string) $request->to_ward_code,
            $fee->cartWeight($cart)
        );

        if ($total === null) {
            return response()->json([
                'code'    => 400,
                'message' => 'Tuyến này chưa hỗ trợ giao hàng.',
            ]);
        }

        return response()->json([
            'code' => 200,
            'data' => ['total' => $total],
        ]);
    }

    // ==========================================
    // 2. ĐẶT HÀNG
    // ==========================================

    public function store(Request $request, ShippingFeeService $feeService, GHNOrderService $ghnOrder)
    {
        $data = $request->validate([
            'fullname'       => 'required|string|max:255',
            'phone'          => ['required', 'regex:/^0\d{9}$/'],
            'address'        => 'required|string|max:255',
            'to_province_id' => 'required|integer',
            'to_district_id' => 'required|integer',
            'to_ward_code'   => 'required|string|max:20',
            'payment_method' => 'required|in:cod,momo',
            'note'           => 'nullable|string|max:500',
        ], [
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0.',
        ]);

        $cart = $this->selectedCart();

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống.');
        }

        // Tinh lai tien hang tu session (khong tin gia tri client gui len)
        $subtotal = (int) array_sum(array_map(
            fn ($i) => $i['price'] * $i['quantity'],
            $cart
        ));

        // Tinh lai phi ship tu GHN (khong tin gia tri client gui len)
        $shippingFee = $feeService->quote(
            (int) $data['to_district_id'],
            (string) $data['to_ward_code'],
            $feeService->cartWeight($cart)
        );

        if ($shippingFee === null) {
            return back()->withInput()
                ->with('error', 'Không tính được phí vận chuyển cho địa chỉ này. Vui lòng chọn lại.');
        }

        // Ap ma giam gia (khong tin so tien client gui len, tinh lai tu session + subtotal that)
        $coupon         = null;
        $discountAmount = 0;

        if (session()->has('coupon_code')) {
            $coupon = Coupon::where('code', session('coupon_code'))->first();
            $check  = $coupon ? $coupon->checkEligibility(Auth::user(), (float) $subtotal) : ['ok' => false];

            if (empty($check['ok'])) {
                session()->forget('coupon_code');
                $coupon = null;
            } else {
                $discountAmount = (int) $coupon->calculateDiscount((float) $subtotal);
            }
        }

        // Tong thanh toan = tien hang - giam gia + phi ship
        $totalPrice = $subtotal - $discountAmount + $shippingFee;
        // MoMo gioi han moi giao dich tu 1.000d den 50.000.000d
        if ($data['payment_method'] === 'momo' && $totalPrice > 50_000_000) {
            return back()->withInput()->with(
                'error',
                'MoMo chỉ hỗ trợ giao dịch tối đa 50.000.000 ₫. Đơn của bạn là '
                . number_format($totalPrice, 0, ',', '.')
                . ' ₫, vui lòng chọn thanh toán khi nhận hàng (COD).'
            );
        }
        try {
            $order = DB::transaction(function () use ($data, $cart, $subtotal, $shippingFee, $totalPrice, $coupon, $discountAmount) {
                $order = Order::create([
                    'user_id'         => Auth::id(),
                    'name'            => $data['fullname'],
                    'phone'           => $data['phone'],
                    'address'         => $data['address'],
                    'subtotal'        => $subtotal,
                    'ghn_total_fee'   => $shippingFee,
                    'total_price'     => $totalPrice,
                    'status'          => 'pending',
                    'payment_method'  => $data['payment_method'],
                    'shipping_status' => 'not_shipped',
                    'to_province_id'  => $data['to_province_id'],
                    'to_district_id'  => $data['to_district_id'],
                    'to_ward_code'    => $data['to_ward_code'],
                    'coupon_id'       => $coupon?->id,
                    'coupon_code'     => $coupon?->code,
                    'discount_amount' => $discountAmount,
                ]);

                foreach ($cart as $productId => $item) {
                    $product = Product::lockForUpdate()->find($productId);

                    if (!$product || $product->quantity < $item['quantity']) {
                        throw new \RuntimeException('Sản phẩm "' . $item['name'] . '" không đủ tồn kho.');
                    }

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $productId,
                        'quantity'   => $item['quantity'],
                        'price'      => $item['price'],
                    ]);

                    $product->decrement('quantity', $item['quantity']);
                }

                if ($coupon) {
                    $coupon->usages()->create([
                        'user_id'         => Auth::id(),
                        'order_id'        => $order->id,
                        'discount_amount' => $discountAmount,
                    ]);
                    $coupon->increment('used_count');
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Tao don hang that bai', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Không thể tạo đơn hàng, vui lòng thử lại.');
        }

        // Xoa cac mon vua dat khoi gio, giu lai mon chua tick
        $this->forgetPurchasedItems(array_keys($cart));
        session()->forget('coupon_code');

        // Email xac nhan don hang
        $order->load('items.product');
        try {
            Auth::user()->notify(new OrderPlacedNotification($order));
        } catch (\Throwable $e) {
            Log::warning('Khong gui duoc email xac nhan don hang', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }

        // ===== NHÁNH MOMO: chuyển sang cổng thanh toán =====
        if ($data['payment_method'] === 'momo') {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway'  => 'momo',
                'amount'   => $order->total_price,
                'status'   => 'pending',
            ]);

            return redirect()->route('momo.start', $order);
        }

        // ===== NHÁNH COD: tạo vận đơn ngay, shipper thu hộ cả phí ship =====
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway'  => 'cod',
            'amount'   => $order->total_price,
            'status'   => 'pending',
            'message'  => 'Thanh toán khi nhận hàng',
        ]);

        $ok = $ghnOrder->push($order, isPaid: false);
        $order->update(['status' => 'cod_ordered']);

        return redirect()->route('order.show', $order)->with(
            $ok ? 'success' : 'warning',
            $ok ? 'Đặt hàng thành công. Mã vận đơn GHN: ' . $order->ghn_order_code
                : 'Đặt hàng thành công nhưng chưa tạo được vận đơn GHN. Shop sẽ xử lý thủ công.'
        );
    }

    // ==========================================
    // 3. DANH SÁCH & CHI TIẾT ĐƠN HÀNG
    // ==========================================

    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with([
                'items.product',
                'paymentTransactions' => fn ($q) => $q->latest(),
            ])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        $order->load([
            'items.product',
            'paymentTransactions' => fn ($q) => $q->latest(),
        ]);

        return view('orders.show', compact('order'));
    }

    // ==========================================
    // 4. HỦY ĐƠN
    // ==========================================

    public function cancel(Order $order, GHNOrderService $ghnOrder)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if (!$order->isCancellable()) {
            return back()->with('error', 'Đơn hàng đã được lấy hàng, không thể hủy.');
        }

        $ghnOrder->cancel($order);

        DB::transaction(function () use ($order) {
            // Hoan lai ton kho
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Đã hủy đơn hàng.');
    }

    // ==========================================
    // 5. YÊU CẦU HOÀN HÀNG
    // ==========================================

    public function requestReturn(Order $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if (!$order->isReturnable()) {
            return back()->with('error', 'Chỉ có thể yêu cầu hoàn hàng khi đơn đã giao thành công.');
        }

        $order->update(['shipping_status' => 'return']);

        return back()->with('success', 'Đã gửi yêu cầu hoàn hàng. Shop sẽ liên hệ để xử lý.');
    }

    // ==========================================
    // HELPER
    // ==========================================

    /** Các món đang thanh toán — chỉ những món đã tick ở trang giỏ hàng. */
    private function selectedCart(): array
    {
        $cart     = session('cart', []);
        $selected = session('checkout_selected', array_keys($cart));

        return array_filter(
            $cart,
            fn ($id) => in_array((string) $id, array_map('strval', $selected), true),
            ARRAY_FILTER_USE_KEY
        );
    }

    /** Xoá khỏi giỏ những món vừa đặt, giữ lại món chưa chọn. */
    private function forgetPurchasedItems(array $ids): void
    {
        $cart = session('cart', []);

        foreach ($ids as $id) {
            unset($cart[$id]);
        }

        session()->put('cart', $cart);
        session()->forget('checkout_selected');
    }
}