{{-- resources/views/cart/checkout.blade.php --}}
@extends('layouts.shop')
@section('title', 'Thanh toán')

@section('content')
<div class="checkout-head">
    <div>
        <h1 class="mb-1">Thanh toán</h1>
        <p class="text-muted mb-0">Điền thông tin nhận hàng và chọn hình thức thanh toán.</p>
    </div>
    <a href="{{ route('cart.index') }}" class="btn btn-line">← Quay lại giỏ hàng</a>
</div>

<div class="steps">
    <span class="step done">Giỏ hàng</span>
    <span class="step-line"></span>
    <span class="step current">Thông tin & thanh toán</span>
    <span class="step-line"></span>
    <span class="step">Hoàn tất</span>
</div>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('order.store') }}" method="POST" id="checkout_form">
@csrf
<div class="row g-4">
    {{-- Cột trái: biểu mẫu --}}
    <div class="col-lg-7">
        <div class="co-card">
            <h2 class="co-title">Thông tin nhận hàng</h2>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="fullname">Họ và tên</label>
                    <input type="text" id="fullname" name="fullname" class="form-control"
                           value="{{ old('fullname', Auth::user()->name) }}" placeholder="Nguyễn Văn A" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="phone">Số điện thoại</label>
                    <input type="tel" id="phone" name="phone" class="form-control"
                           value="{{ old('phone') }}" placeholder="0912345678" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="province_select">Tỉnh/Thành</label>
                    <select id="province_select" name="to_province_id" class="form-select" required>
                        <option value="">-- Đang tải... --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="district_select">Quận/Huyện</label>
                    <select id="district_select" name="to_district_id" class="form-select" disabled required>
                        <option value="">-- Chọn Quận/Huyện --</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="ward_select">Phường/Xã</label>
                    <select id="ward_select" name="to_ward_code" class="form-select" disabled required>
                        <option value="">-- Chọn Phường/Xã --</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label" for="address">Địa chỉ cụ thể</label>
                    <input type="text" id="address" name="address" class="form-control"
                           value="{{ old('address') }}" placeholder="Số nhà, tên đường" required>
                    <small class="text-muted" style="font-size:12px">
                        Chỉ nhập số nhà và tên đường.
                    </small>
                </div>

                <div class="col-12">
                    <label class="form-label" for="note">Ghi chú (không bắt buộc)</label>
                    <textarea id="note" name="note" rows="3" class="form-control"
                              placeholder="Thời gian nhận hàng, hướng dẫn giao...">{{ old('note') }}</textarea>
                </div>
            </div>
        </div>

        <div class="co-card mt-4">
            <h2 class="co-title">Hình thức thanh toán</h2>

            <label class="pay-option" for="payCod">
                <input type="radio" id="payCod" name="payment_method" value="cod" checked>
                <span class="pay-mark"></span>
                <span class="pay-icon pay-icon-cod">₫</span>
                <span class="pay-text">
                    <strong>Thanh toán trực tiếp</strong>
                    <small>Trả tiền mặt cho nhân viên giao hàng khi nhận ống kính.</small>
                </span>
            </label>

            <label class="pay-option" for="payMomo">
                <input type="radio" id="payMomo" name="payment_method" value="momo">
                <span class="pay-mark"></span>
                <span class="pay-icon pay-icon-momo">M</span>
                <span class="pay-text">
                    <strong>Ví MoMo</strong>
                    <small>Quét mã QR trên ứng dụng MoMo để thanh toán ngay.</small>
                </span>
            </label>
        </div>
    </div>

    {{-- Cột phải: đơn hàng --}}
    <div class="col-lg-5">
        <div class="co-card summary-card">
            <h2 class="co-title">Đơn hàng của bạn</h2>

            @foreach ($items as $id => $item)
                <div class="co-item">
                    @if ($item['image'])
                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="co-thumb">
                    @else
                        <div class="co-thumb co-thumb-empty">—</div>
                    @endif

                    <div class="co-item-info">
                        <div class="co-item-name">{{ $item['name'] }}</div>
                        <div class="text-muted" style="font-size:13px">
                            {{ number_format($item['price'], 0, ',', '.') }} ₫ × {{ $item['quantity'] }}
                        </div>
                    </div>

                    <div class="co-item-total">
                        {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} ₫
                    </div>
                </div>
            @endforeach

            <div class="co-row">
                <span>Tạm tính</span>
                <strong>{{ number_format($total, 0, ',', '.') }} ₫</strong>
            </div>
            <div class="co-row" id="discountRow" style="display:{{ $coupon ? 'flex' : 'none' }}">
                <span>Giảm giá <small class="text-muted" id="discountCouponLabel">({{ $coupon->code ?? '' }})</small></span>
                <strong class="text-success" id="discountAmountText">-{{ number_format($discount, 0, ',', '.') }} ₫</strong>
            </div>
            <div class="co-row">
                <span>Phí vận chuyển <small class="text-muted">(GHN)</small></span>
                <strong id="shipping_fee_text">Chọn địa chỉ để tính</strong>
            </div>

            <div class="co-total">
                <span>Tổng thanh toán</span>
                <span id="final_total_text">{{ number_format(max(0, $total - $discount), 0, ',', '.') }} ₫</span>
            </div>

            {{-- Server vẫn tính lại các giá trị này, đây chỉ để hiển thị/đối chiếu --}}
            <input type="hidden" id="subtotal_input"     name="subtotal"     value="{{ (int) $total }}">
            <input type="hidden" id="discount_input"     name="discount"     value="{{ (int) $discount }}">
            <input type="hidden" id="shipping_fee_input" name="shipping_fee" value="0">
            <input type="hidden" id="total_price_input"  name="total_price"  value="{{ (int) max(0, $total - $discount) }}">

            <button type="submit" class="btn btn-ink w-100 mt-3" id="submit_btn" disabled>
                Đặt hàng
            </button>

            <p class="co-note" id="co_note">
                Vui lòng chọn Tỉnh/Quận/Phường để hệ thống tính phí vận chuyển.
            </p>
        </div>
    </div>
