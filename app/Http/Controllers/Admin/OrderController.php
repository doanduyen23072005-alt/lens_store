<?php
// app/Http/Controllers/Admin/OrderController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\GHNOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /** Nhãn trạng thái thanh toán/đơn hàng (khớp cột orders.status thực tế trong DB). */
    private const STATUS_LABELS = [
        'pending'        => 'Chờ thanh toán',
        'paid'           => 'Đã thanh toán (MoMo)',
        'cod_ordered'    => 'COD - đã đặt hàng',
        'cod_paid'       => 'COD - đã thu tiền',
        'failed'         => 'Thanh toán thất bại',
        'cancelled'      => 'Đã hủy',
        'refund_pending' => 'Chờ hoàn tiền',
        'refunded'       => 'Đã hoàn tiền',
    ];

    /** Nhãn trạng thái vận chuyển GHN (khớp Order::getShippingLabelAttribute). */
    private const SHIPPING_LABELS = [
        'not_shipped'   => 'Chưa gửi hàng',
        'processing'    => 'Đang tạo vận đơn',
        'ready_to_pick' => 'Chờ lấy hàng',
        'picking'       => 'Đang lấy hàng',
        'picked'        => 'Đã lấy hàng',
        'storing'       => 'Đang ở kho',
        'transporting'  => 'Đang trung chuyển',
        'sorting'       => 'Đang phân loại',
        'delivering'    => 'Đang giao hàng',
        'delivered'     => 'Giao thành công',
        'delivery_fail' => 'Giao không thành công',
        'return'        => 'Đang hoàn hàng',
        'returned'      => 'Đã hoàn hàng',
        'cancel'        => 'Đã hủy vận đơn',
    ];

    /**
     * Nhóm trạng thái thành các tab lọc trên danh sách đơn hàng.
     * 'field' cho biết tab lọc theo cột nào: 'shipping_status' (vận chuyển, mặc định) hoặc 'status' (thanh toán).
     * Hai chiều trạng thái này độc lập nhau — một đơn có thể "Giao thành công" nhưng vẫn "Chờ hoàn tiền".
     */
    private const TABS = [
        'all'        => ['label' => 'Tất cả',            'badge' => 'primary',   'field' => 'shipping_status', 'statuses' => []],
        'pending'    => ['label' => 'Chờ xử lý',          'badge' => 'secondary', 'field' => 'shipping_status', 'statuses' => ['not_shipped', 'processing']],
        'ready'      => ['label' => 'Chờ lấy hàng',       'badge' => 'info',      'field' => 'shipping_status', 'statuses' => ['ready_to_pick']],
        'picking'    => ['label' => 'Đang lấy hàng',      'badge' => 'info',      'field' => 'shipping_status', 'statuses' => ['picking']],
        'delivering' => ['label' => 'Đang giao',          'badge' => 'warning',   'field' => 'shipping_status', 'statuses' => ['picked', 'storing', 'transporting', 'sorting', 'delivering']],
        'delivered'  => ['label' => 'Thành công',         'badge' => 'success',   'field' => 'shipping_status', 'statuses' => ['delivered']],
        'return'     => ['label' => 'Hoàn / Thất bại',    'badge' => 'dark',      'field' => 'shipping_status', 'statuses' => ['return', 'returned', 'delivery_fail']],
        'refund'     => ['label' => 'Hoàn tiền',          'badge' => 'dark',      'field' => 'status',          'statuses' => ['refund_pending', 'refunded']],
        'cancelled'  => ['label' => 'Đã hủy',             'badge' => 'danger',    'field' => 'shipping_status', 'statuses' => ['cancel']],
    ];

    /** Trình tự tiến độ vận chuyển bình thường — dùng để gợi ý bước kế tiếp cho admin. */
    private const SHIPPING_FLOW = [
        'not_shipped', 'processing', 'ready_to_pick', 'picking', 'picked',
        'storing', 'transporting', 'sorting', 'delivering', 'delivered',
    ];

    /** Trạng thái cuối cùng: không thể đổi tiếp hoặc hủy. 'delivered' KHÔNG nằm ở đây vì đơn đã giao vẫn có thể chuyển sang 'return' khi khách yêu cầu hoàn hàng. */
    private const TERMINAL_STATUSES = ['cancel', 'returned'];

    /** Hiển thị danh sách đơn hàng kèm bộ lọc, tab trạng thái. */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search'           => ['nullable', 'string', 'max:100'],
            'status'           => ['nullable', Rule::in(array_keys(self::STATUS_LABELS))],
            'payment_method'   => ['nullable', Rule::in(['cod', 'momo'])],
            'shipping_status'  => ['nullable', Rule::in(array_keys(self::SHIPPING_LABELS))],
            'tab'              => ['nullable', Rule::in(array_keys(self::TABS))],
            'date_from'        => ['nullable', 'date_format:Y-m-d'],
            'date_to'          => ['nullable', 'date_format:Y-m-d', ...($request->filled('date_from') ? ['after_or_equal:date_from'] : [])],
            'per_page'         => ['nullable', 'integer', Rule::in([25, 50, 100])],
            'sort'             => ['nullable', Rule::in(['newest', 'oldest', 'amount_desc', 'amount_asc'])],
            'page'             => ['nullable', 'integer', 'min:1'],
        ], [
            'date_to.after_or_equal' => 'Ngày kết thúc phải từ ngày bắt đầu trở đi.',
            '*.date_format'          => 'Ngày lọc không hợp lệ.',
            '*.in'                   => 'Giá trị bộ lọc không hợp lệ.',
        ]);

        $query = Order::query()->with(['items.product', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $filters['status']);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if ($request->filled('search')) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('ghn_order_code', 'like', '%'.$search.'%')
                    ->orWhereHas('items.product', fn ($products) => $products->where('name', 'like', '%'.$search.'%'));

                if (preg_match('/^(?:#|DH)?0*(\d+)$/i', $search, $matches)) {
                    $q->orWhere('id', $matches[1]);
                }
            });
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<', Carbon::parse($filters['date_to'])->addDay()->startOfDay());
        }

        // Số trên tab theo bộ lọc chung (trừ tab/status/shipping_status), không bị giới hạn bởi trang hiện tại.
        $shippingCounts = (clone $query)->select('shipping_status')->selectRaw('COUNT(*) as total')
            ->groupBy('shipping_status')->pluck('total', 'shipping_status');
        $paymentCounts = (clone $query)->select('status')->selectRaw('COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        $tabs = collect(self::TABS)->map(function ($tab, $key) use ($shippingCounts, $paymentCounts) {
            $counts = $tab['field'] === 'status' ? $paymentCounts : $shippingCounts;
            $tab['count'] = $key === 'all'
                ? $shippingCounts->sum()
                : collect($tab['statuses'])->sum(fn ($status) => $counts->get($status, 0));

            return $tab;
        });

        $activeTab = $filters['tab'] ?? 'all';

        if ($activeTab !== 'all') {
            $query->whereIn(self::TABS[$activeTab]['field'], self::TABS[$activeTab]['statuses']);
        }

        if ($request->filled('shipping_status')) {
            $query->where('shipping_status', $filters['shipping_status']);
        }

        [$column, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest'      => ['created_at', 'asc'],
            'amount_desc' => ['total_price', 'desc'],
            'amount_asc'  => ['total_price', 'asc'],
            default       => ['created_at', 'desc'],
        };

        $orders = $query->orderBy($column, $direction)->orderBy('id', $direction)
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        return view('admin.orders.index', [
            'orders'         => $orders,
            'filters'        => $filters,
            'tabs'           => $tabs,
            'activeTab'      => $activeTab,
            'statusLabels'   => self::STATUS_LABELS,
            'shippingLabels' => self::SHIPPING_LABELS,
        ]);
    }

    /** Hiển thị chi tiết đơn hàng + hành động chuyển trạng thái / hủy. */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'paymentTransactions' => fn ($q) => $q->latest()]);

        return view('admin.orders.show', [
            'order'          => $order,
            'statusLabels'   => self::STATUS_LABELS,
            'shippingLabels' => self::SHIPPING_LABELS,
            'nextOptions'    => $this->nextShippingOptions($order->shipping_status),
            'isTerminal'     => in_array($order->shipping_status, self::TERMINAL_STATUSES, true),
        ]);
    }

    /** Admin chuyển trạng thái vận chuyển (mô phỏng tiến trình giao hàng). */
    public function updateStatus(Request $request, Order $order)
    {
        if (in_array($order->shipping_status, self::TERMINAL_STATUSES, true)) {
            return back()->with('error', 'Đơn hàng đã ở trạng thái cuối, không thể đổi tiếp.');
        }

        $data = $request->validate([
            'shipping_status' => ['required', Rule::in(array_keys(self::SHIPPING_LABELS))],
        ], [
            'shipping_status.required' => 'Vui lòng chọn trạng thái mới.',
            'shipping_status.in'       => 'Trạng thái không hợp lệ.',
        ]);

        $order->update(['shipping_status' => $data['shipping_status']]);

        if ($data['shipping_status'] === 'delivered') {
            $this->awardLoyaltyPointsFor($order);
        }

        $this->notifyCustomer($order);

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng #'.$order->id.'.');
    }

    /** Admin hủy đơn — chặn nếu đơn đã được lấy hàng trở đi (đang giao). */
    public function cancel(Order $order, GHNOrderService $ghnOrder)
    {
        if (!$order->isCancellable()) {
            return back()->with('error', 'Đơn hàng đã được lấy hàng, không thể hủy.');
        }

        $ghnOrder->cancel($order);

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);
        });

        $this->notifyCustomer($order);

        return back()->with('success', 'Đã hủy đơn hàng #'.$order->id.'.');
    }

    /** Cộng điểm thân thiết khi đơn giao thành công — 1 điểm mỗi 50.000đ, chỉ cộng một lần cho mỗi đơn. */
    private function awardLoyaltyPointsFor(Order $order): void
    {
        if (! $order->user || $order->user->loyaltyTransactions()->where('order_id', $order->id)->exists()) {
            return;
        }

        $points = (int) floor(((float) $order->total_price) / 50000);

        if ($points > 0) {
            $order->user->awardLoyaltyPoints($points, 'Đơn hàng #'.$order->id.' giao thành công', $order);
        }
    }

    /** Báo cho khách qua email khi trạng thái đơn thay đổi — không chặn luồng chính nếu gửi lỗi. */
    private function notifyCustomer(Order $order): void
    {
        if (! $order->user) {
            return;
        }

        try {
            $order->user->notify(new OrderStatusChangedNotification($order));
        } catch (\Throwable $e) {
            Log::warning('Khong gui duoc email cap nhat don hang', ['order_id' => $order->id, 'error' => $e->getMessage()]);
        }
    }

    /** Các trạng thái vận chuyển admin có thể chuyển tới từ trạng thái hiện tại. */
    private function nextShippingOptions(string $current): array
    {
        // Đang hoàn hàng -> admin xác nhận đã nhận lại hàng.
        if ($current === 'return') {
            return ['returned'];
        }

        // Đã giao thành công -> chỉ còn đường duy nhất là ghi nhận yêu cầu hoàn hàng.
        if ($current === 'delivered') {
            return ['return'];
        }

        $flowIndex = array_search($current, self::SHIPPING_FLOW, true);
        $forward   = $flowIndex === false ? [] : array_slice(self::SHIPPING_FLOW, $flowIndex + 1);

        // Từ lúc đã lấy hàng đến trước khi giao thành công mới cho phép đánh dấu giao thất bại / hoàn hàng giữa đường.
        $pickingIndex = array_search('picking', self::SHIPPING_FLOW, true);
        if ($flowIndex !== false && $flowIndex >= $pickingIndex) {
            $forward = array_merge($forward, ['delivery_fail', 'return']);
        }

        return $forward;
    }
}
