{{-- resources/views/cart/index.blade.php --}}
@extends('layouts.shop')
@section('title', 'Giỏ hàng')

@section('content')
<div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1">Giỏ hàng</h1>
        <p class="text-muted mb-0">{{ count($cart) }} sản phẩm đang có trong giỏ.</p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-line">← Tiếp tục mua sắm</a>
</div>

@if (count($cart) > 0)
    @php $grandTotal = 0; @endphp

    <form id="checkoutForm" action="{{ route('cart.checkout') }}" method="POST">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="cart-panel">
                    <div class="cart-head">
                        <label class="check-wrap mb-0">
                            <input type="checkbox" id="checkAll" checked>
                            <span>Chọn tất cả</span>
                        </label>

                        <button type="button" class="btn-link-danger ms-auto" onclick="document.getElementById('clearForm').submit()">
                            Xoá hết giỏ hàng
                        </button>
                    </div>

                    @foreach ($cart as $id => $item)
                        @php $lineTotal = $item['price'] * $item['quantity']; $grandTotal += $lineTotal; @endphp

                        <div class="cart-row">
                            <label class="check-wrap">
                                <input type="checkbox" name="selected[]" value="{{ $id }}"
                                       class="item-check" data-line="{{ $lineTotal }}" checked>
                            </label>

                            @if ($item['image'])
                                <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="cart-thumb">
                            @else
                                <div class="cart-thumb cart-thumb-empty">Chưa có ảnh</div>
                            @endif

                            <div class="cart-info">
                                <a href="{{ route('shop.show', $id) }}" class="cart-name">{{ $item['name'] }}</a>
                                <div class="cart-meta">
                                    <span class="tag">{{ $item['category'] ?? 'Chưa phân loại' }}</span>
                                    <span class="text-muted">{{ $item['code'] ?? '' }}</span>
                                </div>
                                <div class="cart-unit">{{ number_format($item['price'], 0, ',', '.') }} ₫ / chiếc</div>
                            </div>

                            <div class="cart-qty">
                                <button type="button" class="qty-btn" onclick="stepQty('qty{{ $id }}', -1)">−</button>
                                <input type="number" id="qty{{ $id }}" value="{{ $item['quantity'] }}"
                                       min="1" class="qty-input" readonly>
                                <button type="button" class="qty-btn" onclick="stepQty('qty{{ $id }}', 1)">+</button>
                            </div>

                            <div class="cart-line">{{ number_format($lineTotal, 0, ',', '.') }} ₫</div>

                            <button type="button" class="btn-remove"
                                    onclick="document.getElementById('remove{{ $id }}').submit()"
                                    title="Xoá khỏi giỏ">×</button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary">
                    <h2 class="summary-title">Tóm tắt đơn hàng</h2>

                    <div class="summary-row">
                        <span>Đã chọn</span>
                        <strong id="sumCount">0 sản phẩm</strong>
                    </div>
                    <div class="summary-row">
                        <span>Tạm tính</span>
                        <strong id="sumSubtotal">0 ₫</strong>
                    </div>
                    <div class="summary-row" id="discountRow" style="display:{{ $coupon ? 'flex' : 'none' }}">
                        <span id="discountCouponLabel">Giảm giá ({{ $coupon->code ?? '' }})</span>
                        <strong class="text-success" id="sumDiscount">-{{ number_format($discount, 0, ',', '.') }} ₫</strong>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển</span>
                        <strong class="text-muted" style="font-weight:500">Tính ở bước sau</strong>
                    </div>

                    <div class="summary-total">
                        <span>Tạm tính</span>
                        <span id="sumTotal">0 ₫</span>
                    </div>

                    <button type="submit" class="btn btn-ink w-100 mt-3" id="checkoutBtn">
                        Tiến hành thanh toán
                    </button>

                    <p class="summary-note">
                        Phí vận chuyển do GHN tính theo địa chỉ nhận hàng ở bước tiếp theo.
                        Mã giảm giá được nhập ở bước thanh toán.
                    </p>
                </div>
            </div>
        </div>
    </form>

    <form id="clearForm" action="{{ route('cart.clear') }}" method="POST" class="d-none">
        @csrf @method('DELETE')
    </form>

    @foreach ($cart as $id => $item)
        <form id="remove{{ $id }}" action="{{ route('cart.remove', $id) }}" method="POST" class="d-none">
            @csrf @method('DELETE')
        </form>
        <form id="update{{ $id }}" action="{{ route('cart.update', $id) }}" method="POST" class="d-none">
            @csrf @method('PATCH')
            <input type="hidden" name="quantity" id="hiddenQty{{ $id }}" value="{{ $item['quantity'] }}">
        </form>
    @endforeach
@else
    <div class="empty-cart">
        <div class="empty-ring"></div>
        <h2>Giỏ hàng đang trống</h2>
        <p class="text-muted">Chọn một ống kính ưng ý để bắt đầu.</p>
        <a href="{{ route('home') }}" class="btn btn-ink px-4">Xem ống kính</a>
    </div>
@endif

