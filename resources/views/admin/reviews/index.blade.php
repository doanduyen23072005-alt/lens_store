{{-- resources/views/admin/reviews/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Đánh giá sản phẩm')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Đánh giá sản phẩm</h1>
        <p class="page-sub">Kiểm duyệt đánh giá của khách hàng, xoá nếu vi phạm.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label">Tổng số đánh giá</div>
            <div class="stat-value">{{ number_format($totalReviews) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat accent">
            <div class="stat-label">Điểm trung bình</div>
            <div class="stat-value">{{ $avgRating ? number_format($avgRating, 1) : '—' }}/5</div>
        </div>
    </div>
</div>

<div class="card-panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="d-flex gap-2 flex-wrap w-100">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"
                   style="max-width:280px" placeholder="Tìm theo tên khách, sản phẩm, nội dung">
            <select name="rating" class="form-select" style="max-width:150px">
                <option value="">Tất cả số sao</option>
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected(($filters['rating'] ?? null) == $i)>{{ $i }} sao</option>
                @endfor
            </select>
            <button class="btn btn-line">Lọc</button>
            @if (request('search') || request('rating'))
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-line">Xoá bộ lọc</a>
            @endif
        </form>
    </div>

    @if ($reviews->count())
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Khách hàng</th>
                        <th class="text-center">Số sao</th>
                        <th>Nhận xét</th>
                        <th>Ngày</th>
                        <th class="text-end" style="width:100px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($reviews as $review)
                    <tr>
                        <td>
                            @if ($review->product)
                                <a href="{{ route('shop.show', $review->product_id) }}" class="fw-semibold text-dark" target="_blank">
                                    {{ $review->product->name }}
                                </a>
                            @else
                                <span class="text-muted">Sản phẩm đã xoá</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $review->user->name ?? 'Đã xoá' }}</div>
                            <div class="text-muted small">{{ $review->user->email ?? '' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="tag {{ $review->rating >= 4 ? 'tag-ok' : ($review->rating <= 2 ? 'tag-danger' : 'tag-warn') }}">
                                {{ $review->rating }} ★
                            </span>
                        </td>
                        <td class="text-muted small" style="max-width:280px">
                            {{ $review->comment ? Str::limit($review->comment, 100) : '—' }}
                        </td>
                        <td class="text-muted small">{{ $review->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST"
                                  onsubmit="return confirm('Xoá đánh giá này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-line btn-sm text-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                Hiển thị {{ $reviews->firstItem() }}–{{ $reviews->lastItem() }} trong {{ $reviews->total() }} đánh giá
            </span>
            {{ $reviews->links() }}
        </div>
    @else
        <div class="empty">
            <h4>Chưa có đánh giá nào</h4>
            <p>Đánh giá của khách sẽ xuất hiện ở đây sau khi đơn hàng được giao thành công.</p>
        </div>
    @endif
</div>
@endsection
