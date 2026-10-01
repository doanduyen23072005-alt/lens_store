{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.shop')
@section('title', 'Đơn hàng của tôi')

@section('content')
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1">Đơn hàng của tôi</h1>
        <p class="text-muted mb-0">{{ $orders->total() }} đơn hàng đã đặt.</p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-line">← Tiếp tục mua sắm</a>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if (session('warning')) <div class="alert alert-warning">{{ session('warning') }}</div> @endif
@if (session('info'))    <div class="alert alert-info">{{ session('info') }}</div>       @endif
@if (session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>    @endif

@if ($orders->count() > 0)
    @foreach ($orders as $order)
        <div class="ol-card">
            <div class="ol-head">
                <div>
                    <a href="{{ route('order.show', $order) }}" class="ol-id">Đơn hàng #{{ $order->id }}</a>
                    <span class="text-muted ms-2" style="font-size:13px">
                        {{ $order->created_at->format('H:i d/m/Y') }}
                    </span>
                </div>

                <div class="ol-badges">
                    <span class="badge-status {{ $order->status_badge === 'success' ? 'badge-ship' : ($order->status_badge === 'danger' ? 'badge-danger' : '') }}">
                        {{ $order->status_label }}
                    </span>
                    <span class="badge-status badge-ship">{{ $order->shipping_label }}</span>
                </div>
            </div>

            <div class="ol-body">
                <div class="ol-thumbs">
                    @foreach ($order->items->take(4) as $item)
                        @if ($item->product && $item->product->image)
                            <img src="{{ asset('storage/' . $item->product->image) }}"
                                 alt="{{ $item->product->name }}" class="ol-thumb">
                        @else
                            <div class="ol-thumb ol-thumb-empty">—</div>
                        @endif
                    @endforeach

                    @if ($order->items->count() > 4)
                        <div class="ol-thumb ol-thumb-more">+{{ $order->items->count() - 4 }}</div>
                    @endif
                </div>

                <div class="ol-meta">
                    <div class="text-muted" style="font-size:13px">
                        {{ $order->total_quantity }} sản phẩm
                        · {{ $order->payment_method === 'cod' ? 'COD' : 'MoMo' }}
                        @if ($order->ghn_order_code)
                            · Vận đơn <b>{{ $order->ghn_order_code }}</b>
                        @endif
                    </div>
                    <div class="ol-total">{{ number_format($order->total_price, 0, ',', '.') }} ₫</div>
                </div>

                <div class="ol-actions">
                    @if ($order->canRetryPayment())
                        <a href="{{ route('momo.pay_again', $order) }}" class="btn btn-ink">Thanh toán lại</a>
                    @endif
                    @if ($order->shipping_status === 'delivered' && $order->items->first()?->product)
                        <a href="{{ route('shop.show', $order->items->first()->product_id) }}#reviews" class="btn btn-ink">
                            Đánh giá
                        </a>
                    @endif
                    <a href="{{ route('order.show', $order) }}" class="btn btn-line">Xem chi tiết</a>
                </div>
            </div>
        </div>
    @endforeach

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@else
    <div class="empty-orders">
        <div class="empty-ring"></div>
        <h2>Chưa có đơn hàng nào</h2>
        <p class="text-muted">Hãy chọn một ống kính ưng ý để bắt đầu.</p>
        <a href="{{ route('home') }}" class="btn btn-ink px-4">Xem ống kính</a>
    </div>
@endif

@push('styles')
<style>
    .ol-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .ol-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--line);
        background: #FAFAFB;
    }

    .ol-id {
        font-weight: 600;
        color: var(--ink);
        font-size: 15px;
    }
    .ol-id:hover { color: #8A6410; }

    .ol-badges { display: flex; gap: 8px; flex-wrap: wrap; }

    .badge-status {
        padding: 4px 12px;
        border-radius: 999px;
        background: #FEF6E4;
        color: #8A6410;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .badge-ship { background: #E9F6F0; color: var(--ok); }
    .badge-danger { background: #FDEDEE; color: #B42318; }

    .ol-body {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 16px 20px;
        flex-wrap: wrap;
    }

    .ol-thumbs { display: flex; gap: 8px; flex: none; }

    .ol-thumb {
        width: 52px; height: 52px;
        object-fit: cover;
        border-radius: 9px;
        border: 1px solid var(--line);
        background: #F6F7F8;
    }

    .ol-thumb-empty,
    .ol-thumb-more {
        display: grid;
        place-items: center;
        color: #A6ACB5;
        font-size: 12px;
        font-weight: 600;
    }

    .ol-meta { flex: 1; min-width: 180px; }

    .ol-total {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 700;
        font-size: 18px;
        font-variant-numeric: tabular-nums;
        margin-top: 4px;
    }

    .ol-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    .empty-orders { text-align: center; padding: 60px 20px; }

    .empty-ring {
        width: 110px; height: 110px;
        margin: 0 auto 22px;
        border-radius: 50%;
        background: radial-gradient(circle at 34% 30%, rgba(224,168,46,.28) 0 18%, rgba(124,107,214,.22) 19% 38%, #E9EBEE 39% 100%);
        box-shadow: inset 0 0 0 8px #E4E7EA;
    }

    @media (max-width: 640px) {
        .ol-actions { width: 100%; }
        .ol-actions .btn { flex: 1; }
    }
</style>
@endpush
@endsection