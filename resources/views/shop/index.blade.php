{{-- resources/views/shop/index.blade.php --}}
@extends('layouts.shop')
@section('title', 'Ống kính đang bán')

@section('content')
<div class="shop-hero">
    <h1 class="mb-1">Ống kính máy ảnh chính hãng</h1>
    <p class="page-sub-light mb-4">{{ $products->total() }} ống kính đang còn hàng · giao nhanh toàn quốc.</p>

    <form method="GET" action="{{ route('home') }}" class="filter-form d-flex gap-2 flex-wrap">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
               style="max-width:200px" placeholder="Tìm ống kính">
        <select name="category_id" class="form-select" style="max-width:180px">
            <option value="">Tất cả phân loại</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <input type="number" name="price_min" value="{{ request('price_min') }}" class="form-control"
               style="max-width:130px" placeholder="Giá từ" min="0">
        <input type="number" name="price_max" value="{{ request('price_max') }}" class="form-control"
               style="max-width:130px" placeholder="Giá đến" min="0">
        <select name="sort" class="form-select" style="max-width:160px">
            <option value="" @selected(!request('sort'))>Mới nhất</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá tăng dần</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá giảm dần</option>
        </select>
        <button class="btn btn-ink" style="background:var(--coating);border-color:var(--coating);color:#16181C">Lọc</button>
        @if (request()->anyFilled(['q', 'category_id', 'price_min', 'price_max', 'sort']))
            <a href="{{ route('home') }}" class="btn btn-ghost">Xoá lọc</a>
        @endif
    </form>
</div>

@if ($bestSellers->count() && !request()->anyFilled(['q', 'category_id', 'price_min', 'price_max', 'sort']))
    <div class="mb-4">
        <h2 style="font-size:18px" class="mb-3">🔥 Sản phẩm bán chạy</h2>
        <div class="row g-3">
            @foreach ($bestSellers as $product)
                <div class="col-sm-6 col-lg-3">
                    <div class="lens-card position-relative">
                        <span class="best-seller-badge">Bán chạy · {{ $product->sold_qty }} đã bán</span>
                        <a href="{{ route('shop.show', $product->id) }}">
                            @if ($product->image)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                            @else
                                <div class="no-img">Chưa có ảnh</div>
                            @endif
                        </a>
                        <div class="lens-body">
                            <h3 style="font-size:15px;margin:0">
                                <a href="{{ route('shop.show', $product->id) }}" style="color:inherit;text-decoration:none">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <div class="price">{{ $product->price_formatted }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@if ($products->count())
    <div class="row g-3">
        @foreach ($products as $product)
            <div class="col-sm-6 col-lg-4">
                <div class="lens-card">
                    <a href="{{ route('shop.show', $product->id) }}">
                        @if ($product->image)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                        @else
                            <div class="no-img">Chưa có ảnh</div>
                        @endif
                    </a>

                    <div class="lens-body">
                        <span class="tag">{{ $product->category?->name }}</span>

                        <h3 style="font-size:16px;margin:0">
                            <a href="{{ route('shop.show', $product->id) }}" style="color:inherit;text-decoration:none">
                                {{ $product->name }}
                            </a>
                        </h3>

                        <p class="text-muted mb-0" style="font-size:13px">
                            {{ Str::limit($product->description, 70) }}
                        </p>

                        @if ($product->reviews_count)
                            @include('shop.partials.stars', ['rating' => $product->reviews_avg_rating, 'count' => $product->reviews_count])
                        @endif

                        <div class="price">{{ $product->price_formatted }}</div>

                        <div class="card-actions">
                            @auth
                                <button type="button"
                                        class="btn btn-ink btn-sm flex-fill add-to-cart"
                                        data-url="{{ route('cart.add', $product->id) }}"
                                        data-name="{{ $product->name }}">
                                    Thêm vào giỏ
                                </button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-ink btn-sm flex-fill">
                                    Đăng nhập để mua
                                </a>
                            @endauth

                            <a href="{{ route('shop.show', $product->id) }}" class="btn btn-line btn-sm">Chi tiết</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $products->links() }}
    </div>
@else
    <div class="text-center text-muted py-5">
        <h3>Không tìm thấy ống kính phù hợp</h3>
        <p>Thử bỏ bớt bộ lọc hoặc quay lại sau.</p>
        <a href="{{ route('home') }}" class="btn btn-line">Xem tất cả</a>
    </div>
@endif

@push('styles')
<style>
    .card-actions { display: flex; gap: 8px; margin-top: 10px; }

    .add-to-cart.done {
        background: #1E7F5C;
        border-color: #1E7F5C;
    }
</style>
@endpush

@push('scripts')
<script>
    document.querySelectorAll('.add-to-cart').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const original = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Đang thêm...';

            try {
                const res = await fetch(btn.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ quantity: 1 }),
                });

                if (!res.ok) throw new Error('Máy chủ trả về lỗi ' + res.status);

                const data = await res.json();

                setCartCount(data.count);
                showToast(data.message);

                btn.textContent = 'Đã thêm ✓';
                btn.classList.add('done');

                setTimeout(function () {
                    btn.textContent = original;
                    btn.classList.remove('done');
                    btn.disabled = false;
                }, 1400);
            } catch (e) {
                showToast('Không thêm được vào giỏ. Hãy thử lại.');
                btn.textContent = original;
                btn.disabled = false;
            }
        });
    });
</script>
@endpush
@endsection