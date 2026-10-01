{{-- resources/views/admin/marketing/compose.blade.php --}}
@extends('layouts.admin')
@section('title', 'Email marketing')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Email marketing / Thông báo</h1>
        <p class="page-sub">Gửi email khuyến mãi hoặc thông báo tới toàn bộ khách hàng đã xác thực email.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-panel panel-body">
            <form action="{{ route('admin.marketing.send') }}" method="POST"
                  onsubmit="return confirm('Gửi email này tới {{ $customerCount }} khách hàng?')">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="subject">Tiêu đề email</label>
                    <input type="text" id="subject" name="subject" class="form-control"
                           value="{{ old('subject') }}" placeholder="VD: Săn sale cuối tuần — giảm đến 20%" required maxlength="150">
                </div>

                <div class="mb-3">
                    <label class="form-label" for="body">Nội dung</label>
                    <textarea id="body" name="body" rows="8" class="form-control" required maxlength="3000"
                              placeholder="Mỗi dòng sẽ hiển thị như một đoạn riêng trong email.">{{ old('body') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="coupon_code">Đính kèm mã giảm giá (không bắt buộc)</label>
                    <select id="coupon_code" name="coupon_code" class="form-select">
                        <option value="">— Không đính kèm —</option>
                        @foreach ($coupons as $coupon)
                            <option value="{{ $coupon->code }}" @selected(old('coupon_code') === $coupon->code)>
                                {{ $coupon->code }} — {{ $coupon->value_label }}
                            </option>
                        @endforeach
                    </select>
                    @if ($coupons->isEmpty())
                        <div class="form-text">Chưa có mã giảm giá nào đang chạy. <a href="{{ route('admin.coupons.create') }}">Tạo mã mới</a>.</div>
                    @endif
                </div>

                <button type="submit" class="btn btn-ink">Gửi email tới {{ number_format($customerCount) }} khách hàng</button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-head"><strong>Lưu ý</strong></div>
            <div class="panel-body text-muted" style="font-size:13px">
                <ul class="ps-3 mb-0">
                    <li class="mb-2">Chỉ gửi tới khách hàng (không gửi cho quản trị viên) đã xác thực email.</li>
                    <li class="mb-2">Hiện có <strong>{{ number_format($customerCount) }}</strong> khách hàng đủ điều kiện nhận email.</li>
                    <li>Hệ thống cũng tự động gửi email xác nhận đơn hàng và cập nhật trạng thái giao hàng.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
