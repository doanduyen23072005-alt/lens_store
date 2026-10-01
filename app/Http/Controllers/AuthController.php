<?php
// app/Http/Controllers/AuthController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /** Hiển thị form đăng ký */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /** Xử lý đăng ký tài khoản mới */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'name'     => 'họ tên',
            'email'    => 'email',
            'password' => 'mật khẩu',
        ]);

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'customer',   // mặc định là khách hàng
            ]);

            // Gửi email chứa link xác thực
            $user->sendEmailVerificationNotification();

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('verification.notice')
                ->with('success', 'Đăng ký thành công. Vui lòng kiểm tra hộp thư để xác thực email.');
        } catch (\Exception $e) {
            Log::error('Đăng ký thất bại: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Không tạo được tài khoản. Vui lòng thử lại.');
        }
    }

    /** Hiển thị form đăng nhập */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /** Xử lý đăng nhập */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email'    => 'email',
            'password' => 'mật khẩu',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Chưa xác thực email thì đưa về trang thông báo
            if (! Auth::user()->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withInput($request->only('email'))
            ->with('error', 'Email hoặc mật khẩu không đúng.');
    }

    /** Đăng xuất */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Bạn đã đăng xuất.');
    }
}