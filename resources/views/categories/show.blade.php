{{-- resources/views/categories/show.blade.php --}}
@extends('layouts.admin')
@section('title', $category->name)

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">{{ $category->name }}</h1>
        <p class="page-sub">
            <span class="code-chip">{{ $category->code }}</span>
            <span class="tag {{ $category->is_active ? 'tag-ok' : '' }}">
                {{ $category->is_active ? 'Đang hiện' : 'Đang ẩn' }}
            </span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-ink">Sửa</a>
        <a href="{{ route('categories.index') }}" class="btn btn-line">← Về danh sách</a>
    </div>
</div>

<div class="card-panel mb-4">
    <div class="panel-body">
        <div class="stat-label mb-2">Mô tả</div>
        <p class="mb-0">{{ $category->description ?: 'Chưa có mô tả.' }}</p>
    </div>
</div>

<div class="card-panel">
    <div class="panel-head">
        <strong>Ống kính thuộc phân loại này</strong>
        <span class="tag ms-auto">{{ $category->products->count() }} sản phẩm</span>
    </div>

    @if ($category->products->count())
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:70px">Ảnh</th>
                        <th>Mã</th>
                        <th>Tên</th>
                        <th class="text-end">Giá</th>
                        <th class="text-center">Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($category->products as $product)
                    <tr>
                        <td>
                            @if ($product->image)
                                <img src="{{ $product->image_url }}" class="thumb" alt="{{ $product->name }}">
                            @else
                                <div class="thumb-empty">—</div>
                            @endif
                        </td>
                        <td><span class="code-chip">{{ $product->code }}</span></td>
                        <td><a href="{{ route('products.show', $product->id) }}" class="text-dark fw-semibold">{{ $product->name }}</a></td>
                        <td class="text-end num">{{ $product->price_formatted }}</td>
                        <td class="text-center num">{{ $product->quantity }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">
            <h4>Phân loại này chưa có ống kính</h4>
            <a href="{{ route('products.create') }}" class="btn btn-ink">Thêm ống kính</a>
        </div>
    @endif
</div>
@endsection