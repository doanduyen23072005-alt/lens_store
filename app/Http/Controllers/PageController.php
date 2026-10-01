<?php
// app/Http/Controllers/PageController.php
namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    /** Hiển thị trang tĩnh (Về chúng tôi, Liên hệ, Chính sách...) theo slug */
    public function show(Page $page)
    {
        abort_unless($page->is_published, 404);

        return view('pages.show', compact('page'));
    }
}