@push('styles')
<style>
    .cart-panel {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        overflow: hidden;
    }

    .cart-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--line);
        background: #FAFAFB;
        font-size: 14px;
    }

    .check-wrap { display: flex; align-items: center; gap: 8px; cursor: pointer; }

    .check-wrap input[type="checkbox"] {
        width: 18px; height: 18px;
        accent-color: var(--barrel);
        cursor: pointer;
    }

    .btn-link-danger {
        background: none;
        border: 0;
        color: var(--danger);
        font-size: 13px;
        cursor: pointer;
        padding: 0;
    }
    .btn-link-danger:hover { text-decoration: underline; }

    .cart-row {
        display: grid;
        grid-template-columns: auto 84px 1fr auto auto auto;
        align-items: center;
        gap: 14px;
        padding: 16px 18px;
        border-bottom: 1px solid #EFF1F3;
    }
    .cart-row:last-child { border-bottom: 0; }

    .cart-thumb {
        width: 84px; height: 84px;
        object-fit: cover;
        border-radius: 11px;
        border: 1px solid var(--line);
        background: #F6F7F8;
    }

    .cart-thumb-empty {
        display: grid;
        place-items: center;
        color: #A6ACB5;
        font-size: 11px;
        text-align: center;
        border-style: dashed;
    }

    .cart-name {
        font-weight: 600;
        color: var(--ink);
        font-size: 15px;
        display: block;
        text-decoration: none;
    }
    .cart-name:hover { color: #8A6410; }

    .cart-meta { display: flex; gap: 8px; align-items: center; margin: 5px 0; font-size: 12px; }
    .cart-unit { font-size: 13px; color: var(--muted); }

    .cart-qty {
        display: flex;
        align-items: center;
        border: 1px solid var(--line);
        border-radius: 9px;
        overflow: hidden;
    }

    .qty-btn {
        width: 32px; height: 34px;
        border: 0;
        background: #FAFAFB;
        color: #3F454D;
        font-size: 17px;
        line-height: 1;
        cursor: pointer;
    }
    .qty-btn:hover { background: #F0F1F3; }

    .qty-input {
        width: 44px;
        height: 34px;
        border: 0;
        border-left: 1px solid var(--line);
        border-right: 1px solid var(--line);
        text-align: center;
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        background: #fff;
    }
    .qty-input::-webkit-outer-spin-button,
    .qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

    .cart-line {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        min-width: 110px;
        text-align: right;
    }

    .btn-remove {
        width: 30px; height: 30px;
        border: 1px solid var(--line);
        background: #fff;
        border-radius: 8px;
        color: #9AA1AA;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
    }
    .btn-remove:hover { border-color: #EFC7C1; color: var(--danger); }

    .summary {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 20px;
        position: sticky;
        top: 20px;
    }

    .summary-title { font-size: 17px; margin: 0 0 16px; }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 9px 0;
        font-size: 14px;
        color: var(--muted);
    }
    .summary-row strong { color: var(--ink); font-variant-numeric: tabular-nums; }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        border-top: 1px solid var(--line);
        margin-top: 10px;
        padding-top: 14px;
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 700;
        font-size: 21px;
        font-variant-numeric: tabular-nums;
    }

    .summary-note { font-size: 12px; color: var(--muted); margin: 12px 0 0; }

    .empty-cart { text-align: center; padding: 60px 20px; }

    .empty-ring {
        width: 110px; height: 110px;
        margin: 0 auto 22px;
        border-radius: 50%;
        background: radial-gradient(circle at 34% 30%, rgba(224,168,46,.28) 0 18%, rgba(124,107,214,.22) 19% 38%, #E9EBEE 39% 100%);
        box-shadow: inset 0 0 0 8px #E4E7EA;
    }

    @media (max-width: 780px) {
        .cart-row {
            grid-template-columns: auto 64px 1fr auto;
            grid-template-areas:
                "check img info remove"
                ". . qty line";
            row-gap: 10px;
        }
        .cart-row > .check-wrap { grid-area: check; }
        .cart-thumb { grid-area: img; width: 64px; height: 64px; }
        .cart-info { grid-area: info; }
        .cart-qty { grid-area: qty; }
        .cart-line { grid-area: line; }
        .btn-remove { grid-area: remove; }
    }
</style>
@endpush

@push('scripts')
<script>
    function vnd(n) {
        return new Intl.NumberFormat('vi-VN').format(n) + ' ₫';
    }

    let appliedDiscount = {{ (float) $discount }};

    function recalc() {
        const checks = document.querySelectorAll('.item-check');
        let total = 0, count = 0;

        checks.forEach(function (c) {
            if (c.checked) {
                total += parseFloat(c.dataset.line);
                count++;
            }
        });

        const afterDiscount = Math.max(0, total - appliedDiscount);

        document.getElementById('sumCount').textContent    = count + ' sản phẩm';
        document.getElementById('sumSubtotal').textContent = vnd(total);
        document.getElementById('sumTotal').textContent    = vnd(afterDiscount);
        document.getElementById('checkoutBtn').disabled    = (count === 0);

        const all = document.getElementById('checkAll');
        all.checked = (count === checks.length && count > 0);
    }

    function stepQty(inputId, delta) {
        const input = document.getElementById(inputId);
        const id    = inputId.replace('qty', '');
        const value = Math.max(1, parseInt(input.value, 10) + delta);

        document.getElementById('hiddenQty' + id).value = value;
        document.getElementById('update' + id).submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.item-check').forEach(function (c) {
            c.addEventListener('change', recalc);
        });

        document.getElementById('checkAll').addEventListener('change', function (e) {
            document.querySelectorAll('.item-check').forEach(function (c) {
                c.checked = e.target.checked;
            });
            recalc();
        });

        recalc();
    });
</script>
@endpush
@endsection