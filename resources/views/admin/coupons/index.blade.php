{{-- resources/views/admin/coupons/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Mã giảm giá')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Mã giảm giá</h1>
        <p class="page-sub">Tạo và quản lý các chương trình khuyến mãi.</p>
    </div>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-ink">+ Tạo mã giảm giá</a>
</div>

<div class="card-panel">
    @if ($coupons->count())
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Giá trị</th>
                        <th>Điều kiện</th>
                        <th class="text-center">Đã dùng</th>
                        <th>Hiệu lực</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end" style="width:170px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($coupons as $coupon)
                    @php
                        $expired = $coupon->expires_at && $coupon->expires_at->isPast();
                        $notStarted = $coupon->starts_at && $coupon->starts_at->isFuture();
                    @endphp
                    <tr>
                        <td>
                            <span class="code-chip">{{ $coupon->code }}</span>
                            @if ($coupon->description)
                                <div class="text-muted small mt-1">{{ $coupon->description }}</div>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $coupon->value_label }}</td>
                        <td class="text-muted small">
                            @if ($coupon->min_order_amount > 0)
                                Đơn tối thiểu {{ number_format($coupon->min_order_amount, 0, ',', '.') }} đ<br>
                            @endif
                            @if ($coupon->max_discount)
                                Giảm tối đa {{ number_format($coupon->max_discount, 0, ',', '.') }} đ<br>
                            @endif
                            @if ($coupon->per_user_limit)
                                {{ $coupon->per_user_limit }} lượt/khách
                            @endif
                        </td>
                        <td class="text-center num">
                            {{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}
                        </td>
                        <td class="text-muted small">
                            @if ($coupon->starts_at || $coupon->expires_at)
                                {{ $coupon->starts_at?->format('d/m/Y') ?? '—' }} → {{ $coupon->expires_at?->format('d/m/Y') ?? '—' }}
                            @else
                                Không giới hạn
                            @endif
                        </td>
                        <td class="text-center">
                            @if (! $coupon->is_active)
                                <span class="tag">Đã tắt</span>
                            @elseif ($expired)
                                <span class="tag tag-danger">Hết hạn</span>
                            @elseif ($notStarted)
                                <span class="tag tag-warn">Chưa bắt đầu</span>
                            @else
                                <span class="tag tag-ok">Đang chạy</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-line btn-sm">Sửa</a>
                            <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Xoá mã {{ $coupon->code }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-line btn-sm text-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">Tổng {{ $coupons->total() }} mã giảm giá</span>
            {{ $coupons->links() }}
        </div>
    @else
        <div class="empty">
            <h4>Chưa có mã giảm giá nào</h4>
            <p>Tạo mã đầu tiên để chạy chương trình khuyến mãi.</p>
            <a href="{{ route('admin.coupons.create') }}" class="btn btn-ink">Tạo mã giảm giá</a>
        </div>
    @endif
</div>
@endsection
