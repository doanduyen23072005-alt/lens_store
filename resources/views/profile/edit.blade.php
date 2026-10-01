{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.shop')
@section('title', 'Hồ sơ của tôi')

@section('content')
<div class="mb-4">
    <h1 class="mb-1">Hồ sơ của tôi</h1>
    <p class="text-muted mb-0">Xem và cập nhật thông tin tài khoản.</p>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="bg-white border rounded-4 p-4">
            <h2 style="font-size:16px" class="mb-3">Thông tin cá nhân</h2>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Họ tên</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    @if ($user->hasVerifiedEmail())
                        <div class="form-text text-success">Đã xác thực.</div>
                    @else
                        <div class="form-text text-warning">Chưa xác thực email.</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label">Vai trò</label>
                    <input type="text" value="{{ $user->role_label }}" class="form-control" disabled>
                </div>

                <button type="submit" class="btn btn-ink">Cập nhật thông tin</button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="bg-white border rounded-4 p-4">
            <h2 style="font-size:16px" class="mb-3">Đổi mật khẩu</h2>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Mật khẩu hiện tại</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>

                <div class="mb-3">
                    <label class="form-label">Nhập lại mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                </div>

                <button type="submit" class="btn btn-ink">Đổi mật khẩu</button>
            </form>
        </div>
    </div>
</div>
@endsection
