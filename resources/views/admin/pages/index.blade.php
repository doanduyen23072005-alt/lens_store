{{-- resources/views/admin/pages/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Trang tĩnh')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Trang tĩnh</h1>
        <p class="page-sub">Về chúng tôi, Liên hệ, Chính sách... hiển thị công khai trên website.</p>
    </div>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-ink">+ Tạo trang</a>
</div>

<div class="card-panel">
    @if ($pages->count())
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Tiêu đề</th>
                        <th>Đường dẫn</th>
                        <th class="text-center">Trạng thái</th>
                        <th>Cập nhật</th>
                        <th class="text-end" style="width:200px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($pages as $page)
                    <tr>
                        <td class="fw-semibold">{{ $page->title }}</td>
                        <td><span class="code-chip">/trang/{{ $page->slug }}</span></td>
                        <td class="text-center">
                            <span class="tag {{ $page->is_published ? 'tag-ok' : '' }}">
                                {{ $page->is_published ? 'Đã xuất bản' : 'Bản nháp' }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $page->updated_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            @if ($page->is_published)
                                <a href="{{ route('pages.show', $page->slug) }}" class="btn btn-line btn-sm" target="_blank">Xem</a>
                            @endif
                            <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-line btn-sm">Sửa</a>
                            <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Xoá trang “{{ $page->title }}”?')">
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
            <span class="text-muted small">Tổng {{ $pages->total() }} trang</span>
            {{ $pages->links() }}
        </div>
    @else
        <div class="empty">
            <h4>Chưa có trang nào</h4>
            <p>Tạo trang "Về chúng tôi", "Liên hệ" hoặc "Chính sách" đầu tiên.</p>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-ink">Tạo trang</a>
        </div>
    @endif
</div>
@endsection
