{{-- resources/views/admin/users/show.blade.php --}}
@extends('layouts.admin')
@section('title', 'Thông tin người dùng')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">{{ $user->name }}</h1>
        <p class="page-sub">{{ $user->email }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-line">Sửa</a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-line">← Quay lại</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-panel">
            <div class="panel-head"><strong>Thông tin tài khoản</strong></div>
            <div class="panel-body">
                <p class="mb-2"><span class="stat-label d-block">ID</span>#{{ $user->id }}</p>
                <p class="mb-2">
                    <span class="stat-label d-block">Vai trò</span>
                    <span class="tag {{ $user->isAdmin() ? 'tag-ok' : '' }}">{{ $user->role_label }}</span>
                </p>
                <p class="mb-2">
                    <span class="stat-label d-block">Xác thực email</span>
                    {{ $user->email_verified_at ? 'Đã xác thực' : 'Chưa xác thực' }}
                </p>
                <p class="mb-0">
                    <span class="stat-label d-block">Ngày tạo</span>
                    {{ $user->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-panel">
            <div class="panel-head"><strong>Đơn hàng ({{ $user->orders_count }})</strong></div>
            @php $recentOrders = $user->orders()->latest()->take(10)->get(); @endphp
            @if ($recentOrders->count())
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày tạo</th>
                                <th class="text-end">Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($recentOrders as $order)
                            <tr>
                                <td><span class="code-chip">DH{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span></td>
                                <td class="text-muted small">{{ $order->created_at->format('d/m/Y') }}</td>
                                <td class="text-end num">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                                <td><span class="tag">{{ $order->status_label }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-line btn-sm">Xem</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty"><h4>Chưa có đơn hàng nào</h4></div>
            @endif
        </div>
    </div>
</div>
@endsection
