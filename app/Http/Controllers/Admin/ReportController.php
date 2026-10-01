<?php
// app/Http/Controllers/Admin/ReportController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Đơn được tính vào doanh thu: MoMo đã thanh toán (status = paid),
     * hoặc COD đã giao thành công (tiền COD chỉ thực thu khi giao xong).
     * Loại đơn đã hủy.
     */
    private function paidOrders(): Builder
    {
        return Order::query()
            ->where('created_at', '<=', now())
            ->where('status', '!=', 'cancelled')
            ->where(function (Builder $query) {
                $query->where('status', 'paid')
                    ->orWhere(function (Builder $cod) {
                        $cod->where('payment_method', 'cod')->where('shipping_status', 'delivered');
                    });
            });
    }

    private function categoryRevenue(): Collection
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('order_items.order_id', $this->paidOrders()->select('orders.id'))
            ->select('products.category_id', 'categories.name as category_name')
            ->selectRaw('SUM(order_items.price * order_items.quantity) as total_revenue, SUM(order_items.quantity) as total_qty')
            ->groupBy('products.category_id', 'categories.name')
            ->orderByDesc('total_revenue')->get();
    }

    private function bestSellingProducts(int $limit = 10): Collection
    {
        return DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereIn('order_items.order_id', $this->paidOrders()->select('orders.id'))
            ->select('products.id', 'products.name')
            ->selectRaw('SUM(order_items.quantity) as total_qty, SUM(order_items.price * order_items.quantity) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();
    }

    private function dailyRevenue(): Collection
    {
        return $this->paidOrders()
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total_revenue, COUNT(*) as order_count')
            ->groupByRaw('DATE(created_at)')->orderBy('date')->get();
    }

    /** Tổng hợp từ dữ liệu theo ngày, dùng được với cả MySQL và SQLite. */
    private function periodRevenue(Collection $days, string $period): Collection
    {
        return $days->groupBy(fn ($day) => substr($day->date, 0, $period === 'month' ? 7 : 4))
            ->map(fn (Collection $rows, $key) => (object) [
                $period         => (string) $key,
                'total_revenue' => $rows->sum('total_revenue'),
                'order_count'   => $rows->sum('order_count'),
            ])->values();
    }

    public function index()
    {
        $categoryRevenue    = $this->categoryRevenue();
        $bestSellingProducts = $this->bestSellingProducts();
        $totalOrders        = Order::count();
        $totalCustomers     = DB::table('users')->where('role', 'customer')->count();
        $revenueByDate      = $this->dailyRevenue();
        $revenueByMonth     = $this->periodRevenue($revenueByDate, 'month');
        $revenueByYear      = $this->periodRevenue($revenueByDate, 'year');
        $totalRevenue       = $revenueByDate->sum('total_revenue');

        return view('admin.reports.index', compact(
            'categoryRevenue', 'bestSellingProducts', 'totalOrders', 'totalCustomers', 'totalRevenue',
            'revenueByDate', 'revenueByMonth', 'revenueByYear'
        ));
    }

    public function charts()
    {
        $categories = $this->categoryRevenue();
        $catLabels  = $categories->map(fn ($row) => $row->category_name ?? 'Chưa phân loại')->all();
        $catRevenue = $categories->pluck('total_revenue')->map(fn ($value) => (float) $value)->all();

        $daily   = $this->dailyRevenue();
        $byDate  = $daily->keyBy('date');
        $byMonth = $this->periodRevenue($daily, 'month')->keyBy('month');
        $byYear  = $this->periodRevenue($daily, 'year');

        $startDay   = Carbon::now()->startOfDay()->subDays(29);
        $startMonth = Carbon::now()->startOfMonth()->subMonths(11);

        $revDateLabels = $revDateData = $revMonthLabels = $revMonthData = [];

        for ($i = 0; $i < 30; $i++) {
            $date            = $startDay->copy()->addDays($i)->toDateString();
            $revDateLabels[] = $date;
            $revDateData[]   = (float) ($byDate->get($date)?->total_revenue ?? 0);
        }

        for ($i = 0; $i < 12; $i++) {
            $month            = $startMonth->copy()->addMonths($i);
            $revMonthLabels[] = $month->format('m/Y');
            $revMonthData[]   = (float) ($byMonth->get($month->format('Y-m'))?->total_revenue ?? 0);
        }

        $revYearLabels = $byYear->pluck('year')->all();
        $revYearData   = $byYear->pluck('total_revenue')->map(fn ($value) => (float) $value)->all();

        $methodRevenue = $this->paidOrders()
            ->select('payment_method')->selectRaw('SUM(total_price) as revenue')
            ->groupBy('payment_method')->pluck('revenue', 'payment_method');

        $paymentMethodLabels  = ['MoMo', 'COD'];
        $paymentMethodRevenue = [(float) $methodRevenue->get('momo', 0), (float) $methodRevenue->get('cod', 0)];

        $bestSellers      = $this->bestSellingProducts(8);
        $bestSellerLabels = $bestSellers->pluck('name')->all();
        $bestSellerQty    = $bestSellers->pluck('total_qty')->map(fn ($v) => (int) $v)->all();

        return view('admin.reports.charts', compact(
            'catLabels', 'catRevenue', 'revDateLabels', 'revDateData',
            'revMonthLabels', 'revMonthData', 'revYearLabels', 'revYearData',
            'paymentMethodLabels', 'paymentMethodRevenue',
            'bestSellerLabels', 'bestSellerQty'
        ));
    }
}
