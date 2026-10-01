<?php
// app/Http/Middleware/AdminMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Chưa đăng nhập thì đưa về trang đăng nhập
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        // Đã đăng nhập nhưng không phải admin
        if (! Auth::user()->isAdmin()) {
            return redirect()->route('home')
                ->with('error', 'Khu vực này chỉ dành cho quản trị viên.');
        }

        return $next($request);
    }
}