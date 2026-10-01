{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Thêm người dùng')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Thêm người dùng</h1>
        <p class="page-sub">Tạo tài khoản quản trị viên hoặc khách hàng mới.</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-line">← Quay lại</a>
</div>

<div class="card-panel">
    <div class="panel-body" style="max-width:520px">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Họ tên</label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control" required minlength="8">
                <div class="form-text">Tối thiểu 8 ký tự.</div>
            </div>

            <div class="mb-4">
                <label class="form-label">Vai trò</label>
                <select name="role" class="form-select" required>
                    <option value="customer" @selected(old('role') === 'customer')>Khách hàng</option>
                    <option value="admin" @selected(old('role') === 'admin')>Quản trị viên</option>
                </select>
            </div>

            <button type="submit" class="btn btn-ink">Lưu người dùng</button>
        </form>
    </div>
</div>
@endsection
