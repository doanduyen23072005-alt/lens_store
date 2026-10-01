{{-- resources/views/categories/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Phân loại')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Phân loại sản phẩm</h1>
        <p class="page-sub">Nhóm ống kính theo kiểu ngàm, tiêu cự hoặc công dụng.</p>
    </div>
    <a href="{{ route('categories.create') }}" class="btn btn-ink">+ Thêm phân loại</a>
</div>

<div class="card-panel">
    <div class="panel-head">
        <form method="GET" action="{{ route('categories.index') }}" class="d-flex gap-2 w-100">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" style="max-width:280px"
                   placeholder="Tìm theo tên hoặc mã">
            <button class="btn btn-line">Tìm</button>
            @if (request('q'))
                <a href="{{ route('categories.index') }}" class="btn btn-line">Xoá bộ lọc</a>
            @endif
        </form>
    </div>

    @if ($categories->count())
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center" style="width:70px">Thứ tự</th>
                        <th>Mã</th>
                        <th>Tên phân loại</th>
                        <th>Slug</th>
                        <th class="text-center">Sản phẩm</th>
                        <th class="text-center">Trạng thái</th>
                        <th class="text-end" style="width:210px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td class="text-center num text-muted">{{ $category->sort_order }}</td>
                        <td><span class="code-chip">{{ $category->code }}</span></td>
                        <td>
                            <a href="{{ route('categories.show', $category->id) }}" class="fw-semibold text-dark">
                                {{ $category->name }}
                            </a>
                            @if ($category->description)
                                <div class="text-muted small">{{ Str::limit($category->description, 70) }}</div>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $category->slug }}</td>
                        <td class="text-center num">{{ $category->products_count }}</td>
                        <td class="text-center">
                            <span class="tag {{ $category->is_active ? 'tag-ok' : '' }}">
                                {{ $category->is_active ? 'Đang hiện' : 'Đang ẩn' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('categories.show', $category->id) }}" class="btn btn-line btn-sm">Xem</a>
                            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-line btn-sm">Sửa</a>
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Xoá phân loại “{{ $category->name }}”?')">
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
            <span class="text-muted small">Tổng {{ $categories->total() }} phân loại</span>
            {{ $categories->links() }}
        </div>
    @else
        <div class="empty">
            <h4>Chưa có phân loại nào</h4>
            <p>Tạo phân loại trước, sau đó gán ống kính vào từng nhóm.</p>
            <a href="{{ route('categories.create') }}" class="btn btn-ink">Thêm phân loại</a>
        </div>
    @endif
</div>
@endsection