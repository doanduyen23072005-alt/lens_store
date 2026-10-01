<?php
// app/Http/Controllers/Admin/UserController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** Hiển thị danh sách người dùng. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role'   => ['nullable', Rule::in(['admin', 'customer'])],
        ]);

        $users = User::withCount('orders')
            ->when($request->filled('search'), function ($query) use ($filters) {
                $search = trim($filters['search']);
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $filters['role']))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    /** Hiển thị form tạo người dùng mới. */
    public function create()
    {
        return view('admin.users.create');
    }

    /** Lưu người dùng mới vào CSDL. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['required', Rule::in(['admin', 'customer'])],
        ], [], [
            'name' => 'họ tên', 'email' => 'email', 'password' => 'mật khẩu', 'role' => 'vai trò',
        ]);

        User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'role'              => $data['role'],
            // Admin tạo tài khoản thay người dùng nên bỏ qua bước xác thực email.
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Thêm người dùng thành công.');
    }

    /** Hiển thị chi tiết người dùng. */
    public function show(User $user)
    {
        $user->loadCount('orders');

        return view('admin.users.show', compact('user'));
    }

    /** Hiển thị form chỉnh sửa người dùng. */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /** Cập nhật thông tin người dùng. */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'  => ['required', Rule::in(['admin', 'customer'])],
        ], [], [
            'name' => 'họ tên', 'email' => 'email', 'role' => 'vai trò',
        ]);

        if ($user->id === Auth::id() && $data['role'] !== 'admin') {
            return back()->withInput()->with('error', 'Không thể tự hạ quyền tài khoản đang đăng nhập.');
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật người dùng thành công.');
    }

    /** Xóa người dùng. */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Không thể xóa tài khoản đang đăng nhập.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return back()->with('error', 'Phải còn ít nhất một quản trị viên trong hệ thống.');
        }

        // orders.user_id có ràng buộc cascade — xóa thẳng sẽ xóa luôn lịch sử đơn hàng của khách.
        if ($user->orders()->exists()) {
            return back()->with('error', 'Không thể xóa: người dùng này đã có đơn hàng trong hệ thống.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Xóa người dùng thành công.');
    }
}
