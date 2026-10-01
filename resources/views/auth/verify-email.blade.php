{{-- resources/views/auth/verify-email.blade.php --}}
@extends('layouts.auth')
@section('title', 'Xác thực email')

@section('content')
<h1>Kiểm tra hộp thư</h1>
<p class="auth-sub">
    Chúng tôi đã gửi một liên kết xác thực tới <strong>{{ Auth::user()->email }}</strong>.
    Bấm vào liên kết đó để kích hoạt tài khoản.
</p>

<div class="hint-box mb-3">
    Không thấy email? Kiểm tra thư mục Spam hoặc Quảng cáo. Liên kết có hiệu lực trong 60 phút.
</div>

<form action="{{ route('verification.send') }}" method="POST">
    @csrf
    <div class="d-grid">
        <button type="submit" class="btn btn-ink">Gửi lại email xác thực</button>
    </div>
</form>

<form action="{{ route('logout') }}" method="POST" class="mt-3 text-center">
    @csrf
    <button type="submit"
            style="background:none;border:0;padding:0;color:#8A6410;font-size:14px;cursor:pointer">
        Đăng xuất và dùng tài khoản khác
    </button>
</form>
@endsection