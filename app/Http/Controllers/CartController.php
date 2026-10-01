<?php
// app/Http/Controllers/CartController.php
namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /** Hiển thị giỏ hàng */
    public function index()
    {
        $cart = session()->get('cart', []);

        $coupon   = null;
        $discount = 0.0;

        if (session()->has('coupon_code')) {
            $subtotal = (float) array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $cart));
            $coupon   = Coupon::where('code', session('coupon_code'))->first();
            $check    = $coupon ? $coupon->checkEligibility(Auth::user(), $subtotal) : ['ok' => false];

            if (empty($check['ok'])) {
                session()->forget('coupon_code');
                $coupon = null;
            } else {
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        return view('cart.index', compact('cart', 'coupon', 'discount'));
    }

    /**
     * Thêm sản phẩm vào giỏ.
     * Trả JSON nếu gọi bằng AJAX (nút thêm nhanh ở trang chủ),
     * trả redirect nếu submit form thường (trang chi tiết).
     */
    public function add(Request $request, Product $product)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart     = session()->get('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $quantity;
        } else {
                        $cart[$product->id] = [
                'id'       => $product->id,
                'name'     => $product->name,
                'code'     => $product->code,
                'price'    => (float) $product->price,
                'quantity' => $quantity,
                'image'    => $product->image,
                'weight'   => $product->shipping_weight,   // gram, dung cho GHN
                'category' => $product->category?->name,
            ];
        }

        // Không cho vượt quá tồn kho
        if ($cart[$product->id]['quantity'] > $product->quantity) {
            $cart[$product->id]['quantity'] = max(1, $product->quantity);
        }

        session()->put('cart', $cart);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok'      => true,
                'count'   => $this->countItems($cart),
                'message' => 'Đã thêm "' . $product->name . '" vào giỏ.',
            ]);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    /** Cập nhật số lượng một dòng trong giỏ */
    public function update(Request $request, $id)
    {
        $quantity = (int) $request->input('quantity');
        $cart     = session()->get('cart', []);

        if (! isset($cart[$id])) {
            return redirect()->route('cart.index')
                ->with('error', 'Sản phẩm không có trong giỏ hàng.');
        }

        if ($quantity < 1) {
            unset($cart[$id]);
            session()->put('cart', $cart);

            return redirect()->route('cart.index')
                ->with('success', 'Đã xoá sản phẩm khỏi giỏ hàng.');
        }

        // Chặn vượt tồn kho
        $stock = Product::whereKey($id)->value('quantity') ?? 0;
        if ($quantity > $stock) {
            $quantity = $stock;
        }

        $cart[$id]['quantity'] = max(1, $quantity);
        session()->put('cart', $cart);

        return redirect()->route('cart.index')
            ->with('success', 'Đã cập nhật giỏ hàng.');
    }

    /** Xoá một sản phẩm khỏi giỏ */
    public function remove($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')
            ->with('success', 'Đã xoá sản phẩm khỏi giỏ hàng.');
    }

    /** Xoá sạch giỏ hàng */
    public function clear()
    {
        session()->forget('cart');

        return redirect()->route('cart.index')
            ->with('success', 'Đã xoá toàn bộ giỏ hàng.');
    }

    /**
     * Trang thanh toán.
     * Nhận danh sách id được tick từ trang giỏ hàng, chỉ hiển thị các món đó.
     */
    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        // POST tu trang gio hang gui 'selected'; GET (quay lai sau khi ap ma) dung lai lua chon da luu.
        $selected = (array) $request->input('selected', session('checkout_selected', array_keys($cart)));

        $items = array_filter(
            $cart,
            fn ($id) => in_array((string) $id, array_map('strval', $selected), true),
            ARRAY_FILTER_USE_KEY
        );

        if (empty($items)) {
            return redirect()->route('cart.index')
                ->with('error', 'Hãy chọn ít nhất một sản phẩm để thanh toán.');
        }

        $total = array_sum(array_map(
            fn ($item) => $item['price'] * $item['quantity'],
            $items
        ));
        session()->put('checkout_selected', array_keys($items));

        // Xem trước giảm giá nếu đang áp mã — số tiền chính xác sẽ được tính lại khi đặt hàng.
        $coupon   = null;
        $discount = 0.0;

        if (session()->has('coupon_code')) {
            $coupon = Coupon::where('code', session('coupon_code'))->first();
            $check  = $coupon ? $coupon->checkEligibility(Auth::user(), (float) $total) : ['ok' => false, 'message' => 'Mã giảm giá không hợp lệ.'];

            if (! $check['ok']) {
                session()->forget('coupon_code');
                $coupon = null;
            } else {
                $discount = $coupon->calculateDiscount((float) $total);
            }
        }

        return view('cart.checkout', compact('items', 'total', 'coupon', 'discount'));
    }

    /** Đếm tổng số món trong giỏ */
    private function countItems(array $cart): int
    {
        return array_sum(array_column($cart, 'quantity'));
    }
}