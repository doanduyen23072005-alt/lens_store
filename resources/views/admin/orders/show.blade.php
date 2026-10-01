{{-- resources/views/admin/orders/show.blade.php --}}
@extends('layouts.admin')
@section('title', 'Chi tiết đơn hàng #'.$order->id)

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Đơn hàng DH{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h1>
        <p class="page-sub">Đặt lúc {{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-line">← Quay lại danh sách</a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-panel mb-4">
            <div class="panel-head"><strong>Sản phẩm trong đơn</strong></div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td>{{ $item->product?->name ?? 'Sản phẩm đã xoá' }}</td>
                            <td class="text-center num">{{ $item->quantity }}</td>
                            <td class="text-end num">{{ number_format($item->price, 0, ',', '.') }} đ</td>
                            <td class="text-end num fw-semibold">{{ number_format($item->subtotal, 0, ',', '.') }} đ</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end text-muted">Tiền hàng</td>
                            <td class="text-end num">{{ number_format($order->subtotal, 0, ',', '.') }} đ</td>
                        </tr>
                        @if ($order->discount_amount > 0)
                            <tr>
                                <td colspan="3" class="text-end text-muted">Giảm giá ({{ $order->coupon_code }})</td>
                                <td class="text-end num text-success">-{{ number_format($order->discount_amount, 0, ',', '.') }} đ</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="text-end text-muted">Phí vận chuyển</td>
                            <td class="text-end num">{{ number_format($order->ghn_total_fee, 0, ',', '.') }} đ</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-semibold">Tổng thanh toán</td>
                            <td class="text-end num fw-semibold">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-panel">
            <div class="panel-head"><strong>Lịch sử giao dịch thanh toán</strong></div>
            @if ($order->paymentTransactions->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Cổng</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Số tiền</th>
                                <th>Thời điểm</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($order->paymentTransactions as $tx)
                            <tr>
                                <td>{{ $tx->gateway_label }}</td>
                                <td>
                                    <span class="tag {{ $tx->status === 'paid' ? 'tag-ok' : ($tx->status === 'failed' ? 'tag-danger' : '') }}">
                                        {{ $tx->status_label }}
                                    </span>
                                </td>
                                <td class="text-end num">{{ number_format($tx->amount, 0, ',', '.') }} đ</td>
                                <td class="text-muted small">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty"><h4>Chưa có giao dịch</h4></div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel mb-4">
            <div class="panel-head"><strong>Khách hàng</strong></div>
            <div class="panel-body">
                <p class="mb-1"><strong>{{ $order->name }}</strong></p>
                <p class="mb-1 text-muted small">{{ $order->phone }}</p>
                <p class="mb-1 text-muted small">{{ $order->address }}</p>
                @if ($order->user)
                    <p class="mb-0 text-muted small">Tài khoản: {{ $order->user->email }}</p>
                @endif
            </div>
        </div>

        <div class="card-panel mb-4">
            <div class="panel-head"><strong>Trạng thái</strong></div>
            <div class="panel-body">
                <p class="mb-2">
                    <span class="stat-label d-block">Thanh toán</span>
                    <span class="tag {{ match ($order->status_badge) { 'success' => 'tag-ok', 'danger' => 'tag-danger', default => 'tag-warn' } }}">
                        {{ $order->status_label }}
                    </span>
                    <span class="text-muted small">· {{ $order->payment_label }}</span>
                </p>
                <p class="mb-0">
                    <span class="stat-label d-block">Vận chuyển</span>
                    <span class="tag {{ $order->shipping_status === 'delivered' ? 'tag-ok' : ($order->shipping_status === 'cancel' ? 'tag-danger' : '') }}">
                        {{ $shippingLabels[$order->shipping_status] ?? $order->shipping_status }}
                    </span>
                    @if ($order->ghn_order_code)
                        <span class="text-muted small">· Mã vận đơn {{ $order->ghn_order_code }}</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="card-panel">
            <div class="panel-head"><strong>Xử lý đơn hàng</strong></div>
            <div class="panel-body d-flex flex-column gap-3">
                @if (!$isTerminal)
                    @if (count($nextOptions))
                        <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="form-label">Chuyển trạng thái vận chuyển</label>
                            <select name="shipping_status" class="form-select mb-2" required>
                                <option value="">— Chọn trạng thái mới —</option>
                                @foreach ($nextOptions as $value)
                                    <option value="{{ $value }}">{{ $shippingLabels[$value] }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-ink w-100">Cập nhật trạng thái</button>
                        </form>
                    @endif

                    @if ($order->isCancellable())
                        <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST"
                              onsubmit="return confirm('Hủy đơn hàng #{{ $order->id }}? Tồn kho sẽ được hoàn lại.')">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-line text-danger w-100">Hủy đơn hàng</button>
                        </form>
                    @else
                        <p class="text-muted small mb-0">Đơn đang được vận chuyển — không thể hủy.</p>
                    @endif
                @else
                    <p class="text-muted small mb-0">Đơn hàng đã ở trạng thái cuối cùng, không thể thay đổi thêm.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
