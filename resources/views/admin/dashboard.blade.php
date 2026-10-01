{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.admin')
@section('title', 'Tổng quan')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Xin chào, {{ Auth::user()->name }}</h1>
        <p class="page-sub">Tình hình kho ống kính hôm nay, {{ now()->format('d/m/Y') }}.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.create') }}" class="btn btn-ink">+ Thêm ống kính</a>
        <a href="{{ route('home') }}" class="btn btn-line" target="_blank">Xem cửa hàng</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat">
            <div class="stat-label">Ống kính</div>
            <div class="stat-value">{{ $stats['products'] }}</div>
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
            <div class="stat-label">Khách hàng</div>
            <div class="stat-value">{{ $stats['customers'] }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat {{ $stats['out_stock'] > 0 ? 'accent' : '' }}">
            <div class="stat-label">Hết hàng</div>
            <div class="stat-value">{{ $stats['out_stock'] }}</div>
        </div>
    </div>
</div>

<div class="card-panel mb-4">
    <div class="panel-body d-flex justify-content-between flex-wrap gap-3">
        <div>
            <div class="stat-label">Tổng số lượng tồn</div>
            <div class="stat-value">{{ number_format($stats['inventory'], 0, ',', '.') }}</div>
        </div>
        <div>
            <div class="stat-label">Giá trị hàng tồn</div>
            <div class="stat-value">{{ number_format($stats['value'], 0, ',', '.') }} ₫</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-head"><strong>Sắp hết hàng</strong></div>

            @if ($lowStock->count())
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ống kính</th>
                                <th class="text-center">Còn lại</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($lowStock as $product)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $product->name }}</div>
                                    <div class="text-muted small">{{ $product->category?->name }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="tag {{ $product->quantity <= 0 ? 'tag-danger' : 'tag-warn' }}">
                                        {{ $product->quantity }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-line btn-sm">Nhập thêm</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty"><h4>Kho ổn định</h4><p>Không có ống kính nào dưới 5 chiếc.</p></div>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-head"><strong>Mới thêm gần đây</strong></div>

            @if ($latest->count())
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ống kính</th>
                                <th class="text-end">Giá</th>
                                <th class="text-end">Ngày thêm</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($latest as $product)
                            <tr>
                                <td>
                                    <a href="{{ route('products.show', $product->id) }}" class="text-dark fw-semibold">
                                        {{ $product->name }}
                                    </a>
                                    <div class="text-muted small">{{ $product->category?->name }}</div>
                                </td>
                                <td class="text-end num">{{ $product->price_formatted }}</td>
                                <td class="text-end num text-muted small">{{ $product->created_at?->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty"><h4>Chưa có dữ liệu</h4></div>
            @endif
        </div>
    </div>
</div>
@endsection