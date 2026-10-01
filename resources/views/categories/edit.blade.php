{{-- resources/views/categories/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Sửa phân loại')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Sửa phân loại</h1>
        <p class="page-sub">{{ $category->code }} · {{ $category->name }}</p>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-line">← Về danh sách</a>
</div>

<form action="{{ route('categories.update', $category->id) }}" method="POST">
    @csrf
    @method('PUT')
    @include('categories._form', ['submitLabel' => 'Cập nhật'])
</form>
@endsection