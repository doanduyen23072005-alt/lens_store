<?php
// app/Http/Controllers/CategoryController.php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')
            ->when($request->q, fn ($query, $q) => $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            }))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create', ['category' => new Category(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Category::create($data);

        return redirect()->route('categories.index')
            ->with('success', 'Đã thêm phân loại mới.');
    }

    public function show(Category $category)
    {
        $category->load(['products' => fn ($q) => $q->latest()]);

        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validateData($request, $category->id));

        return redirect()->route('categories.index')
            ->with('success', 'Đã cập nhật phân loại.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Phân loại này còn sản phẩm. Hãy chuyển hoặc xoá sản phẩm trước.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Đã xoá phân loại.');
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code'        => ['required', 'string', 'max:50', Rule::unique('categories', 'code')->ignore($ignoreId)],
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($ignoreId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'   => ['nullable', 'boolean'],
        ], [], [
            'code'       => 'mã phân loại',
            'name'       => 'tên phân loại',
            'sort_order' => 'thứ tự',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = $request->boolean('is_active');

        return $data;
    }
}