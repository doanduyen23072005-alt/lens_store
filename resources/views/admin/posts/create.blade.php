{{-- resources/views/admin/posts/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Viết bài mới')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Viết bài mới</h1>
        <p class="page-sub">Blog/tin tức liên quan đến ống kính và nhiếp ảnh.</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-line">← Quay lại</a>
</div>

<form action="{{ route('admin.posts.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @php $submitLabel = 'Đăng bài'; @endphp
    @include('admin.posts._form')
</form>
@endsection
