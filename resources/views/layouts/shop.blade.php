{{-- resources/views/layouts/shop.blade.php --}}
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Cửa hàng') · Lens Store</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --barrel:   #121417;
            --barrel-2: #1E2126;
            --coating:  #E3A930;
            --coating-2:#8C7CE8;
            --paper:    #F5F6F8;
            --surface:  #FFFFFF;
            --line:     #E5E7EB;
            --line-2:   #EEF0F2;
            --ink:      #14161A;
            --muted:    #6B7280;
            --muted-2:  #9CA3AF;
            --ok:       #12925F;
            --ok-bg:    #E5F6EE;
            --danger:   #D6362A;
            --danger-bg:#FBEAE8;
            --warn:     #A9720C;
            --warn-bg:  #FDF3DE;
            --info:     #205EA6;
            --info-bg:  #E8F1FC;
            --radius-lg: 18px;
            --radius-md: 12px;
            --radius-sm: 9px;
            --shadow-xs: 0 1px 2px rgba(20,22,26,.05);
            --shadow-sm: 0 6px 18px rgba(20,22,26,.08);
            --shadow-md: 0 16px 36px rgba(20,22,26,.12);
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--paper);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3 { font-family: 'Space Grotesk', sans-serif; letter-spacing: -.015em; }

        .topnav {
            position: sticky; top: 0; z-index: 50;
            background: linear-gradient(180deg, var(--barrel), var(--barrel-2));
            color: #E7E9EC;
            padding: 14px 0;
            box-shadow: 0 8px 24px rgba(0,0,0,.18);
        }
        .topnav a { color: #C6CAD0; text-decoration: none; font-weight: 500; transition: color .15s ease; }
        .topnav a:hover { color: #fff; }

        .brand-mark {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: radial-gradient(circle at 32% 28%, var(--coating) 0 22%, var(--coating-2) 23% 46%, #101216 47% 100%);
            box-shadow: 0 0 0 2px rgba(255,255,255,.1), 0 4px 10px rgba(0,0,0,.35);
        }

        .brand-name {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            color: #fff;
            font-size: 17px;
        }

        .role-chip {
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid #33383F;
            background: rgba(255,255,255,.04);
            color: #9BA2AA;
        }

        /* Nút giỏ hàng trên thanh điều hướng */
        .cart-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border: 1px solid #33383F;
            border-radius: var(--radius-sm);
            padding: 6px 13px;
            transition: border-color .15s ease, background .15s ease;
        }
        .cart-link:hover { border-color: #565D66; background: rgba(255,255,255,.04); }

        .cart-badge {
            min-width: 20px; height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: var(--coating);
            color: #16181C;
            font-size: 12px;
            font-weight: 700;
            display: grid;
            place-items: center;
            font-variant-numeric: tabular-nums;
        }

        /* Hero / khối nổi bật đầu trang */
        .shop-hero {
            background: linear-gradient(135deg, var(--barrel) 0%, #262A33 55%, var(--barrel) 100%);
            border-radius: var(--radius-lg);
            padding: 30px 32px;
            color: #fff;
            margin-bottom: 26px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        .shop-hero::after {
            content: "";
            position: absolute; inset: 0;
            background: radial-gradient(480px circle at 88% 15%, rgba(227,169,48,.22), transparent 60%),
                        radial-gradient(360px circle at 100% 100%, rgba(140,124,232,.18), transparent 60%);
            pointer-events: none;
        }
        .shop-hero h1 { color: #fff; position: relative; }
        .shop-hero .page-sub-light { color: #B9BEC6; position: relative; }
        .shop-hero .filter-form { position: relative; }
        .shop-hero .form-control, .shop-hero .form-select {
            background: rgba(255,255,255,.06);
            border-color: rgba(255,255,255,.14);
            color: #fff;
        }
        .shop-hero .form-control::placeholder { color: #9BA2AA; }
        .shop-hero .form-control:focus, .shop-hero .form-select:focus {
            background: rgba(255,255,255,.1);
            border-color: var(--coating);
        }
        .shop-hero select.form-select option { color: #14161A; }

        /* Thẻ sản phẩm */
        .lens-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-xs);
            transition: border-color .2s ease, transform .2s ease, box-shadow .2s ease;
        }
        .lens-card:hover { border-color: transparent; transform: translateY(-5px); box-shadow: var(--shadow-md); }

        .lens-card img { width: 100%; height: 190px; object-fit: cover; background: #F6F7F8; transition: transform .45s ease; display: block; }
        .lens-card:hover img { transform: scale(1.06); }

        .lens-card .no-img {
            height: 190px;
            display: grid;
            place-items: center;
            color: #A6ACB5;
            background: #F6F7F8;
            font-size: 13px;
        }

        .lens-body { padding: 15px 16px 17px; display: flex; flex-direction: column; gap: 6px; flex: 1; }

        .tag {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: #F7F8F9;
            color: #3F454D;
            width: fit-content;
        }

        .price {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 19px;
            font-variant-numeric: tabular-nums;
            margin-top: auto;
            color: var(--ink);
        }

        /* Sao đánh giá sản phẩm */
        .stars { font-size: 14px; letter-spacing: 1px; }
        .star-filled { color: var(--coating); }
        .star-empty { color: #D8DBE0; }

        .best-seller-badge {
            position: absolute;
            top: 12px; left: 12px;
            background: linear-gradient(135deg, var(--coating), #C98D18);
            color: #16181C;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 11px;
            border-radius: 999px;
            z-index: 2;
            box-shadow: 0 4px 10px rgba(0,0,0,.18);
        }

        .btn { border-radius: var(--radius-sm); font-weight: 600; transition: all .15s ease; }
        .btn-ink { background: var(--barrel); border-color: var(--barrel); color: #fff; box-shadow: var(--shadow-xs); }
        .btn-ink:hover { background: #000; border-color: #000; color: #fff; transform: translateY(-1px); box-shadow: var(--shadow-sm); }
        .btn-ink:disabled { opacity: .5; transform: none; box-shadow: none; }
        .btn-line { background: #fff; border: 1px solid var(--line); color: #3F454D; }
        .btn-line:hover { border-color: #C7CBD1; color: var(--ink); background: #FAFAFB; }
        .btn-ghost { background: transparent; border: 1px solid #33383F; color: #C6CAD0; }
        .btn-ghost:hover { border-color: #565D66; color: #fff; background: rgba(255,255,255,.04); }

        .alert { border-radius: var(--radius-md); border: 1px solid transparent; border-left-width: 4px; padding: 13px 16px; }
        .alert-success { background: var(--ok-bg); border-color: #BFE3D4; border-left-color: var(--ok); color: var(--ok); }
        .alert-danger  { background: var(--danger-bg); border-color: #EFC7C1; border-left-color: var(--danger); color: var(--danger); }
        .alert-warn    { background: var(--warn-bg); border-color: #EBD6A2; border-left-color: var(--warn); color: var(--warn); }
        .alert-warning { background: var(--warn-bg); border-color: #EBD6A2; border-left-color: var(--warn); color: var(--warn); }
        .alert-info    { background: var(--info-bg); border-color: #C3D8F0; border-left-color: var(--info); color: var(--info); }

        .form-control, .form-select { border-radius: var(--radius-sm); border-color: var(--line); padding: 9px 13px; }
        .form-control:focus, .form-select:focus {
            border-color: var(--coating);
            box-shadow: 0 0 0 4px rgba(227,169,48,.15);
        }

        .page-link { color: #3F454D; border-radius: 8px; margin: 0 2px; border-color: var(--line); }
        .page-item.active .page-link { background: var(--barrel); border-color: var(--barrel); }

        ::selection { background: rgba(227,169,48,.35); }

        /* Thông báo nổi khi thêm vào giỏ */
        .toast-box {
            position: fixed;
            right: 20px; bottom: 90px;
            background: var(--barrel);
            color: #fff;
            padding: 13px 18px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
            font-size: 14px;
            opacity: 0;
            transform: translateY(12px);
            transition: opacity .2s ease, transform .2s ease;
            pointer-events: none;
            z-index: 60;
            max-width: 320px;
        }
        .toast-box.show { opacity: 1; transform: translateY(0); }

        /* Footer */
        .site-footer {
            border-top: 1px solid var(--line);
            margin-top: 50px;
            padding: 26px 0;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
            background: var(--surface);
        }

        /* ===== Popup chat hỗ trợ ===== */
        #chat-box { position: fixed; right: 20px; bottom: 20px; z-index: 70; }

        #chat-toggle {
            width: 56px; height: 56px;
            border-radius: 50%;
            border: 0;
            background: var(--barrel);
            color: #fff;
            font-size: 22px;
            cursor: pointer;
            box-shadow: var(--shadow-md);
            position: relative;
            transition: transform .15s ease, background .15s ease;
        }
        #chat-toggle:hover { background: #000; transform: translateY(-2px); }

        .chat-unread {
            position: absolute;
            top: -2px; right: -2px;
            min-width: 20px; height: 20px;
            padding: 0 5px;
            border-radius: 999px;
            background: var(--danger);
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: grid;
            place-items: center;
            border: 2px solid #fff;
        }

        #chat-popup {
            display: none;
            width: 340px;
            height: 460px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            flex-direction: column;
        }
        #chat-popup.open { display: flex; }

        .chat-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            background: var(--barrel);
            color: #fff;
        }

        .chat-head-sub { font-size: 12px; color: #9BA2AA; }

        #chat-close {
            background: none;
            border: 0;
            color: #C6CAD0;
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            padding: 0 4px;
        }
        #chat-close:hover { color: #fff; }

        #chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 14px;
            background: #F7F8F9;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-empty { text-align: center; color: var(--muted); font-size: 13px; margin: auto; }

        .msg {
            max-width: 78%;
            padding: 9px 13px;
            border-radius: 14px;
            font-size: 14px;
            line-height: 1.45;
            word-break: break-word;
        }

        .msg-me {
            align-self: flex-end;
            background: var(--barrel);
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .msg-them {
            align-self: flex-start;
            background: #fff;
            border: 1px solid var(--line);
            border-bottom-left-radius: 4px;
        }

        .msg-time { font-size: 11px; opacity: .65; margin-top: 3px; }

        .chat-foot {
            display: flex;
            gap: 8px;
            padding: 12px;
            border-top: 1px solid var(--line);
            background: #fff;
        }

        .chat-foot input {
            flex: 1;
            border: 1px solid var(--line);
            border-radius: 9px;
            padding: 9px 12px;
            font-size: 14px;
            outline: none;
        }
        .chat-foot input:focus { border-color: var(--coating); }

        .chat-foot button {
            border: 0;
            border-radius: 9px;
            padding: 0 16px;
            background: var(--barrel);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }
        .chat-foot button:disabled { opacity: .5; cursor: not-allowed; }

        @media (max-width: 420px) {
            #chat-popup { width: calc(100vw - 40px); height: 70vh; }
        }

        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>

    @stack('styles')
</head>
<body>

<header class="topnav">
    <div class="container d-flex align-items-center gap-3 flex-wrap">
        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2">
            <span class="brand-mark"></span>
            <span class="brand-name">Lens Store</span>
        </a>

        <a href="{{ route('blog.index') }}">Blog</a>

        <div class="ms-auto d-flex align-items-center gap-3 flex-wrap">
            @guest
                <a href="{{ route('login') }}">Đăng nhập</a>
                <a href="{{ route('register') }}" class="btn btn-ghost btn-sm px-3">Đăng ký</a>
            @else
                <span class="role-chip">{{ Auth::user()->role_label }}</span>
                <a href="{{ route('profile.edit') }}" style="color:#9BA2AA">{{ Auth::user()->name }}</a>

                <a href="{{ route('order.index') }}">Đơn hàng</a>
                <a href="{{ route('loyalty.index') }}">Điểm thân thiết</a>

                <a href="{{ route('cart.index') }}" class="cart-link">
                    Giỏ hàng
                    <span class="cart-badge" id="cartCount">{{ array_sum(array_column(session('cart', []), 'quantity')) }}</span>
                </a>

                @if (Auth::user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-ghost btn-sm px-3">Trang quản trị</a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-ghost btn-sm px-3">Đăng xuất</button>
                </form>
            @endguest
        </div>
    </div>
</header>

<main class="container py-4">
    @auth
        @if (! Auth::user()->hasVerifiedEmail())
            <div class="alert alert-warn d-flex align-items-center gap-3 flex-wrap">
                <span>Tài khoản của bạn chưa xác thực email. Một số chức năng sẽ bị hạn chế.</span>
                <a href="{{ route('verification.notice') }}" class="btn btn-line btn-sm ms-auto">Xác thực ngay</a>
            </div>
        @endif
    @endauth

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warn">{{ session('warning') }}</div>
    @endif
    @if (session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @yield('content')
</main>

<footer class="site-footer">
    <div class="container">
        @php $footerPages = \App\Models\Page::where('is_published', true)->orderBy('title')->get(); @endphp
        @if ($footerPages->count())
            <div class="d-flex justify-content-center gap-3 flex-wrap mb-2" style="font-size:13px">
                @foreach ($footerPages as $footerPage)
                    <a href="{{ route('pages.show', $footerPage->slug) }}" class="text-muted">{{ $footerPage->title }}</a>
                @endforeach
                <a href="{{ route('blog.index') }}" class="text-muted">Blog</a>
            </div>
        @endif
        © {{ date('Y') }} <strong style="color:var(--ink)">Lens Store</strong> — Ống kính máy ảnh chính hãng, bảo hành toàn quốc.
    </div>
</footer>

<div class="toast-box" id="toastBox"></div>

{{-- ===== Popup chat hỗ trợ khách hàng ===== --}}
@auth
<div id="chat-box">
    <button id="chat-toggle" type="button" title="Hỗ trợ khách hàng">
        <span>💬</span>
        <span id="chat-unread" class="chat-unread" style="display:none">0</span>
    </button>

    <div id="chat-popup">
        <div class="chat-head">
            <div>
                <strong>Hỗ trợ khách hàng</strong>
                <div class="chat-head-sub">Thường trả lời trong vài phút</div>
            </div>
            <button id="chat-close" type="button">×</button>
        </div>

        <div id="chat-messages">
            <div class="chat-empty">Đang tải lịch sử...</div>
        </div>

        <div class="chat-foot">
            <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." autocomplete="off" maxlength="2000">
            <button id="send-btn" type="button">Gửi</button>
        </div>
    </div>
</div>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Hiện thông báo nổi trong 2,5 giây
    function showToast(message) {
        const box = document.getElementById('toastBox');
        box.textContent = message;
        box.classList.add('show');
        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(() => box.classList.remove('show'), 2500);
    }

    // Cập nhật con số trên huy hiệu giỏ hàng
    function setCartCount(n) {
        const badge = document.getElementById('cartCount');
        if (badge) badge.textContent = n;
    }
</script>

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
    const unread   = document.getElementById("chat-unread");
    const myId     = "{{ Auth::id() }}";
    const csrf     = document.querySelector('meta[name="csrf-token"]').content;

    let lastCount = 0;

    // Chong XSS: khong chen thang noi dung tin nhan vao innerHTML
    const esc = s => { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; };

    const fmtTime = iso => {
        const d = new Date(iso);
        return d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0');
    };

    toggleBtn.onclick = () => {
        popup.classList.add("open");
        toggleBtn.style.display = "none";
        loadMessages();
        input.focus();
    };

    closeBtn.onclick = () => {
        popup.classList.remove("open");
        toggleBtn.style.display = "block";
    };

    function loadMessages() {
        fetch("{{ route('chat.messages') }}")
            .then(r => r.json())
            .then(messages => {
                if (messages.length === lastCount) return;   // khong ve lai neu khong doi
                lastCount = messages.length;

                if (messages.length === 0) {
                    box.innerHTML = '<div class="chat-empty">Bắt đầu cuộc trò chuyện với chúng tôi</div>';
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
                unread.style.display = "none";
            })
            .catch(err => console.error("Lỗi tải tin nhắn:", err));
    }

    function checkUnread() {
        fetch("{{ route('chat.unread') }}")
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
        if (!message) return;

        input.disabled = sendBtn.disabled = true;

        fetch("{{ route('chat.send') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrf,
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({ message })
        })
        .then(r => r.json())
        .then(() => {
            input.value = "";
            lastCount = -1;      // ep ve lai khung chat
            loadMessages();
        })
        .catch(err => console.error("Lỗi gửi tin:", err))
        .finally(() => {
            input.disabled = sendBtn.disabled = false;
            input.focus();
        });
    }

    sendBtn.onclick = sendMessage;
    input.addEventListener("keypress", e => { if (e.key === "Enter") sendMessage(); });

    // Cap nhat lien tuc moi 3 giay
    setInterval(() => {
        popup.classList.contains("open") ? loadMessages() : checkUnread();
    }, 3000);

    checkUnread();
});
</script>
@endauth

@stack('scripts')
</body>
</html>