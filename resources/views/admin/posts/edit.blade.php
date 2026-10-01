{{-- resources/views/admin/posts/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Sửa bài viết')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Sửa bài viết</h1>
        <p class="page-sub">{{ $post->title }}</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="btn btn-line">← Quay lại</a>
</div>

<form action="{{ route('admin.posts.update', $post->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @php $submitLabel = 'Cập nhật'; @endphp
    @include('admin.posts._form')
</form>
@endsection
