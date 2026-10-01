{{-- resources/views/auth/register.blade.php --}}
@extends('layouts.auth')
@section('title', 'Đăng ký')

@section('content')
<h1>Tạo tài khoản</h1>
<p class="auth-sub">Tài khoản mới có vai trò khách hàng.</p>

<form action="{{ route('register') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label class="form-label" for="name">Họ tên</label>
        <input type="text" id="name" name="name" class="form-control"
               value="{{ old('name') }}" required autofocus autocomplete="name">
    </div>

    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               value="{{ old('email') }}" required autocomplete="email">
    </div>

    <div class="mb-3">
        <label class="form-label" for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" class="form-control"
               required autocomplete="new-password">
        <div class="form-text">Ít nhất 8 ký tự.</div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="password_confirmation">Nhập lại mật khẩu</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="form-control" required autocomplete="new-password">
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-ink">Đăng ký</button>
    </div>
</form>

<p class="mt-3 mb-0 text-center" style="font-size:14px">
    Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
</p>
@endsection