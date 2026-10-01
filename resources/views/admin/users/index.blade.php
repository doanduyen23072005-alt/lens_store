{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Người dùng')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Người dùng</h1>
        <p class="page-sub">Quản lý tài khoản khách hàng và quản trị viên.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-ink">+ Thêm người dùng</a>
</div>

<div class="card-panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2 flex-wrap w-100">
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control"
                   style="max-width:280px" placeholder="Tìm theo tên hoặc email">
            <select name="role" class="form-select" style="max-width:190px">
                <option value="">Tất cả vai trò</option>
                <option value="admin" @selected(($filters['role'] ?? null) === 'admin')>Quản trị viên</option>
                <option value="customer" @selected(($filters['role'] ?? null) === 'customer')>Khách hàng</option>
            </select>
            <button class="btn btn-line">Lọc</button>
            @if (request('search') || request('role'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-line">Xoá bộ lọc</a>
            @endif
        </form>
    </div>

    @if ($users->count())
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th class="text-center">Vai trò</th>
                        <th class="text-center">Số đơn hàng</th>
                        <th>Ngày tạo</th>
                        <th class="text-end" style="width:210px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="num text-muted">#{{ $user->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="fw-semibold text-dark">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td class="text-muted small">{{ $user->email }}</td>
                        <td class="text-center">
                            <span class="tag {{ $user->isAdmin() ? 'tag-ok' : '' }}">{{ $user->role_label }}</span>
                        </td>
                        <td class="text-center num">{{ $user->orders_count }}</td>
                        <td class="text-muted small">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-line btn-sm">Xem</a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-line btn-sm">Sửa</a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Xoá người dùng “{{ $user->name }}”?')">
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
            <span class="text-muted small">
                Hiển thị {{ $users->firstItem() }}–{{ $users->lastItem() }} trong {{ $users->total() }} người dùng
            </span>
            {{ $users->links() }}
        </div>
    @else
        <div class="empty">
            <h4>Không có người dùng nào khớp bộ lọc</h4>
            <a href="{{ route('admin.users.create') }}" class="btn btn-ink">Thêm người dùng</a>
        </div>
    @endif
</div>
@endsection
