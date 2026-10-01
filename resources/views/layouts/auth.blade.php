{{-- resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Tài khoản') · Lens Store</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --barrel:   #16181C;
            --coating:  #E0A82E;
            --coating-2:#7C6BD6;
            --paper:    #F1F2F4;
            --line:     #DEE1E6;
            --ink:      #1B1D21;
            --muted:    #6B7280;
            --ok:       #1E7F5C;
            --danger:   #C0392B;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            color: var(--ink);
            background: var(--paper);
        }

        .auth-grid {
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            min-height: 100vh;
        }

        /* Cột trái: mô phỏng mặt trước ống kính */
        .auth-aside {
            background: var(--barrel);
            color: #E7E9EC;
            padding: 46px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .aperture {
            width: 260px;
            height: 260px;
            border-radius: 50%;
            margin: 30px auto;
            background:
                radial-gradient(circle at 34% 30%, rgba(224,168,46,.85) 0 16%,
                                                   rgba(124,107,214,.7) 17% 34%,
                                                   rgba(20,22,26,1) 35% 100%);
            box-shadow: 0 0 0 10px #1E2126, 0 0 0 11px #34393F, 0 26px 60px rgba(0,0,0,.55);
        }

        .aside-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 30px;
            font-weight: 700;
            line-height: 1.15;
            margin: 0;
        }

        .aside-text { color: #9BA2AA; font-size: 14px; margin-top: 10px; max-width: 34ch; }

        .aside-specs {
            display: flex;
            gap: 26px;
            font-size: 12px;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #71777F;
            border-top: 1px solid #2B3037;
            padding-top: 16px;
        }

        /* Cột phải: form */
        .auth-main {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .auth-card { width: 100%; max-width: 420px; }

        .auth-card h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 26px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .auth-sub { color: var(--muted); font-size: 14px; margin-bottom: 24px; }

        .form-label { font-size: 13px; font-weight: 600; margin-bottom: 5px; }

        .form-control {
            border-radius: 9px;
            border-color: var(--line);
            padding: 10px 12px;
        }

        .form-control:focus {
            border-color: var(--coating);
            box-shadow: 0 0 0 3px rgba(224, 168, 46, .18);
        }

        .btn-ink {
            background: var(--barrel);
            border-color: var(--barrel);
            color: #fff;
            border-radius: 9px;
            padding: 10px;
            font-weight: 600;
        }
        .btn-ink:hover { background: #000; border-color: #000; color: #fff; }

        .alert { border-radius: 11px; border: 1px solid transparent; font-size: 14px; }
        .alert-success { background: #E9F6F0; border-color: #BFE3D4; color: var(--ok); }
        .alert-danger  { background: #FBEDEB; border-color: #EFC7C1; color: var(--danger); }

        .hint-box {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 11px;
            padding: 12px 14px;
            font-size: 13px;
            color: var(--muted);
            margin-top: 20px;
        }

        .hint-box code { color: var(--ink); }

        a { color: #8A6410; }

        @media (max-width: 900px) {
            .auth-grid { grid-template-columns: 1fr; }
            .auth-aside { padding: 26px 22px; }
            .aperture { width: 130px; height: 130px; margin: 16px auto; }
            .aside-specs { display: none; }
        }

        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>

<div class="auth-grid">
    <aside class="auth-aside">
        <div>
            <h2 class="aside-title">Lens Store</h2>
            <p class="aside-text">Cửa hàng ống kính máy ảnh. Đăng nhập để quản lý kho hoặc theo dõi sản phẩm bạn quan tâm.</p>
        </div>

        <div class="aperture" aria-hidden="true"></div>

        <div class="aside-specs">
            <span>f/1.2 — f/22</span>
            <span>Ngàm RF · Z · E</span>
        </div>
    </aside>

    <main class="auth-main">
        <div class="auth-card">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

</body>
</html>