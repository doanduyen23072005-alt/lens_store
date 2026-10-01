{{-- resources/views/admin/finance/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Thống kê tài chính')

@php
    $tagClass = fn ($status) => match ($status) {
        'paid', 'refunded' => 'tag-ok',
        'pending', 'initiated', 'refund_pending' => 'tag-warn',
        'failed', 'cancelled' => 'tag-danger',
        default => '',
    };
@endphp

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Thống kê tài chính</h1>
        <p class="page-sub">Tổng hợp giá trị thanh toán theo trạng thái và phương thức.</p>
    </div>
</div>

<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.finance.index') }}">Thống kê chỉ số</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.finance.transactions') }}">Giao dịch thanh toán</a>
    </li>
</ul>

<div class="card-panel mb-4">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.finance.index') }}" class="d-flex gap-2 flex-wrap w-100 align-items-end">
            <div>
                <label class="form-label small text-muted mb-1">Tìm đơn hàng</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"
                       style="max-width:220px" placeholder="Mã đơn, tên hoặc số điện thoại">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Từ ngày tạo đơn</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control" style="max-width:160px">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Đến ngày tạo đơn</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control" style="max-width:160px">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Số tiền từ (đ)</label>
                <input type="number" name="min_amount" value="{{ $filters['min_amount'] ?? '' }}" class="form-control" style="max-width:150px" placeholder="Không giới hạn" min="0">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Số tiền đến (đ)</label>
                <input type="number" name="max_amount" value="{{ $filters['max_amount'] ?? '' }}" class="form-control" style="max-width:150px" placeholder="Không giới hạn" min="0">
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Phương thức</label>
                <select name="gateway" class="form-select" style="max-width:160px">
                    <option value="">Tất cả</option>
                    @foreach ($methods as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['gateway'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label small text-muted mb-1">Trạng thái thanh toán</label>
                <select name="payment_status" class="form-select" style="max-width:180px">
                    <option value="">Tất cả</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['payment_status'] ?? null) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-line">Áp dụng bộ lọc</button>
                <a href="{{ route('admin.finance.index') }}" class="btn btn-line">Xoá bộ lọc</a>
            </div>
        </form>
    </div>
    <div class="panel-body">
        <span class="text-muted small">
            Có {{ number_format($summary->order_count) }} đơn phù hợp bộ lọc. Số tiền bao gồm phí vận chuyển; thống kê theo ngày tạo đơn trên toàn bộ kết quả lọc.
        </span>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat accent">
            <div class="stat-label">Tổng giá trị đơn hàng</div>
            <div class="stat-value">{{ number_format($summary->total_amount, 0, ',', '.') }} đ</div>
            <div class="text-muted small mt-1">{{ number_format($summary->order_count) }} đơn, bao gồm đơn đã hủy</div>
        </div>
    </div>
    @foreach ($statuses as $key => $label)
        @php $row = $statusTotals[$key] ?? null; @endphp
        <div class="col-6 col-lg-3">
            <div class="stat">
                <div class="stat-label">{{ $label }}</div>
                <div class="stat-value">{{ number_format($row->total_amount ?? 0, 0, ',', '.') }} đ</div>
                <div class="text-muted small mt-1">{{ number_format($row->order_count ?? 0) }} đơn</div>
            </div>
        </div>
    @endforeach
</div>

<div class="card-panel mb-4">
    <div class="panel-head"><strong>Thống kê theo phương thức</strong></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Phương thức</th>
                    <th class="text-end">Số đơn</th>
                    <th class="text-end">Tổng giá trị</th>
                    <th class="text-end">Đã thanh toán</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($methods as $key => $label)
                @php $row = $methodTotals[$key] ?? null; @endphp
                <tr>
                    <td>
                        <a href="{{ route('admin.finance.transactions', ['gateway' => $key]) }}">{{ $label }}</a>
                    </td>
                    <td class="text-end num">{{ number_format($row->order_count ?? 0) }}</td>
                    <td class="text-end num fw-semibold">{{ number_format($row->total_amount ?? 0, 0, ',', '.') }} đ</td>
                    <td class="text-end num">{{ number_format($row->paid_amount ?? 0, 0, ',', '.') }} đ</td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">Chưa có giao dịch nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
