{{-- resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') · Lens Store</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --barrel:   #121417;   /* thân ống kính */
            --barrel-2: #1E2126;
            --barrel-3: #262A30;
            --coating:  #E3A930;   /* ánh vàng lớp tráng phủ */
            --coating-2:#8C7CE8;   /* ánh tím lớp tráng phủ */
            --paper:    #F5F6F8;
            --surface:  #FFFFFF;
            --line:     #E5E7EB;
            --line-2:   #EEF0F2;
            --ink:      #14161A;
            --muted:    #6B7280;
            --muted-2:  #9CA3AF;
            --danger:   #D6362A;
            --danger-bg:#FBEAE8;
            --ok:       #12925F;
            --ok-bg:    #E5F6EE;
            --warn:     #A9720C;
            --warn-bg:  #FDF3DE;
            --info:     #205EA6;
            --info-bg:  #E8F1FC;
            --radius-lg: 18px;
            --radius-md: 12px;
            --radius-sm: 9px;
            --shadow-xs: 0 1px 2px rgba(20,22,26,.05);
            --shadow-sm: 0 4px 14px rgba(20,22,26,.06);
            --shadow-md: 0 10px 28px rgba(20,22,26,.10);
            --sidebar-w: 260px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--paper);
            color: var(--ink);
            font-size: 15px;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, .display-font {
            font-family: 'Space Grotesk', 'Inter', sans-serif;
            letter-spacing: -.015em;
        }

        /* ---------- Sidebar ---------- */
        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: var(--sidebar-w);
            background: linear-gradient(180deg, var(--barrel), #0C0D0F);
            color: #E7E9EC;
            padding: 22px 16px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            overflow-y: auto;
            box-shadow: 0 0 0 1px rgba(255,255,255,.03) inset;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 4px 6px 16px;
            border-bottom: 1px solid rgba(255,255,255,.06);
        }

        .brand-mark {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: radial-gradient(circle at 32% 28%, var(--coating) 0 22%, var(--coating-2) 23% 46%, #101216 47% 100%);
            box-shadow: 0 0 0 2px rgba(255,255,255,.08), 0 4px 12px rgba(0,0,0,.4);
            flex: none;
        }

        .brand-name {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 16px;
            line-height: 1.1;
        }

        .brand-sub {
            font-size: 11px;
            color: #8A9099;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .nav-label {
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #6E747C;
            padding: 0 10px 10px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            color: #C6CAD0;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: background .15s ease, color .15s ease, transform .15s ease;
        }

        .nav-item svg { flex: none; opacity: .8; }

        .nav-item:hover { background: var(--barrel-3); color: #fff; transform: translateX(1px); }
        .nav-item:hover svg { opacity: 1; }

        .nav-item.active {
            background: linear-gradient(90deg, rgba(227,169,48,.14), rgba(227,169,48,.03));
            color: #fff;
            border-left-color: var(--coating);
        }
        .nav-item.active svg { opacity: 1; color: var(--coating); }

        .nav-item .count {
            margin-left: auto;
            font-size: 11px;
            font-weight: 700;
            color: #C6CAD0;
            background: rgba(255,255,255,.08);
            padding: 2px 8px;
            border-radius: 999px;
        }

        .sidebar-foot {
            margin-top: auto;
            font-size: 12px;
            color: #6E747C;
            border-top: 1px solid rgba(255,255,255,.06);
            padding-top: 14px;
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--barrel-3), #33383F);
            color: var(--coating);
            display: grid;
            place-items: center;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            flex: none;
            box-shadow: 0 0 0 1px rgba(255,255,255,.06);
        }

        .user-name { font-size: 13px; font-weight: 600; color: #E7E9EC; line-height: 1.2; }
        .user-role { font-size: 11px; color: #71777F; }

        .btn-logout {
            background: transparent;
            border: 1px solid #33383F;
            color: #C6CAD0;
            border-radius: var(--radius-sm);
            font-size: 12px;
            padding: 5px 11px;
            margin-left: auto;
            transition: all .15s ease;
        }
        .btn-logout:hover { border-color: #565D66; color: #fff; background: rgba(255,255,255,.04); }

        /* ---------- Vùng nội dung ---------- */
        .main { margin-left: var(--sidebar-w); padding: 30px 34px 60px; max-width: 1440px; }

        .topbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .page-title { font-size: 27px; font-weight: 700; margin: 0; letter-spacing: -.02em; }
        .page-sub { color: var(--muted); font-size: 14px; margin: 5px 0 0; }

        .card-panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xs);
        }

        .panel-head {
            padding: 15px 20px;
            border-bottom: 1px solid var(--line-2);
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .panel-body { padding: 20px; }

        /* ---------- Thẻ số liệu ---------- */
        .stat {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 17px 19px 15px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-xs);
            transition: box-shadow .2s ease, transform .2s ease;
        }
        .stat:hover { box-shadow: var(--shadow-sm); transform: translateY(-2px); }

        .stat::before {
            content: "";
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: linear-gradient(180deg, var(--coating), var(--coating-2));
            opacity: .55;
        }

        .stat-label {
            font-size: 11px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 600;
        }

        .stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 29px;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            margin-top: 3px;
            letter-spacing: -.01em;
        }

        .stat.accent { border-color: #EFDBA0; background: linear-gradient(180deg, #FFFDF7, #fff); }
        .stat.accent .stat-value { color: #8A6410; }
        .stat.accent::before { opacity: 1; }

        /* ---------- Bảng ---------- */
        .table { margin: 0; }

        .table > thead th {
            font-size: 11px;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 700;
            border-bottom: 1px solid var(--line);
            padding: 13px 16px;
            background: #FAFBFC;
            white-space: nowrap;
        }

        .table > tbody td {
            padding: 13px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--line-2);
        }

        .table > tbody tr { transition: background .12s ease; }
        .table > tbody tr:hover { background: #FAFBFC; }
        .table > tbody tr:last-child td { border-bottom: 0; }

        .num { font-variant-numeric: tabular-nums; }

        .thumb {
            width: 52px; height: 52px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--line);
            background: #F6F7F8;
        }

        .thumb-empty {
            width: 52px; height: 52px;
            border-radius: var(--radius-sm);
            border: 1px dashed var(--line);
            display: grid;
            place-items: center;
            color: #A6ACB5;
            font-size: 11px;
        }

        .code-chip {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12px;
            background: #F0F1F3;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 2px 7px;
        }

        .tag {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: #F7F8F9;
            color: #3F454D;
        }

        .tag-ok     { background: var(--ok-bg);     border-color: #BFE3D4; color: var(--ok); }
        .tag-warn   { background: var(--warn-bg);   border-color: #EBD6A2; color: var(--warn); }
        .tag-danger { background: var(--danger-bg); border-color: #EFC7C1; color: var(--danger); }

        /* ---------- Nút ---------- */
        .btn { border-radius: var(--radius-sm); font-weight: 600; transition: all .15s ease; }

        .btn-ink {
            background: var(--barrel);
            border-color: var(--barrel);
            color: #fff;
            box-shadow: var(--shadow-xs);
        }
        .btn-ink:hover { background: #000; border-color: #000; color: #fff; transform: translateY(-1px); box-shadow: var(--shadow-sm); }

        .btn-line {
            background: #fff;
            border: 1px solid var(--line);
            color: #3F454D;
        }
        .btn-line:hover { border-color: #C7CBD1; background: #FAFAFB; color: var(--ink); }

        .btn-sm { padding: 5px 11px; font-size: 13px; }

        .form-label { font-size: 13px; font-weight: 600; margin-bottom: 5px; }
        .form-control, .form-select { border-radius: var(--radius-sm); border-color: var(--line); padding: 9px 13px; }
        .form-control:focus, .form-select:focus {
            border-color: var(--coating);
            box-shadow: 0 0 0 4px rgba(227, 169, 48, .15);
        }
        .form-text { font-size: 12px; }

        .alert { border-radius: var(--radius-md); border: 1px solid transparent; border-left-width: 4px; padding: 13px 16px; }
        .alert-success { background: var(--ok-bg); border-color: #BFE3D4; border-left-color: var(--ok); color: var(--ok); }
        .alert-danger  { background: var(--danger-bg); border-color: #EFC7C1; border-left-color: var(--danger); color: var(--danger); }

        .empty {
            text-align: center;
            padding: 50px 20px;
            color: var(--muted);
        }
        .empty h4 { color: var(--ink); font-size: 17px; }

        .page-link { color: #3F454D; border-radius: 8px; margin: 0 2px; border-color: var(--line); }
        .page-item.active .page-link { background: var(--barrel); border-color: var(--barrel); }

        a { text-decoration: none; }
        ::selection { background: rgba(227,169,48,.35); }

        /* ---------- Popup chat hỗ trợ ---------- */
        #admin-chat-box { position: fixed; right: 24px; bottom: 24px; z-index: 70; }

        #admin-chat-box > #chat-toggle {
            border: 0;
            border-radius: 999px;
            padding: 12px 20px;
            background: var(--barrel);
            color: #fff;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(0,0,0,.25);
            position: relative;
        }
        #admin-chat-box > #chat-toggle:hover { background: #000; }

        .chat-unread {
            position: absolute;
            top: -6px; right: -6px;
            min-width: 22px; height: 22px;
            padding: 0 6px;
            border-radius: 999px;
            background: var(--danger);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            display: grid;
            place-items: center;
            border: 2px solid #fff;
        }

        #admin-chat-box #chat-popup {
            display: none;
            width: 620px;
            height: 480px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: 0 20px 50px rgba(0,0,0,.22);
            overflow: hidden;
            flex-direction: column;
        }
        #admin-chat-box #chat-popup.open { display: flex; }

        #admin-chat-box .chat-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 16px;
            background: var(--barrel);
            color: #fff;
        }

        #admin-chat-box #chat-close {
            background: none; border: 0; color: #C6CAD0;
            font-size: 22px; line-height: 1; cursor: pointer;
        }
        #admin-chat-box #chat-close:hover { color: #fff; }

        .chat-body { flex: 1; display: flex; min-height: 0; }

        #user-list {
            width: 200px;
            border-right: 1px solid var(--line);
            overflow-y: auto;
            background: #FAFAFB;
        }

        .user-item {
            padding: 11px 14px;
            border-bottom: 1px solid #EFF1F3;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }
        .user-item:hover { background: #F0F1F3; }
        .user-item.active { background: var(--barrel); color: #fff; }

        .user-badge {
            min-width: 20px; height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: var(--danger);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: grid;
            place-items: center;
            flex: none;
        }

        .chat-right { flex: 1; display: flex; flex-direction: column; min-width: 0; }

        #admin-chat-box #chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            background: #F7F8F9;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-empty { margin: auto; color: var(--muted); font-size: 13px; text-align: center; }

        .msg {
            max-width: 78%;
            padding: 9px 13px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.45;
            word-break: break-word;
        }

        .msg-me   { align-self: flex-end;   background: var(--barrel); color: #fff; border-bottom-right-radius: 4px; }
        .msg-them { align-self: flex-start; background: #fff; border: 1px solid var(--line); border-bottom-left-radius: 4px; }
        .msg-time { font-size: 11px; opacity: .65; margin-top: 3px; }

        #admin-chat-box .chat-foot {
            display: flex;
            gap: 8px;
            padding: 12px;
            border-top: 1px solid var(--line);
            background: #fff;
        }

        #admin-chat-box .chat-foot input {
            flex: 1;
            border: 1px solid var(--line);
            border-radius: 9px;
            padding: 9px 12px;
            font-size: 14px;
            outline: none;
        }
        #admin-chat-box .chat-foot input:focus { border-color: var(--coating); }

        #admin-chat-box .chat-foot button {
            border: 0;
            border-radius: 9px;
            padding: 0 18px;
            background: var(--barrel);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        #admin-chat-box .chat-foot button:disabled,
        #admin-chat-box .chat-foot input:disabled { opacity: .5; cursor: not-allowed; }

        @media (max-width: 680px) {
            #admin-chat-box #chat-popup { width: calc(100vw - 48px); height: 70vh; }
            #user-list { width: 130px; }
        }

        @media (max-width: 900px) {
            :root { --sidebar-w: 0px; }
            .sidebar {
                position: static;
                width: auto;
                flex-direction: row;
                align-items: center;
                gap: 14px;
                overflow-x: auto;
                padding: 12px 14px;
            }
            .sidebar .nav-label { display: none; }
            .sidebar-foot { margin-top: 0; border-top: 0; padding-top: 0; margin-left: auto; }
            .user-box { border-top: 0; padding-top: 0; }
            .user-box > div:not(.avatar) { display: none; }
            .sidebar nav { display: flex; gap: 8px; }
            .nav-item { white-space: nowrap; border-left: 0; border-bottom: 3px solid transparent; }
            .nav-item.active { border-left: 0; border-bottom-color: var(--coating); }
            .main { margin-left: 0; padding: 18px 16px 50px; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark"></div>
        <div>
            <div class="brand-name">Lens Store</div>
            <div class="brand-sub">Trang quản trị</div>
        </div>
    </div>

    <div>
        <div class="nav-label">Quản trị</div>
        <nav class="d-flex flex-column gap-1">
            <a class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
               href="{{ route('admin.dashboard') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v9a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1v-9"/></svg>
                Tổng quan
            </a>
            <a class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}"
               href="{{ route('products.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7.5 12 3 3 7.5l9 4.5 9-4.5Z"/><path d="M3 7.5v9L12 21l9-4.5v-9"/><path d="M12 12v9"/></svg>
                Ống kính
                <span class="count">{{ \App\Models\Product::count() }}</span>
            </a>
            <a class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}"
               href="{{ route('categories.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a1 1 0 0 1 1-1h5l2 2h9a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V7Z"/></svg>
                Phân loại
                <span class="count">{{ \App\Models\Category::count() }}</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"
               href="{{ route('admin.orders.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8V6a6 6 0 0 1 12 0v2"/><rect x="3.5" y="8" width="17" height="13" rx="2"/></svg>
                Đơn hàng
                <span class="count">{{ \App\Models\Order::count() }}</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.finance.index') ? 'active' : '' }}"
               href="{{ route('admin.finance.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M11 20V4M18 20v-7"/><path d="M3 20h18"/></svg>
                Thống kê tài chính
            </a>
            <a class="nav-item {{ request()->routeIs('admin.finance.transactions') ? 'active' : '' }}"
               href="{{ route('admin.finance.transactions') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2"/><path d="M2.5 10h19"/><path d="M6 15h4"/></svg>
                Giao dịch thanh toán
            </a>
            <a class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
               href="{{ route('admin.users.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 8.2a3 3 0 1 1 3.4 5.9"/><path d="M15.5 14.3c2.7.2 5 1.7 5.9 4.2"/></svg>
                Người dùng
                <span class="count">{{ \App\Models\User::count() }}</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}"
               href="{{ route('admin.reviews.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"><path d="m12 3 2.6 5.6 6 .7-4.5 4.1 1.2 6-5.3-3-5.3 3 1.2-6-4.5-4.1 6-.7L12 3Z"/></svg>
                Đánh giá
                <span class="count">{{ \App\Models\Review::count() }}</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"
               href="{{ route('admin.coupons.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 9.5 3H19a2 2 0 0 1 2 2v9.5L14.5 21 3 9.5Z"/><circle cx="9.5" cy="8.5" r="1.4"/></svg>
                Mã giảm giá
                <span class="count">{{ \App\Models\Coupon::count() }}</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.marketing.*') ? 'active' : '' }}"
               href="{{ route('admin.marketing.compose') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6 8.5 6.5L20.5 6"/></svg>
                Marketing
            </a>
            <a class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"
               href="{{ route('admin.reports.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M11 20V4M18 20v-7"/><path d="M3 20h18"/></svg>
                Báo cáo
            </a>
        </nav>
    </div>

    <div>
        <div class="nav-label">Nội dung</div>
        <nav class="d-flex flex-column gap-1">
            <a class="nav-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}"
               href="{{ route('admin.pages.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h6"/></svg>
                Trang tĩnh
                <span class="count">{{ \App\Models\Page::count() }}</span>
            </a>
            <a class="nav-item {{ request()->routeIs('admin.posts.*') ? 'active' : '' }}"
               href="{{ route('admin.posts.index') }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="4" width="17" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>
                Blog / Tin tức
                <span class="count">{{ \App\Models\Post::count() }}</span>
            </a>
            <a class="nav-item" href="{{ route('home') }}" target="_blank">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6"/><path d="M10 14 20 4"/><path d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6"/></svg>
                Xem cửa hàng
            </a>
        </nav>
    </div>

    <div class="sidebar-foot">
        @auth
            <div class="user-box">
                <div class="avatar">{{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}</div>
                <a href="{{ route('profile.edit') }}" style="text-decoration:none">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ Auth::user()->role_label }}</div>
                </a>
                <form action="{{ route('logout') }}" method="POST" class="ms-auto">
                    @csrf
                    <button class="btn btn-logout">Thoát</button>
                </form>
            </div>
        @endauth
    </div>
</aside>

<main class="main">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Chưa lưu được. Kiểm tra lại các mục sau:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

{{-- ===== Popup chat hỗ trợ khách hàng ===== --}}
@auth
<div id="admin-chat-box">
    <button id="chat-toggle" type="button">
        💬 Chat khách hàng
        <span id="chat-unread" class="chat-unread" style="display:none">0</span>
    </button>

    <div id="chat-popup">
        <div class="chat-head">
            <strong>Hỗ trợ trực tuyến</strong>
            <button id="chat-close" type="button">×</button>
        </div>

        <div class="chat-body">
            <div id="user-list">
                <div class="chat-empty"><small>Đang tải...</small></div>
            </div>

            <div class="chat-right">
                <div id="chat-messages">
                    <div class="chat-empty">Chọn một khách hàng để xem tin nhắn</div>
                </div>

                <div class="chat-foot">
                    <input type="text" id="chat-input" placeholder="Nhập câu trả lời..." autocomplete="off" maxlength="2000" disabled>
                    <button id="send-btn" type="button" disabled>Gửi</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@auth
<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggleBtn = document.getElementById("chat-toggle");
    if (!toggleBtn) return;

    const popup    = document.getElementById("chat-popup");
    const closeBtn = document.getElementById("chat-close");
    const sendBtn  = document.getElementById("send-btn");
    const input    = document.getElementById("chat-input");
    const box      = document.getElementById("chat-messages");
    const listEl   = document.getElementById("user-list");
    const unread   = document.getElementById("chat-unread");
    const myId     = "{{ Auth::id() }}";
    const csrf     = document.querySelector('meta[name="csrf-token"]').content;

    let currentUserId = null;
    let lastCount     = 0;

    // Chong XSS: khong chen thang noi dung tin nhan vao innerHTML
    const esc = s => { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

    const fmtTime = iso => {
        const d = new Date(iso);
        return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
    };

    toggleBtn.onclick = () => {
        popup.classList.add("open");
        loadUsers();
    };

    closeBtn.onclick = () => popup.classList.remove("open");

    function loadUsers() {
        fetch("{{ route('admin.chat.users') }}")
            .then(r => r.json())
            .then(users => {
                if (!users.length) {
                    listEl.innerHTML = '<div class="chat-empty"><small>Chưa có hội thoại</small></div>';
                    return;
                }

                listEl.innerHTML = users.map(u => `
                    <div class="user-item ${currentUserId == u.id ? 'active' : ''}" data-id="${u.id}">
                        <span>${esc(u.name)}</span>
                        ${u.unread > 0 ? `<span class="user-badge">${u.unread}</span>` : ''}
                    </div>
                `).join('');

                listEl.querySelectorAll('.user-item').forEach(el => {
                    el.onclick = () => selectUser(parseInt(el.dataset.id));
                });
            })
            .catch(err => console.error("Lỗi tải danh sách:", err));
    }

    function selectUser(userId) {
        currentUserId = userId;
        lastCount     = -1;

        listEl.querySelectorAll('.user-item').forEach(el => {
            el.classList.toggle('active', parseInt(el.dataset.id) === userId);
        });

        input.disabled = sendBtn.disabled = false;
        loadMessages();
        input.focus();
    }

    function loadMessages() {
        if (!currentUserId) return;

        fetch(`{{ url('admin/chat/messages') }}/${currentUserId}`)
            .then(r => r.json())
            .then(messages => {
                if (messages.length === lastCount) return;
                lastCount = messages.length;

                if (!messages.length) {
                    box.innerHTML = '<div class="chat-empty">Chưa có tin nhắn</div>';
                    return;
                }

                box.innerHTML = messages.map(m => {
                    const me = m.sender_id == myId;
                    return `<div class="msg ${me ? 'msg-me' : 'msg-them'}">
                                ${esc(m.content)}
                                <div class="msg-time">${fmtTime(m.created_at)}</div>
                            </div>`;
                }).join('');

                box.scrollTop = box.scrollHeight;
            })
            .catch(err => console.error("Lỗi tải tin nhắn:", err));
    }

    function checkUnread() {
        fetch("{{ route('admin.chat.unread') }}")
            .then(r => r.json())
            .then(res => {
                if (res.count > 0 && !popup.classList.contains("open")) {
                    unread.textContent = res.count;
                    unread.style.display = "grid";
                } else {
                    unread.style.display = "none";
                }
            })
            .catch(() => {});
    }

    function sendMessage() {
        const message = input.value.trim();
        if (!message || !currentUserId) return;

        input.disabled = sendBtn.disabled = true;

        fetch("{{ route('admin.chat.send') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrf,
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ message, user_id: currentUserId })
        })
        .then(r => r.json())
        .then(() => {
            input.value = "";
            lastCount   = -1;
            loadMessages();
        })
        .catch(err => console.error("Lỗi gửi tin:", err))
        .finally(() => {
            input.disabled = sendBtn.disabled = false;
            input.focus();
        });
    }

    sendBtn.onclick  = sendMessage;
    input.onkeypress = e => { if (e.key === 'Enter') sendMessage(); };

    // Cap nhat lien tuc moi 3 giay
    setInterval(() => {
        if (popup.classList.contains("open")) {
            loadMessages();
            loadUsers();
        } else {
            checkUnread();
        }
    }, 3000);

    checkUnread();
});
</script>
@endauth

@stack('scripts')
</body>
</html>