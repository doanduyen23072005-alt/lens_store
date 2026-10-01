{{-- resources/views/shop/show.blade.php --}}
@extends('layouts.shop')
@section('title', $product->name)

@section('content')
<a href="{{ route('home') }}" class="text-muted" style="font-size:14px">← Về danh sách</a>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        @if ($product->image)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                 style="width:100%;border-radius:14px;border:1px solid var(--line)">
        @else
            <div class="lens-card"><div class="no-img" style="height:320px">Chưa có ảnh</div></div>
        @endif
    </div>

    <div class="col-lg-6">
        <span class="tag">{{ $product->category?->name }}</span>
        <h1 class="mt-2 mb-1">{{ $product->name }}</h1>

        @if ($product->reviews_count)
            <div class="mb-2">
                @include('shop.partials.stars', ['rating' => $product->reviews_avg_rating, 'count' => $product->reviews_count])
                <a href="#reviews" class="text-muted" style="font-size:13px">Xem đánh giá</a>
            </div>
        @endif

        <div class="price mb-3" style="font-size:26px">{{ $product->price_formatted }}</div>

        <p>{{ $product->description ?: 'Chưa có mô tả cho sản phẩm này.' }}</p>

        <table class="table" style="max-width:420px">
            <tbody>
                <tr><td class="text-muted">Mã sản phẩm</td><td>{{ $product->code }}</td></tr>
                <tr><td class="text-muted">Phân loại</td><td>{{ $product->category?->name }}</td></tr>
                <tr><td class="text-muted">Tình trạng</td><td>{{ $product->stock_label }}</td></tr>
            </tbody>
        </table>

        @auth
            @if ($product->quantity > 0)
                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="d-flex gap-2 flex-wrap align-items-center">
                    @csrf
                    <label class="text-muted" for="quantity" style="font-size:14px">Số lượng</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1"
                           max="{{ $product->quantity }}" class="form-control" style="width:90px">
                    <button type="submit" class="btn btn-ink px-4">Thêm vào giỏ hàng</button>
                    <a href="{{ route('cart.index') }}" class="btn btn-line">Xem giỏ hàng</a>
                </form>
            @else
                <button class="btn btn-ink px-4" disabled>Sản phẩm đã hết hàng</button>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn btn-ink px-4">Đăng nhập để đặt mua</a>
        @endauth
    </div>
</div>

@if ($related->count())
    <h2 class="mt-5 mb-3" style="font-size:20px">Ống kính cùng phân loại</h2>
    <div class="row g-3">
        @foreach ($related as $item)
            <div class="col-sm-6 col-lg-4">
                <div class="lens-card">
                    <a href="{{ route('shop.show', $item->id) }}">
                        @if ($item->image)
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                        @else
                            <div class="no-img">Chưa có ảnh</div>
                        @endif
                    </a>
                    <div class="lens-body">
                        <h3 style="font-size:15px;margin:0">{{ $item->name }}</h3>
                        <div class="price">{{ $item->price_formatted }}</div>
                        <a href="{{ route('shop.show', $item->id) }}" class="btn btn-line btn-sm mt-2">Xem chi tiết</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div id="reviews" class="mt-5">
    <h2 class="mb-3" style="font-size:20px">
        Đánh giá sản phẩm
        @if ($product->reviews_count)
            <span class="text-muted" style="font-size:15px;font-weight:400">
                ({{ number_format($product->reviews_avg_rating, 1) }}/5 · {{ $product->reviews_count }} đánh giá)
            </span>
        @endif
    </h2>

    @auth
        @if ($canReview)
            <div class="bg-white border rounded-4 p-4 mb-4">
                <h3 style="font-size:15px" class="mb-3">
                    {{ $myReview ? 'Cập nhật đánh giá của bạn' : 'Bạn đã nhận hàng — hãy đánh giá sản phẩm này' }}
                </h3>
                <form action="{{ route('reviews.store', $product->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label d-block">Số sao</label>
                        <select name="rating" class="form-select" style="max-width:160px" required>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected(old('rating', $myReview->rating ?? 5) == $i)>{{ $i }} sao</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhận xét (không bắt buộc)</label>
                        <textarea name="comment" class="form-control" rows="3" maxlength="1000"
                                  placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm...">{{ old('comment', $myReview->comment ?? '') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-ink">{{ $myReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}</button>
                </form>
            </div>
        @elseif (!$myReview)
            <div class="alert alert-info">Bạn cần mua và nhận sản phẩm này thành công mới có thể đánh giá.</div>
        @endif
    @endauth

    @if ($reviews->count())
        <div class="d-flex flex-column gap-3">
            @foreach ($reviews as $review)
                <div class="bg-white border rounded-4 p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="fw-semibold" style="font-size:14px">{{ $review->user->name ?? 'Khách hàng' }}</div>
                            @include('shop.partials.stars', ['rating' => $review->rating])
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size:12px">{{ $review->created_at->format('d/m/Y') }}</span>
                            @if (Auth::id() === $review->user_id)
                                <form action="{{ route('reviews.destroy', $review->id) }}" method="POST"
                                      onsubmit="return confirm('Xoá đánh giá của bạn?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-line btn-sm text-danger">Xoá</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @if ($review->comment)
                        <p class="mb-0 mt-2" style="font-size:14px">{{ $review->comment }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-3">{{ $reviews->links() }}</div>
    @else
        <p class="text-muted">Chưa có đánh giá nào cho sản phẩm này.</p>
    @endif
</div>
@endsection