</div>
</form>

<div class="row g-4 mt-1">
    <div class="col-lg-5 offset-lg-7">
        <div class="co-card">
            <h2 class="co-title" style="font-size:15px">Mã giảm giá</h2>

            <div id="couponApplied" class="justify-content-between align-items-center" style="display:{{ $coupon ? 'flex' : 'none' }}">
                <div>
                    <span class="coupon-chip" id="couponChipCode">{{ $coupon->code ?? '' }}</span>
                    <div class="text-muted mt-1" style="font-size:12px" id="couponChipInfo">
                        Đã áp dụng · giảm {{ number_format($discount, 0, ',', '.') }} ₫
                    </div>
                </div>
                <button type="button" class="btn btn-line btn-sm" id="couponRemoveBtn">Bỏ mã</button>
            </div>

            <form id="couponApplyForm" class="gap-2" style="display:{{ $coupon ? 'none' : 'flex' }}">
                <input type="text" name="code" id="couponCodeInput" class="form-control text-uppercase" placeholder="Nhập mã giảm giá" required>
                <button type="submit" class="btn btn-line" id="couponApplyBtn">Áp dụng</button>
            </form>
            <div class="text-danger mt-2" id="couponError" style="display:none;font-size:13px"></div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .checkout-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 18px;
    }

    /* Thanh tiến trình */
    .steps {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 26px;
        font-size: 13px;
        flex-wrap: wrap;
    }

    .step {
        padding: 5px 13px;
        border-radius: 999px;
        border: 1px solid var(--line);
        background: #fff;
        color: var(--muted);
    }
    .step.done { border-color: #BFE3D4; background: #E9F6F0; color: var(--ok); }
    .step.current { border-color: var(--barrel); background: var(--barrel); color: #fff; }

    .step-line { flex: 1; height: 1px; background: var(--line); min-width: 20px; }

    .co-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        padding: 22px;
    }

    .co-title { font-size: 17px; margin: 0 0 18px; }

    .form-label { font-size: 13px; font-weight: 600; margin-bottom: 5px; }
    .form-control, .form-select { padding: 10px 12px; }

    /* Hình thức thanh toán */
    .pay-option {
        display: flex;
        align-items: center;
        gap: 14px;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 14px 16px;
        cursor: pointer;
        margin-bottom: 12px;
        transition: border-color .15s ease, background .15s ease;
    }
    .pay-option:last-child { margin-bottom: 0; }
    .pay-option:hover { border-color: #B9BFC7; }

    .pay-option input[type="radio"] { display: none; }

    .pay-mark {
        width: 20px; height: 20px;
        border-radius: 50%;
        border: 2px solid #C4C9CF;
        flex: none;
        position: relative;
    }

    .pay-option input:checked ~ .pay-mark {
        border-color: var(--coating);
    }
    .pay-option input:checked ~ .pay-mark::after {
        content: "";
        position: absolute;
        inset: 3px;
        border-radius: 50%;
        background: var(--coating);
    }

    .pay-option:has(input:checked) {
        border-color: var(--coating);
        background: #FEFBF3;
    }

    .pay-icon {
        width: 42px; height: 42px;
        border-radius: 11px;
        display: grid;
        place-items: center;
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 700;
        font-size: 19px;
        flex: none;
    }

    .pay-icon-cod  { background: #E9F6F0; color: var(--ok); }
    .pay-icon-momo { background: #F6E9F5; color: #A50064; }

    .pay-text { display: flex; flex-direction: column; }
    .pay-text strong { font-size: 15px; }
    .pay-text small { color: var(--muted); font-size: 13px; }

    /* Tóm tắt đơn */
    .summary-card { position: sticky; top: 20px; }

    .co-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #EFF1F3;
    }

    .co-thumb {
        width: 54px; height: 54px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid var(--line);
        background: #F6F7F8;
        flex: none;
    }

    .co-thumb-empty { display: grid; place-items: center; color: #A6ACB5; border-style: dashed; }

    .co-item-info { flex: 1; min-width: 0; }
    .co-item-name { font-weight: 600; font-size: 14px; }

    .co-item-total {
        font-variant-numeric: tabular-nums;
        font-weight: 600;
        white-space: nowrap;
    }

    .co-row {
        display: flex;
        justify-content: space-between;
        padding: 9px 0;
        font-size: 14px;
        color: var(--muted);
    }
    .co-row:first-of-type { margin-top: 10px; }
    .co-row strong { color: var(--ink); font-variant-numeric: tabular-nums; }

    .co-total {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        border-top: 1px solid var(--line);
        margin-top: 8px;
        padding-top: 14px;
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 700;
        font-size: 21px;
        font-variant-numeric: tabular-nums;
    }

    .co-note { font-size: 12px; color: var(--muted); margin: 12px 0 0; }

    .coupon-chip {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 700;
        font-size: 13px;
        background: #FEF6E4;
        border: 1px solid #EBD6A2;
        color: #8A6410;
        border-radius: 6px;
        padding: 3px 9px;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const provinceSelect   = document.getElementById('province_select');
    const districtSelect   = document.getElementById('district_select');
    const wardSelect       = document.getElementById('ward_select');
    const shippingFeeText  = document.getElementById('shipping_fee_text');
    const finalTotalText   = document.getElementById('final_total_text');
    const shippingFeeInput = document.getElementById('shipping_fee_input');
    const totalPriceInput  = document.getElementById('total_price_input');
    const submitBtn        = document.getElementById('submit_btn');
    const coNote           = document.getElementById('co_note');

    const districtsUrl = "{{ route('locations.districts', ['provinceId' => '__PROVINCE__']) }}";
    const wardsUrl     = "{{ route('locations.wards', ['districtId' => '__DISTRICT__']) }}";

    const subtotal = parseInt(document.getElementById('subtotal_input').value) || 0;
    let   discount = parseInt(document.getElementById('discount_input').value) || 0;
    let   feeReady = false;
    let   lastFee  = 0;
    let   lastFeeReadyFlag = false;

    const vnd = n => new Intl.NumberFormat('vi-VN').format(n) + ' ₫';

       function updateTotals(fee, ready = true) {
        lastFee = fee;
        lastFeeReadyFlag = ready;

        const grandTotal = Math.max(0, subtotal - discount) + fee;

        shippingFeeText.innerText = ready ? vnd(fee) : 'Chọn địa chỉ để tính';
        finalTotalText.innerText  = vnd(grandTotal);
        shippingFeeInput.value    = fee;
        totalPriceInput.value     = grandTotal;

        feeReady = ready && fee > 0;
        submitBtn.disabled = !feeReady;
        coNote.innerText = feeReady
            ? 'Phí vận chuyển do GHN tính theo địa chỉ nhận hàng.'
            : 'Vui lòng chọn Tỉnh/Quận/Phường để hệ thống tính phí vận chuyển.';

        checkMomoLimit(grandTotal);
    }

    // ===== Mã giảm giá — xử lý bằng AJAX để KHÔNG tải lại trang (giữ nguyên lựa chọn địa chỉ) =====
    const csrfToken       = document.querySelector('meta[name="csrf-token"]').content;
    const couponApplyForm = document.getElementById('couponApplyForm');
    const couponApplied   = document.getElementById('couponApplied');
    const couponError     = document.getElementById('couponError');
    const discountInput   = document.getElementById('discount_input');
    const discountRow     = document.getElementById('discountRow');

    function setDiscount(amount, code) {
        discount = amount;
        discountInput.value = amount;

        if (amount > 0) {
            discountRow.style.display = 'flex';
            document.getElementById('discountCouponLabel').innerText = '(' + code + ')';
            document.getElementById('discountAmountText').innerText  = '-' + vnd(amount);
        } else {
            discountRow.style.display = 'none';
        }

        updateTotals(lastFee, lastFeeReadyFlag);
    }

    couponApplyForm?.addEventListener('submit', function (e) {
        e.preventDefault();

        const input = document.getElementById('couponCodeInput');
        const btn   = document.getElementById('couponApplyBtn');
        couponError.style.display = 'none';
        btn.disabled = true;
        btn.innerText = 'Đang áp dụng...';

        fetch("{{ route('coupon.apply') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ code: input.value, subtotal: subtotal }),
        })
        .then(r => r.json())
        .then(res => {
            if (!res.ok) {
                couponError.innerText = res.message;
                couponError.style.display = 'block';
                return;
            }

            setDiscount(res.discount, res.code);
            document.getElementById('couponChipCode').innerText = res.code;
            document.getElementById('couponChipInfo').innerText = 'Đã áp dụng · giảm ' + vnd(res.discount);
            couponApplied.style.display   = 'flex';
            couponApplyForm.style.display = 'none';
        })
        .catch(() => {
            couponError.innerText = 'Có lỗi xảy ra, vui lòng thử lại.';
            couponError.style.display = 'block';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = 'Áp dụng';
        });
    });

    document.getElementById('couponRemoveBtn')?.addEventListener('click', function () {
        fetch("{{ route('coupon.remove') }}", {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(() => {
            setDiscount(0, '');
            couponApplied.style.display        = 'none';
            couponApplyForm.style.display      = 'flex';
            document.getElementById('couponCodeInput').value = '';
        });
    });

    // MoMo khong ho tro giao dich tren 50 trieu
    function checkMomoLimit(total) {
        const momoRadio = document.getElementById('payMomo');
        const label     = momoRadio.closest('.pay-option');
        const over      = total > 50000000;

        momoRadio.disabled   = over;
        label.style.opacity  = over ? '.5' : '1';
        label.style.cursor   = over ? 'not-allowed' : 'pointer';

        if (over) {
            document.getElementById('payCod').checked = true;
            label.querySelector('small').innerText =
                'Không khả dụng: đơn vượt hạn mức 50.000.000 ₫ của MoMo.';
        }
    }

    // 1. Tải danh sách Tỉnh/Thành từ GHN
    fetch("{{ route('locations.provinces') }}")
        .then(r => r.json())
        .then(res => {
            if (!res.data) throw new Error('no data');
            let html = '<option value="">-- Chọn Tỉnh/Thành --</option>';
            res.data.forEach(p => html += `<option value="${p.ProvinceID}">${p.ProvinceName}</option>`);
            provinceSelect.innerHTML = html;
        })
        .catch(err => {
            console.error('Lỗi load tỉnh thành:', err);
            provinceSelect.innerHTML = '<option value="">-- Lỗi kết nối GHN --</option>';
        });

    // 2. Chọn Tỉnh -> tải Quận/Huyện
    provinceSelect.addEventListener('change', function () {
        districtSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        districtSelect.disabled  = true;
        wardSelect.innerHTML     = '<option value="">-- Chọn Phường/Xã --</option>';
        wardSelect.disabled      = true;
        updateTotals(0, false);
        if (!this.value) return;

        fetch(districtsUrl.replace('__PROVINCE__', this.value))
            .then(r => r.json())
            .then(res => {
                if (!res.data) throw new Error('no data');
                let html = '<option value="">-- Chọn Quận/Huyện --</option>';
                res.data.forEach(d => html += `<option value="${d.DistrictID}">${d.DistrictName}</option>`);
                districtSelect.innerHTML = html;
                districtSelect.disabled  = false;
            })
            .catch(err => {
                console.error('Lỗi load quận huyện:', err);
                districtSelect.innerHTML = '<option value="">-- Lỗi kết nối GHN --</option>';
            });
    });

    // 3. Chọn Quận/Huyện -> tải Phường/Xã
    districtSelect.addEventListener('change', function () {
        wardSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        wardSelect.disabled  = true;
        updateTotals(0, false);
        if (!this.value) return;

        fetch(wardsUrl.replace('__DISTRICT__', this.value))
            .then(r => r.json())
            .then(res => {
                if (!res.data) throw new Error('no data');
                let html = '<option value="">-- Chọn Phường/Xã --</option>';
                res.data.forEach(w => html += `<option value="${w.WardCode}">${w.WardName}</option>`);
                wardSelect.innerHTML = html;
                wardSelect.disabled  = false;
            })
            .catch(err => {
                console.error('Lỗi load phường xã:', err);
                wardSelect.innerHTML = '<option value="">-- Lỗi kết nối GHN --</option>';
            });
    });

    // 4. Chọn Phường/Xã -> tính cước vận chuyển
    wardSelect.addEventListener('change', function () {
        if (!this.value || !districtSelect.value) return;
        shippingFeeText.innerText = 'Đang tính cước...';

        fetch("{{ route('locations.fee') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                to_district_id: districtSelect.value,
                to_ward_code:   this.value
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.code === 200 && res.data) {
                updateTotals(parseInt(res.data.total) || 0);
            } else {
                updateTotals(0, false);
                shippingFeeText.innerText = 'Tuyến này chưa hỗ trợ';
            }
        })
        .catch(err => {
            console.error('Lỗi tính phí:', err);
            updateTotals(0, false);
            shippingFeeText.innerText = 'Lỗi tính phí';
        });
    });

    // 5. Chặn submit khi chưa có phí ship
    document.getElementById('checkout_form').addEventListener('submit', function (e) {
        if (!feeReady) {
            e.preventDefault();
            alert('Vui lòng chọn đầy đủ Tỉnh/Quận/Phường để tính phí vận chuyển.');
        } else {
            submitBtn.disabled  = true;
            submitBtn.innerText = 'Đang xử lý...';
        }
    });
});
</script>
@endpush
@endsection