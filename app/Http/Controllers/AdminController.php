<?php
// app/Http/Controllers/AdminController.php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class AdminController extends Controller
{
    /** Trang tổng quan của khu vực quản trị */
    public function dashboard()
    {
        $stats = [
            'products'   => Product::count(),
            'categories' => Category::count(),
            'customers'  => User::where('role', 'customer')->count(),
            'inventory'  => (int) Product::sum('quantity'),
            'value'      => Product::selectRaw('SUM(price * quantity) as total')->value('total') ?? 0,
            'out_stock'  => Product::where('quantity', '<=', 0)->count(),
        ];

        $lowStock = Product::with('category')
            ->where('quantity', '<', 5)
            ->orderBy('quantity')
            ->take(5)
            ->get();

        $latest = Product::with('category')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'lowStock', 'latest'));
    }
}