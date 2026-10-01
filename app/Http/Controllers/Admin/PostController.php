<?php
// app/Http/Controllers/Admin/PostController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with('author')
            ->when($request->q, fn ($query, $q) => $query->where('title', 'like', "%{$q}%"))
            ->latest('published_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create()
    {
        return view('admin.posts.create', ['post' => new Post()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        Post::create($data);

        return redirect()->route('admin.posts.index')->with('success', 'Đã đăng bài viết.');
    }

    public function edit(Post $post)
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $this->validated($request, $post->id);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return redirect()->route('admin.posts.index')->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();

        return back()->with('success', 'Đã xoá bài viết.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($ignoreId)],
            'excerpt'       => ['nullable', 'string', 'max:300'],
            'content'       => ['required', 'string'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_published'  => ['nullable', 'boolean'],
            'published_at'  => ['nullable', 'date'],
        ], [], ['title' => 'tiêu đề', 'slug' => 'đường dẫn', 'content' => 'nội dung', 'image' => 'ảnh đại diện']);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
