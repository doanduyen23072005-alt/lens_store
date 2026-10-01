{{-- resources/views/admin/posts/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Blog / Tin tức')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Blog / Tin tức</h1>
        <p class="page-sub">Bài viết liên quan đến sản phẩm và ngành hàng nhiếp ảnh.</p>
    </div>
    <a href="{{ route('admin.posts.create') }}" class="btn btn-ink">+ Viết bài mới</a>
</div>

<div class="card-panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('admin.posts.index') }}" class="d-flex gap-2 w-100">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                   style="max-width:280px" placeholder="Tìm theo tiêu đề">
            <button class="btn btn-line">Tìm</button>
            @if (request('q'))
                <a href="{{ route('admin.posts.index') }}" class="btn btn-line">Xoá bộ lọc</a>
            @endif
        </form>
    </div>

    @if ($posts->count())
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width:70px">Ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Tác giả</th>
                        <th class="text-center">Trạng thái</th>
                        <th>Ngày đăng</th>
                        <th class="text-end" style="width:200px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($posts as $post)
                    <tr>
                        <td>
                            @if ($post->image)
                                <img src="{{ $post->image_url }}" class="thumb" alt="">
                            @else
                                <div class="thumb-empty">—</div>
                            @endif
                        </td>
                        <td class="fw-semibold">{{ $post->title }}</td>
                        <td class="text-muted small">{{ $post->author?->name ?? '—' }}</td>
                        <td class="text-center">
                            <span class="tag {{ $post->is_published ? 'tag-ok' : '' }}">
                                {{ $post->is_published ? 'Đã đăng' : 'Bản nháp' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ optional($post->published_at)->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-end">
                            @if ($post->is_published)
                                <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-line btn-sm" target="_blank">Xem</a>
                            @endif
                            <a href="{{ route('admin.posts.edit', $post->id) }}" class="btn btn-line btn-sm">Sửa</a>
                            <form action="{{ route('admin.posts.destroy', $post->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Xoá bài viết “{{ $post->title }}”?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-line btn-sm text-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">Tổng {{ $posts->total() }} bài viết</span>
            {{ $posts->links() }}
        </div>
    @else
        <div class="empty">
            <h4>Chưa có bài viết nào</h4>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-ink">Viết bài đầu tiên</a>
        </div>
    @endif
</div>
@endsection
