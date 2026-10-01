{{-- resources/views/products/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Ống kính')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Ống kính máy ảnh</h1>
    
    </div>
    <a href="{{ route('products.create') }}" class="btn btn-ink">+ Thêm ống kính</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label">Số ống kính</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label">Phân loại</div>
            <div class="stat-value">{{ $stats['categories'] }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label">Tổng tồn kho</div>
            <div class="stat-value">{{ number_format($stats['inventory'], 0, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat {{ $stats['out_stock'] > 0 ? 'accent' : '' }}">
            <div class="stat-label">Hết hàng</div>
            <div class="stat-value">{{ $stats['out_stock'] }}</div>
        </div>
    </div>
</div>

<div class="card-panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('products.index') }}" class="d-flex gap-2 flex-wrap w-100">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" style="max-width:280px"
                   placeholder="Tìm theo tên hoặc mã">
            <select name="category_id" class="form-select" style="max-width:220px">
                <option value="">Tất cả phân loại</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-line">Lọc</button>
            @if (request('q') || request('category_id'))
                <a href="{{ route('products.index') }}" class="btn btn-line">Xoá bộ lọc</a>
            @endif
        </form>
    </div>

    @if ($products->count())
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width:70px">Ảnh</th>
                        <th>Mã</th>
                        <th>Tên ống kính</th>
                        <th>Phân loại</th>
                        <th class="text-end">Giá</th>
                        <th class="text-center">Số lượng</th>
                        <th class="text-end" style="width:210px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>
                            @if ($product->image)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="thumb">
                            @else
                                <div class="thumb-empty">—</div>
                            @endif
                        </td>
                        <td><span class="code-chip">{{ $product->code }}</span></td>
                        <td>
                            <a href="{{ route('products.show', $product->id) }}" class="fw-semibold text-dark">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td>
                            <span class="tag">{{ $product->category?->name ?? 'Chưa gán' }}</span>
                        </td>
                        <td class="text-end num fw-semibold">{{ $product->price_formatted }}</td>
                        <td class="text-center">
                            <span class="tag {{ $product->quantity <= 0 ? 'tag-danger' : ($product->quantity < 5 ? 'tag-warn' : 'tag-ok') }}">
                                {{ $product->quantity }} · {{ $product->stock_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-line btn-sm">Xem</a>
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-line btn-sm">Sửa</a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Xoá ống kính “{{ $product->name }}”?')">
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
                Hiển thị {{ $products->firstItem() }}–{{ $products->lastItem() }} trong {{ $products->total() }} ống kính
            </span>
            {{ $products->links() }}
        </div>
    @else
        <div class="empty">
            <h4>Chưa có ống kính nào ở đây</h4>
            <p>Thêm ống kính đầu tiên để bắt đầu theo dõi giá và tồn kho.</p>
            <a href="{{ route('products.create') }}" class="btn btn-ink">Thêm ống kính</a>
        </div>
    @endif
</div>
@endsection