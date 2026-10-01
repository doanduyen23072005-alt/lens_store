{{-- resources/views/admin/pages/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Sửa trang')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Sửa trang</h1>
        <p class="page-sub">{{ $page->title }}</p>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-line">← Quay lại</a>
</div>

<form action="{{ route('admin.pages.update', $page->id) }}" method="POST">
    @csrf
    @method('PUT')
    @php $submitLabel = 'Cập nhật'; @endphp
    @include('admin.pages._form')
</form>
@endsection
