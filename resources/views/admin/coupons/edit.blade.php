{{-- resources/views/admin/coupons/edit.blade.php --}}
@extends('layouts.admin')
@section('title', 'Sửa mã giảm giá')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Sửa mã giảm giá</h1>
        <p class="page-sub">{{ $coupon->code }} · đã dùng {{ $coupon->used_count }} lượt</p>
    </div>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-line">← Quay lại</a>
</div>

<form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
    @csrf
    @method('PUT')
    @php $submitLabel = 'Cập nhật'; @endphp
    @include('admin.coupons._form')
</form>
@endsection
