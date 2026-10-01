<?php
// routes/web.php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MomoController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\Admin\AdminChatController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\MarketingController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Trang công khai — ai cũng xem được
|--------------------------------------------------------------------------
*/
Route::get('/', [ShopController::class, 'index'])->name('home');
Route::get('/lens/{product}', [ShopController::class, 'show'])->name('shop.show');

/*
|--------------------------------------------------------------------------
| CMS: Trang tĩnh & Blog/Tin tức — công khai, không cần đăng nhập
|--------------------------------------------------------------------------
*/
Route::get('/tin-tuc', [BlogController::class, 'index'])->name('blog.index');
Route::get('/tin-tuc/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/trang/{page:slug}', [PageController::class, 'show'])->name('pages.show');

/*
|--------------------------------------------------------------------------
| Xác thực — chỉ khách chưa đăng nhập mới vào được form
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Xác thực email
|--------------------------------------------------------------------------
*/
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('home')->with('success', 'Xác thực email thành công.');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('success', 'Đã gửi lại email xác thực.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

/*
|--------------------------------------------------------------------------
| Callback & IPN từ bên thứ ba (MoMo, GHN)
|--------------------------------------------------------------------------
| KHÔNG đặt trong middleware auth vì MoMo/GHN gọi sang tự động,
| không mang theo session đăng nhập của khách.
| Route ipn đã được bypass CSRF trong bootstrap/app.php.
*/
Route::get('/payment/momo/callback', [MomoController::class, 'callback'])->name('momo.callback');
Route::post('/payment/momo/ipn',     [MomoController::class, 'ipn'])->name('momo.ipn');

/*
|--------------------------------------------------------------------------
| GHN — địa chỉ & phí vận chuyển (AJAX)
|--------------------------------------------------------------------------
| Để ngoài middleware auth cho dễ test bằng trình duyệt.
| Khi chạy thật nên đưa vào nhóm auth bên dưới.
*/
Route::prefix('locations')->name('locations.')->group(function () {
    Route::get('/provinces',              [OrderController::class, 'getProvinces'])->name('provinces');
    Route::get('/districts/{provinceId}', [OrderController::class, 'getDistricts'])->name('districts');
    Route::get('/wards/{districtId}',     [OrderController::class, 'getWards'])->name('wards');
    Route::post('/calculate-fee',         [OrderController::class, 'getShippingFee'])->name('fee');
});

/*
|--------------------------------------------------------------------------
| Giỏ hàng, đơn hàng & thanh toán — bắt buộc đăng nhập
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // --- Giỏ hàng ---
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    // Mở trang thanh toán (nhận danh sách món đã tick khi POST từ giỏ hàng;
    // cho phép GET để có thể quay lại trang này sau khi áp/bỏ mã giảm giá)
    Route::match(['get', 'post'], '/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // --- Đặt hàng ---
    Route::post('/dat-hang', [OrderController::class, 'store'])->name('order.store');

    // --- Đơn hàng ---
    Route::get('/orders', [OrderController::class, 'index'])->name('order.index');

    // Các route con của đơn phải khai TRƯỚC /orders/{order}
    Route::get('/orders/{order}/momo/start',     [MomoController::class, 'start'])->name('momo.start');
    Route::get('/orders/{order}/momo/pay-again', [MomoController::class, 'payAgain'])->name('momo.pay_again');
    Route::post('/orders/{order}/cancel',        [OrderController::class, 'cancel'])->name('order.cancel');
    Route::post('/orders/{order}/return',        [OrderController::class, 'requestReturn'])->name('order.return');

    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('order.show');
        // --- Chat khách hàng ---
    Route::post('/chat/send',     [ChatController::class, 'send'])->name('chat.send');
    Route::get('/chat/messages',  [ChatController::class, 'getMessages'])->name('chat.messages');
    Route::get('/chat/unread',    [ChatController::class, 'unreadCount'])->name('chat.unread');

    // --- Hồ sơ cá nhân ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // --- Đánh giá sản phẩm (chỉ khách đã mua và nhận hàng thành công) ---
    Route::post('/lens/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // --- Mã giảm giá ---
    Route::post('/cart/coupon', [CouponController::class, 'apply'])->name('coupon.apply');
    Route::delete('/cart/coupon', [CouponController::class, 'remove'])->name('coupon.remove');

    // --- Khách hàng thân thiết ---
    Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
});

/*
|--------------------------------------------------------------------------
| Khu vực quản trị — đăng nhập + đã xác thực email + vai trò admin
|--------------------------------------------------------------------------
| Đường dẫn có tiền tố /admin nhưng TÊN route giữ nguyên (products.index,
| categories.index...) nên toàn bộ view CRUD ở lab 2 không phải sửa.
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);

    // --- Chat hỗ trợ ---
    Route::get('chat/users',             [AdminChatController::class, 'getUsers'])->name('admin.chat.users');
    Route::get('chat/messages/{userId}', [AdminChatController::class, 'getMessages'])->name('admin.chat.messages');
    Route::post('chat/send',             [AdminChatController::class, 'send'])->name('admin.chat.send');
    Route::get('chat/unread',            [AdminChatController::class, 'unreadCount'])->name('admin.chat.unread');
});

/*
|--------------------------------------------------------------------------
| Khu vực quản trị (tiếp) — Đơn hàng, Người dùng, Báo cáo (Lab 08)
|--------------------------------------------------------------------------
| Nhóm riêng có name('admin.') để route ra tên dạng admin.orders.index,
| admin.users.index, admin.reports.index như lab yêu cầu.
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // --- Đơn hàng: admin chỉ xem danh sách/chi tiết, đổi trạng thái, hủy — không tự tạo đơn ---
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');

    // --- Tài chính: thống kê & giao dịch thanh toán ---
    Route::get('finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('finance/transactions', [FinanceController::class, 'transactions'])->name('finance.transactions');
    Route::patch('finance/{order}/status', [FinanceController::class, 'updateStatus'])->name('finance.update-status');

    // --- Người dùng ---
    Route::resource('users', AdminUserController::class);

    // --- Đánh giá sản phẩm ---
    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // --- Mã giảm giá ---
    Route::resource('coupons', AdminCouponController::class)->except(['show']);

    // --- Marketing / thông báo email ---
    Route::get('marketing', [MarketingController::class, 'compose'])->name('marketing.compose');
    Route::post('marketing', [MarketingController::class, 'send'])->name('marketing.send');

    // --- Báo cáo ---
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/charts', [ReportController::class, 'charts'])->name('reports.charts');

    // --- CMS: Trang tĩnh & Blog/Tin tức ---
    Route::resource('pages', AdminPageController::class)->except(['show']);
    Route::resource('posts', AdminPostController::class)->except(['show']);
});