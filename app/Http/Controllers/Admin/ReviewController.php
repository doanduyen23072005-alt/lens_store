<?php
// app/Http/Controllers/Admin/ReviewController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** Danh sách đánh giá của toàn bộ sản phẩm — admin kiểm duyệt / xoá nếu vi phạm. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $reviews = Review::with(['user', 'product'])
            ->when($request->filled('search'), function ($query) use ($filters) {
                $search = trim($filters['search']);
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                        ->orWhere('comment', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('rating'), fn ($query) => $query->where('rating', $filters['rating']))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews'      => $reviews,
            'filters'      => $filters,
            'totalReviews' => Review::count(),
            'avgRating'    => Review::avg('rating'),
        ]);
    }

    /** Admin xoá đánh giá vi phạm (spam, nội dung không phù hợp...). */
    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'Đã xoá đánh giá.');
    }
}
