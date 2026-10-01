{{-- resources/views/admin/orders/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Đơn hàng')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Đơn hàng</h1>
        <p class="page-sub">Theo dõi trạng thái, chuyển tiến độ giao hàng hoặc hủy đơn.</p>
    </div>
</div>

<div class="card-panel mb-3">
    <div class="panel-body">
        <ul class="nav nav-pills flex-wrap gap-1">
            @foreach ($tabs as $key => $tab)
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === $key ? 'active' : '' }}"
                       href="{{ route('admin.orders.index', array_merge(request()->except(['tab', 'page']), ['tab' => $key])) }}">
                        {{ $tab['label'] }}
                        <span class="badge rounded-pill text-bg-{{ $tab['badge'] }} ms-1">{{ $tab['count'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<div class="card-panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="d-flex gap-2 flex-wrap w-100 align-items-center">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"
                   style="max-width:240px" placeholder="Mã đơn, khách hàng, SĐT, mã vận đơn...">

            <select name="status" class="form-select" style="max-width:190px">
                <option value="">Tất cả thanh toán</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="payment_method" class="form-select" style="max-width:160px">
                <option value="">Tất cả hình thức</option>
                <option value="cod" @selected(($filters['payment_method'] ?? null) === 'cod')>COD</option>
                <option value="momo" @selected(($filters['payment_method'] ?? null) === 'momo')>MoMo</option>
            </select>

            <select name="shipping_status" class="form-select" style="max-width:190px">
                <option value="">Tất cả vận chuyển</option>
                @foreach ($shippingLabels as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['shipping_status'] ?? null) === $value)>{{ $label }}</option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control" style="max-width:150px">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control" style="max-width:150px">

            <select name="sort" class="form-select" style="max-width:170px">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Mới nhất</option>
                <option value="oldest" @selected(($filters['sort'] ?? null) === 'oldest')>Cũ nhất</option>
                <option value="amount_desc" @selected(($filters['sort'] ?? null) === 'amount_desc')>Tổng tiền giảm dần</option>
                <option value="amount_asc" @selected(($filters['sort'] ?? null) === 'amount_asc')>Tổng tiền tăng dần</option>
            </select>

            <button class="btn btn-line">Lọc</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-line">Xoá bộ lọc</a>
        </form>
    </div>

    @if ($orders->count())
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Ngày tạo</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-end">COD cần thu</th>
                        <th>Mã vận đơn</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái giao hàng</th>
                        <th class="text-end" style="width:170px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($orders as $order)
                    @php
                        $codDue = $order->payment_method === 'cod'
                            && !in_array($order->status, ['cancelled', 'cod_paid', 'refund_pending', 'refunded'], true)
                            && $order->shipping_status !== 'delivered'
                            ? $order->total_price : 0;
                        $statusTagClass = match ($order->status_badge) {
                            'success' => 'tag-ok',
                            'danger'  => 'tag-danger',
                            default   => 'tag-warn',
                        };
                    @endphp
                    <tr>
                        <td><span class="code-chip">DH{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                        <td class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $order->name }}</div>
                            <div class="text-muted small">{{ $order->phone }}</div>
                        </td>
                        <td class="text-muted small">
                            {{ $order->items->first()?->product?->name ?? 'Sản phẩm đã xoá' }}
                            @if ($order->items->count() > 1)
                                <span class="text-muted">+{{ $order->items->count() - 1 }}</span>
                            @endif
                        </td>
                        <td class="text-end num fw-semibold">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                        <td class="text-end num">{{ $codDue > 0 ? number_format($codDue, 0, ',', '.').' đ' : '—' }}</td>
                        <td class="text-muted small">{{ $order->ghn_order_code ?? 'Chưa có' }}</td>
                        <td>
                            <span class="tag {{ $statusTagClass }}">{{ $order->status_label }}</span>
                        </td>
                        <td>
                            <span class="tag {{ $order->shipping_status === 'delivered' ? 'tag-ok' : ($order->shipping_status === 'cancel' ? 'tag-danger' : (in_array($order->shipping_status, ['return', 'returned', 'delivery_fail']) ? 'tag-warn' : '')) }}">
                                {{ $shippingLabels[$order->shipping_status] ?? $order->shipping_status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-line btn-sm">Xem</a>
                            @if ($order->isCancellable())
                                <form action="{{ route('admin.orders.cancel', $order->id) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Hủy đơn hàng #{{ $order->id }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-line btn-sm text-danger">Hủy</button>
                                </form>
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
            <h4>Không có đơn hàng nào khớp bộ lọc</h4>
            <p>Thử đổi tab hoặc xoá bớt điều kiện lọc.</p>
        </div>
    @endif
</div>
@endsection
