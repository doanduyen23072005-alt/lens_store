<?php
// app/Http/Controllers/Admin/PageController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderBy('title')->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create', ['page' => new Page()]);
    }

    public function store(Request $request)
    {
        Page::create($this->validated($request));

        return redirect()->route('admin.pages.index')->with('success', 'Đã tạo trang.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $page->update($this->validated($request, $page->id));

        return redirect()->route('admin.pages.index')->with('success', 'Đã cập nhật trang.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return back()->with('success', 'Đã xoá trang.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($ignoreId)],
            'meta_description'  => ['nullable', 'string', 'max:255'],
            'content'           => ['required', 'string'],
            'is_published'      => ['nullable', 'boolean'],
        ], [], ['title' => 'tiêu đề', 'slug' => 'đường dẫn', 'content' => 'nội dung']);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
