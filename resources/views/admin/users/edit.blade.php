{{-- resources/views/admin/users/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Chỉnh sửa người dùng')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Chỉnh sửa người dùng</h1>
        <p class="page-sub">{{ $user->name }} · {{ $user->email }}</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-line">← Quay lại</a>
</div>

<div class="card-panel">
    <div class="panel-body" style="max-width:520px">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Họ tên</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
            </div>

            <div class="mb-4">
                <label class="form-label">Vai trò</label>
                <select name="role" class="form-select" required>
                    <option value="customer" @selected(old('role', $user->role) === 'customer')>Khách hàng</option>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Quản trị viên</option>
                </select>
                @if ($user->id === auth()->id())
                    <div class="form-text">Đây là tài khoản đang đăng nhập — không thể tự hạ quyền.</div>
                @endif
            </div>

            <button type="submit" class="btn btn-ink">Cập nhật</button>
        </form>
    </div>
</div>
@endsection
