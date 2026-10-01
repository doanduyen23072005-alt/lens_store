<?php
// app/Http/Controllers/ProductController.php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, fn ($query, $q) => $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            }))
            ->when($request->category_id, fn ($query, $id) => $query->where('category_id', $id))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        $stats = [
            'total'      => Product::count(),
            'categories' => Category::count(),
            'out_stock'  => Product::where('quantity', '<=', 0)->count(),
            'inventory'  => Product::sum('quantity'),
        ];

        return view('products.index', compact('products', 'categories', 'stats'));
    }

    public function create()
    {
        return view('products.create', [
            'product'    => new Product(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', 'Đã thêm ống kính mới.');
    }

    public function show(Product $product)
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateData($request, $product->id);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', 'Đã cập nhật ống kính.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Đã xoá ống kính.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code'        => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($ignoreId)],
            'name'        => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price'       => ['required', 'numeric', 'min:0'],
            'quantity'    => ['required', 'integer', 'min:0'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], [
            'code'        => 'mã sản phẩm',
            'name'        => 'tên sản phẩm',
            'category_id' => 'phân loại',
            'price'       => 'giá',
            'quantity'    => 'số lượng',
            'image'       => 'ảnh',
        ]);
    }
}