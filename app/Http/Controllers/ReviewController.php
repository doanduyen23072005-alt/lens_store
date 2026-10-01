<?php
// app/Http/Controllers/ReviewController.php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /** Khách đánh giá sản phẩm sau khi đã mua và nhận hàng thành công. Gửi lại = cập nhật đánh giá cũ. */
    public function store(Request $request, Product $product)
    {
        abort_unless(
            $product->purchasedAndDeliveredBy(Auth::id()),
            403,
            'Bạn cần mua và nhận sản phẩm này thành công trước khi đánh giá.'
        );

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [], ['rating' => 'số sao', 'comment' => 'nhận xét']);

        $order = $product->orderItems()
            ->whereHas('order', fn ($q) => $q->where('user_id', Auth::id())->where('shipping_status', 'delivered'))
            ->with('order')
            ->latest('id')
            ->first()
            ?->order;

        Review::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $product->id],
            ['order_id' => $order?->id, 'rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        return back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }

    /** Khách xoá đánh giá của chính mình. */
    public function destroy(Review $review)
    {
        abort_if($review->user_id !== Auth::id(), 403);

        $review->delete();

        return back()->with('success', 'Đã xoá đánh giá của bạn.');
    }
}
