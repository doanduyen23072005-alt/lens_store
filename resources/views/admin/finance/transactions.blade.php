{{-- resources/views/admin/finance/transactions.blade.php --}}
@extends('layouts.admin')
@section('title', 'Giao dịch thanh toán')

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
        <h1 class="page-title">Giao dịch thanh toán</h1>
        <p class="page-sub">Tra cứu thanh toán theo đơn hàng và cập nhật trạng thái COD.</p>
    </div>
</div>

<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.finance.index') }}">Thống kê chỉ số</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.finance.transactions') }}">Giao dịch thanh toán</a>
    </li>
</ul>

<div class="card-panel mb-4">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.finance.transactions') }}" class="d-flex gap-2 flex-wrap w-100 align-items-end">
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
            <div>
                <label class="form-label small text-muted mb-1">Sắp xếp</label>
                <select name="sort" class="form-select" style="max-width:170px">
                    <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Mới nhất</option>
                    <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>Cũ nhất</option>
                    <option value="amount_desc" @selected(($filters['sort'] ?? null) === 'amount_desc')>Số tiền giảm dần</option>
                    <option value="amount_asc" @selected(($filters['sort'] ?? null) === 'amount_asc')>Số tiền tăng dần</option>
                </select>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-line">Áp dụng bộ lọc</button>
                <a href="{{ route('admin.finance.transactions') }}" class="btn btn-line">Xoá bộ lọc</a>
            </div>
        </form>
    </div>
    <div class="panel-body">
        <span class="text-muted small">
            Có {{ number_format($orders->total()) }} đơn phù hợp bộ lọc. Số tiền bao gồm phí vận chuyển; ngày lọc là ngày tạo đơn.
        </span>
    </div>
</div>

<div class="card-panel">
    <div class="panel-head">
        <strong>Danh sách giao dịch ({{ number_format($orders->total()) }} đơn)</strong>
        <span class="text-muted small ms-auto">COD: xác nhận thu tiền hoặc thất bại; đơn đã thu tiền có thể chuyển sang chờ hoàn tiền rồi xác nhận đã hoàn tiền.</span>
    </div>

    @if ($orders->count())
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Phương thức</th>
                        <th class="text-end">Số tiền</th>
                        <th>Thanh toán</th>
                        <th style="width:260px">Cập nhật</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>
                            <span class="code-chip">DH{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                            <div class="text-muted small mt-1">{{ \Illuminate\Support\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $order->name }}</div>
                            <div class="text-muted small">{{ $order->phone }}</div>
                        </td>
                        <td>{{ $methods[$order->gateway] ?? $order->gateway }}</td>
                        <td class="text-end num fw-semibold">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                        <td>
                            <span class="tag {{ $tagClass($order->payment_status) }}">
                                {{ $statuses[$order->payment_status] ?? $order->payment_status }}
                            </span>
                            @if ($order->payment_paid_at)
                                <div class="text-muted small mt-1">{{ \Illuminate\Support\Carbon::parse($order->payment_paid_at)->format('d/m/Y H:i') }}</div>
                            @endif
                        </td>
                        <td>
                            @php
                                $canRefund = $order->shipping_status === 'returned';
                                $options = collect($codTransitions[$order->payment_status] ?? [])
                                    ->reject(fn ($s) => $s === $order->payment_status)
                                    ->reject(fn ($s) => in_array($s, ['refund_pending', 'refunded'], true) && !$canRefund);
                                if ($order->gateway === 'momo') {
                                    // MoMo tự xác nhận thu tiền qua webhook — admin chỉ được thao tác phần hoàn tiền.
                                    $options = $options->filter(fn ($s) => in_array($s, ['refund_pending', 'refunded'], true));
                                }
                            @endphp
                            @if (in_array($order->gateway, ['cod', 'momo'], true) && $options->count())
                                <form action="{{ route('admin.finance.update-status', $order->id) }}" method="POST"
                                      class="d-flex gap-1" onsubmit="return confirm('Cập nhật trạng thái thanh toán đơn #{{ $order->id }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="current_payment_status" value="{{ $order->payment_status }}">
                                    <input type="hidden" name="current_order_status" value="{{ $order->status }}">
                                    <input type="hidden" name="current_payment_id" value="{{ $order->payment_id ?? 0 }}">
                                    <select name="payment_status" class="form-select form-select-sm" style="max-width:160px">
                                        @foreach ($options as $option)
                                            <option value="{{ $option }}">{{ $statuses[$option] ?? $option }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-line btn-sm">Lưu</button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                Hiển thị {{ $orders->firstItem() }}–{{ $orders->lastItem() }} trong {{ $orders->total() }} đơn hàng
            </span>
            {{ $orders->links() }}
        </div>
    @else
        <div class="empty">
            <h4>Không có đơn hàng phù hợp với bộ lọc</h4>
            <p>Thử đổi điều kiện lọc để xem thêm giao dịch.</p>
        </div>
    @endif
</div>
@endsection
