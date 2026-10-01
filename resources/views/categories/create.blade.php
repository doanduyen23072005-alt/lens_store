{{-- resources/views/categories/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Thêm phân loại')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Thêm phân loại</h1>
        <p class="page-sub">Đặt tên nhóm để gán ống kính vào sau.</p>
    </div>
    <a href="{{ route('categories.index') }}" class="btn btn-line">← Về danh sách</a>
</div>

<form action="{{ route('categories.store') }}" method="POST">
    @csrf
    @include('categories._form', ['submitLabel' => 'Lưu phân loại'])
</form>
@endsection