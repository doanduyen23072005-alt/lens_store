{{-- resources/views/admin/reports/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Báo cáo doanh thu')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Báo cáo doanh thu</h1>
        <p class="page-sub">Doanh thu tính theo ngày tạo đơn: MoMo đã thanh toán hoặc COD đã giao thành công. Không gồm đơn đã hủy.</p>
    </div>
</div>

<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.reports.index') }}">Bảng số liệu</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.reports.charts') }}">Biểu đồ</a>
    </li>
</ul>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
        <div class="stat">
            <div class="stat-label">Tổng số đơn hàng</div>
            <div class="stat-value">{{ number_format($totalOrders) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="stat">
            <div class="stat-label">Tổng số khách hàng</div>
            <div class="stat-value">{{ number_format($totalCustomers) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-4">
        <div class="stat accent">
            <div class="stat-label">Tổng doanh thu</div>
            <div class="stat-value">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div>
        </div>
    </div>
</div>

<div class="card-panel mb-4">
    <div class="panel-head">
        <strong>Doanh thu theo danh mục</strong>
        <span class="text-muted small ms-auto">Tính theo giá sản phẩm khi đặt hàng, không gồm phí vận chuyển</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Danh mục</th>
                    <th class="text-end">Số lượng bán</th>
                    <th class="text-end">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($categoryRevenue as $revenue)
                <tr>
                    <td>{{ $revenue->category_name ?? 'Chưa phân loại' }}</td>
                    <td class="text-end num">{{ number_format($revenue->total_qty) }}</td>
                    <td class="text-end num fw-semibold">{{ number_format($revenue->total_revenue, 0, ',', '.') }} đ</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">Chưa có doanh thu.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card-panel mb-4">
    <div class="panel-head">
        <strong>Sản phẩm bán chạy</strong>
        <span class="text-muted small ms-auto">Top {{ $bestSellingProducts->count() }} theo số lượng đã bán (đơn đã tính doanh thu)</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th>Sản phẩm</th>
                    <th class="text-end">Số lượng đã bán</th>
                    <th class="text-end">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($bestSellingProducts as $i => $product)
                <tr>
                    <td class="text-muted num">{{ $i + 1 }}</td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td class="text-end num">{{ number_format($product->total_qty) }}</td>
                    <td class="text-end num fw-semibold">{{ number_format($product->total_revenue, 0, ',', '.') }} đ</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Chưa có sản phẩm nào được bán.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach ([
    ['Doanh thu theo ngày', 'Ngày', 'date', $revenueByDate, 'd/m/Y'],
    ['Doanh thu theo tháng', 'Tháng', 'month', $revenueByMonth, 'm/Y'],
    ['Doanh thu theo năm', 'Năm', 'year', $revenueByYear, null],
] as [$title, $label, $field, $rows, $format])
    <div class="card-panel mb-4">
        <div class="panel-head"><strong>{{ $title }}</strong></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ $label }}</th>
                        <th class="text-end">Số đơn đã thanh toán</th>
                        <th class="text-end">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rows as $revenue)
                    <tr>
                        <td>{{ $format ? \Carbon\Carbon::parse($revenue->{$field}.($field === 'month' ? '-01' : ''))->format($format) : $revenue->{$field} }}</td>
                        <td class="text-end num">{{ number_format($revenue->order_count) }}</td>
                        <td class="text-end num fw-semibold">{{ number_format($revenue->total_revenue, 0, ',', '.') }} đ</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty">Chưa có doanh thu.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection
