<?php
// app/Http/Controllers/ShopController.php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /** Trang chủ dành cho khách: danh sách ống kính đang bán */
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('quantity', '>', 0)
            ->when($request->q, fn ($query, $q) => $query->where('name', 'like', "%{$q}%"))
            ->when($request->category_id, fn ($query, $id) => $query->where('category_id', $id))
            ->when($request->price_min, fn ($query, $min) => $query->where('price', '>=', $min))
            ->when($request->price_max, fn ($query, $max) => $query->where('price', '<=', $max))
            ->when($request->sort, function ($query, $sort) {
                match ($sort) {
                    'price_asc'  => $query->orderBy('price', 'asc'),
                    'price_desc' => $query->orderBy('price', 'desc'),
                    default      => $query->latest(),
                };
            }, fn ($query) => $query->latest())
            ->paginate(9)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        // Sản phẩm bán chạy: xếp theo tổng số lượng đã bán trong tất cả đơn hàng.
        $bestSellers = Product::query()
            ->where('quantity', '>', 0)
            ->withSum('orderItems as sold_qty', 'quantity')
            ->having('sold_qty', '>', 0)
            ->orderByDesc('sold_qty')
            ->take(4)
            ->get();

        return view('shop.index', compact('products', 'categories', 'bestSellers'));
    }

    /** Trang chi tiết một ống kính dành cho khách */
    public function show(Product $product)
    {
        $product->load('category')->loadCount('reviews')->loadAvg('reviews', 'rating');

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(3)
            ->get();

        $reviews    = $product->reviews()->with('user')->latest()->paginate(5, ['*'], 'review_page');
        $canReview  = Auth::check() && $product->purchasedAndDeliveredBy(Auth::id());
        $myReview   = Auth::check() ? $product->reviews()->where('user_id', Auth::id())->first() : null;

        return view('shop.show', compact('product', 'related', 'reviews', 'canReview', 'myReview'));
    }
}