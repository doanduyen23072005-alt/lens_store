{{-- resources/views/admin/pages/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Tạo trang')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Tạo trang tĩnh</h1>
        <p class="page-sub">VD: Về chúng tôi, Liên hệ, Chính sách bảo hành...</p>
    </div>
    <a href="{{ route('admin.pages.index') }}" class="btn btn-line">← Quay lại</a>
</div>

<form action="{{ route('admin.pages.store') }}" method="POST">
    @csrf
    @php $submitLabel = 'Tạo trang'; @endphp
    @include('admin.pages._form')
</form>
@endsection
