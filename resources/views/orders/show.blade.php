@extends('layouts.shop')
@section('title', 'Đơn hàng #' . $order->id)

@section('content')
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1">Đơn hàng #{{ $order->id }}</h1>
        <p class="text-muted mb-0">Đặt lúc {{ $order->created_at->format('H:i d/m/Y') }}</p>
    </div>
    <a href="{{ route('order.index') }}" class="btn btn-line">Tất cả đơn hàng</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="bg-white border rounded-4 p-4">
            <h2 style="font-size:17px" class="mb-3">Sản phẩm</h2>

            @foreach ($order->items as $item)
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    @if ($item->product && $item->product->image)
                        <img src="{{ asset('storage/' . $item->product->image) }}"
                             style="width:60px;height:60px;object-fit:cover;border-radius:10px" alt="">
                    @endif

                    <div class="flex-fill">
                        <div class="fw-semibold" style="font-size:14px">{{ $item->product->name ?? 'Sản phẩm đã xoá' }}</div>
                        <div class="text-muted" style="font-size:13px">
                            {{ number_format($item->price, 0, ',', '.') }} ₫ × {{ $item->quantity }}
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="fw-semibold">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} ₫</div>
                        @if ($order->shipping_status === 'delivered' && $item->product)
                            <a href="{{ route('shop.show', $item->product_id) }}#reviews" class="btn btn-line btn-sm mt-1">
                                Đánh giá
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="bg-white border rounded-4 p-4 mt-4">
            <h2 style="font-size:17px" class="mb-3">Thông tin nhận hàng</h2>
            <p class="mb-1"><b>{{ $order->name }}</b> — {{ $order->phone }}</p>
            <p class="text-muted mb-0">{{ $order->address }}</p>
        </div>

        @if ($order->paymentTransactions->count())
            <div class="bg-white border rounded-4 p-4 mt-4">
                <h2 style="font-size:17px" class="mb-3">Lịch sử giao dịch</h2>

                @foreach ($order->paymentTransactions as $tx)
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>
                            {{ $tx->gateway_label }}
                            <small class="text-muted">{{ $tx->created_at->format('H:i d/m/Y') }}</small>
                        </span>
                        <span class="badge bg-{{ $tx->status_badge }} {{ $tx->status_badge === 'warning' ? 'text-dark' : '' }}">
                            {{ $tx->status_label }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="col-lg-5">
        <div class="bg-white border rounded-4 p-4">
            <h2 style="font-size:17px" class="mb-3">Thanh toán</h2>

            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">Tiền hàng</span>
                <b>{{ number_format($order->subtotal, 0, ',', '.') }} ₫</b>
            </div>
            @if ($order->discount_amount > 0)
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Giảm giá ({{ $order->coupon_code }})</span>
                    <b class="text-success">-{{ number_format($order->discount_amount, 0, ',', '.') }} ₫</b>
                </div>
            @endif
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">Phí vận chuyển (GHN)</span>
                <b>{{ number_format($order->ghn_total_fee, 0, ',', '.') }} ₫</b>
            </div>

            <hr>

            <div class="d-flex justify-content-between">
                <b>Tổng thanh toán</b>
                <b style="font-size:20px">{{ number_format($order->total_price, 0, ',', '.') }} ₫</b>
            </div>

            <div class="d-flex justify-content-between py-1 mt-3">
                <span class="text-muted">Hình thức</span>
                <b>{{ $order->payment_label }}</b>
            </div>
            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">Trạng thái</span>
                <span class="badge bg-{{ $order->status_badge }} {{ $order->status_badge === 'warning' ? 'text-dark' : '' }}">
                    {{ $order->status_label }}
                </span>
            </div>

            @if ($order->canRetryPayment())
                <a href="{{ route('momo.pay_again', $order) }}" class="btn btn-ink w-100 mt-3">
                    Thanh toán lại với MoMo
                </a>
            @endif
        </div>

        <div class="bg-white border rounded-4 p-4 mt-4">
            <h2 style="font-size:17px" class="mb-3">Vận chuyển</h2>

            <div class="d-flex justify-content-between py-1">
                <span class="text-muted">Trạng thái</span>
                <span class="badge bg-info text-dark">{{ $order->shipping_label }}</span>
            </div>

            @if ($order->ghn_order_code)
                <div class="d-flex justify-content-between py-1">
                    <span class="text-muted">Mã vận đơn</span>
                    <b>{{ $order->ghn_order_code }}</b>
                </div>
            @else
                <p class="text-muted mb-0 mt-2" style="font-size:13px">
                    Vận đơn chưa được tạo. Shop sẽ xử lý sớm.
                </p>
            @endif
        </div>

        @if ($order->isCancellable())
            <form action="{{ route('order.cancel', $order) }}" method="POST" class="mt-4"
                  onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?')">
                @csrf
                <button type="submit" class="btn btn-line w-100">Hủy đơn hàng</button>
            </form>
        @elseif ($order->isReturnable())
            <form action="{{ route('order.return', $order) }}" method="POST" class="mt-4"
                  onsubmit="return confirm('Gửi yêu cầu hoàn hàng cho đơn này?')">
                @csrf
                <button type="submit" class="btn btn-line w-100">Yêu cầu hoàn hàng</button>
            </form>
        @endif

        <a href="{{ route('home') }}" class="btn btn-ink w-100 mt-3">Tiếp tục mua sắm</a>
    </div>
</div>
@endsection