<?php
// app/Http/Controllers/ProfileController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /** Hiển thị trang hồ sơ cá nhân. */
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /** Cập nhật họ tên / email. Đổi email thì phải xác thực lại. */
    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [], ['name' => 'họ tên', 'email' => 'email']);

        $emailChanged = $data['email'] !== $user->email;

        $user->fill($data);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice')
                ->with('success', 'Cập nhật thông tin thành công. Vui lòng xác thực lại email mới.');
        }

        return back()->with('success', 'Cập nhật thông tin thành công.');
    }

    /** Đổi mật khẩu — yêu cầu nhập đúng mật khẩu hiện tại. */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ], [], ['current_password' => 'mật khẩu hiện tại', 'password' => 'mật khẩu mới']);

        Auth::user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Đổi mật khẩu thành công.');
    }
}
