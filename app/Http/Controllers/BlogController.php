<?php
// app/Http/Controllers/BlogController.php
namespace App\Http\Controllers;

use App\Models\Post;

class BlogController extends Controller
{
    /** Danh sách tin tức/blog đã đăng */
    public function index()
    {
        $posts = Post::published()->latest('published_at')->paginate(6);

        return view('blog.index', compact('posts'));
    }

    /** Chi tiết một bài viết theo slug */
    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);

        $related = Post::published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
