{{-- resources/views/products/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Thêm ống kính')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Thêm ống kính</h1>
        <p class="page-sub">Điền thông tin sản phẩm rồi lưu vào kho.</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-line">← Về danh sách</a>
</div>

<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @include('products._form', ['submitLabel' => 'Lưu ống kính'])
</form>
@endsection