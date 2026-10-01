{{-- resources/views/admin/coupons/_form.blade.php --}}
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-panel panel-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="code">Mã giảm giá</label>
                    <input type="text" id="code" name="code" class="form-control text-uppercase"
                           value="{{ old('code', $coupon->code) }}" placeholder="VD: SALE10" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="description">Mô tả ngắn</label>
                    <input type="text" id="description" name="description" class="form-control"
                           value="{{ old('description', $coupon->description) }}" placeholder="VD: Giảm 10% mừng khai trương">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="type">Loại giảm giá</label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="percent" @selected(old('type', $coupon->type) === 'percent')>Phần trăm (%)</option>
                        <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Số tiền cố định (đ)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="value">Giá trị</label>
                    <input type="number" id="value" name="value" class="form-control" step="0.01" min="0.01"
                           value="{{ old('value', $coupon->value) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="max_discount">Giảm tối đa (đ)</label>
                    <input type="number" id="max_discount" name="max_discount" class="form-control" min="0"
                           value="{{ old('max_discount', $coupon->max_discount) }}" placeholder="Chỉ áp cho loại %">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="min_order_amount">Đơn tối thiểu (đ)</label>
                    <input type="number" id="min_order_amount" name="min_order_amount" class="form-control" min="0"
                           value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="usage_limit">Tổng lượt dùng</label>
                    <input type="number" id="usage_limit" name="usage_limit" class="form-control" min="1"
                           value="{{ old('usage_limit', $coupon->usage_limit) }}" placeholder="Không giới hạn">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="per_user_limit">Lượt/khách</label>
                    <input type="number" id="per_user_limit" name="per_user_limit" class="form-control" min="1"
                           value="{{ old('per_user_limit', $coupon->per_user_limit ?? 1) }}" placeholder="Không giới hạn">
                </div>

                <div class="col-md-6">
                    <label class="form-label" for="starts_at">Bắt đầu</label>
                    <input type="datetime-local" id="starts_at" name="starts_at" class="form-control"
                           value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="expires_at">Hết hạn</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" class="form-control"
                           value="{{ old('expires_at', $coupon->expires_at?->format('Y-m-d\TH:i')) }}">
                    <div class="form-text">Bỏ trống nếu không giới hạn thời gian.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-head"><strong>Trạng thái</strong></div>
            <div class="panel-body">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $coupon->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Đang kích hoạt</label>
                </div>
                <div class="form-text mt-2">Tắt để tạm dừng mã mà không cần xoá.</div>
            </div>
        </div>

        <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-ink">{{ $submitLabel }}</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-line">Huỷ</a>
        </div>
    </div>
</div>
