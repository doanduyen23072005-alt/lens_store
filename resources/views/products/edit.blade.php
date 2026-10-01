{{-- resources/views/products/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Sửa ống kính')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Sửa ống kính</h1>
        <p class="page-sub">{{ $product->code }} · {{ $product->name }}</p>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-line">← Về danh sách</a>
</div>

<form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('products._form', ['submitLabel' => 'Cập nhật'])
</form>
@endsection