{{-- resources/views/products/show.blade.php --}}
@extends('layouts.admin')
@section('title', $product->name)

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">{{ $product->name }}</h1>
        <p class="page-sub"><span class="code-chip">{{ $product->code }}</span></p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-ink">Sửa</a>
        <a href="{{ route('products.index') }}" class="btn btn-line">← Về danh sách</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-panel panel-body">
            @if ($product->image)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                     style="width:100%;border-radius:11px;border:1px solid var(--line)">
            @else
                <div class="empty">Sản phẩm này chưa có ảnh.</div>
            @endif
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-panel">
            <div class="panel-head"><strong>Thông tin sản phẩm</strong></div>
            <div class="panel-body">
                <table class="table">
                    <tbody>
                        <tr><td class="text-muted" style="width:170px">Phân loại</td>
                            <td>
                                @if ($product->category)
                                    <a href="{{ route('categories.show', $product->category->id) }}" class="tag">
                                        {{ $product->category->name }}
                                    </a>
                                @else
                                    <span class="tag">Chưa gán</span>
                                @endif
                            </td></tr>
                        <tr><td class="text-muted">Giá</td>
                            <td class="num fw-semibold">{{ $product->price_formatted }}</td></tr>
                        <tr><td class="text-muted">Số lượng</td>
                            <td>
                                <span class="tag {{ $product->quantity <= 0 ? 'tag-danger' : ($product->quantity < 5 ? 'tag-warn' : 'tag-ok') }}">
                                    {{ $product->quantity }} · {{ $product->stock_label }}
                                </span>
                            </td></tr>
                        <tr><td class="text-muted">Ngày tạo</td>
                            <td class="num">{{ $product->created_at?->format('d/m/Y H:i') }}</td></tr>
                        <tr><td class="text-muted">Cập nhật</td>
                            <td class="num">{{ $product->updated_at?->format('d/m/Y H:i') }}</td></tr>
                    </tbody>
                </table>

                <div class="mt-3">
                    <div class="stat-label mb-2">Mô tả</div>
                    <p class="mb-0">{{ $product->description ?: 'Chưa có mô tả.' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection