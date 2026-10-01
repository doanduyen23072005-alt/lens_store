{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')
@section('title', 'Đăng nhập')

@section('content')
<h1>Đăng nhập</h1>
<p class="auth-sub">Nhập email và mật khẩu của bạn.</p>

<form action="{{ route('login') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control"
               value="{{ old('email') }}" required autofocus autocomplete="email">
    </div>

    <div class="mb-3">
        <label class="form-label" for="password">Mật khẩu</label>
        <input type="password" id="password" name="password" class="form-control"
               required autocomplete="current-password">
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1"
               @checked(old('remember'))>
        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-ink">Đăng nhập</button>
    </div>
</form>

<p class="mt-3 mb-0 text-center" style="font-size:14px">
    Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
</p>

@endsection