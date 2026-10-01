{{-- resources/views/admin/coupons/create.blade.php --}}
@extends('layouts.admin')
@section('title', 'Tạo mã giảm giá')

@section('content')
<div class="topbar">
    <div>
        <h1 class="page-title">Tạo mã giảm giá</h1>
        <p class="page-sub">Thiết lập voucher cho chương trình khuyến mãi.</p>
    </div>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-line">← Quay lại</a>
</div>

<form action="{{ route('admin.coupons.store') }}" method="POST">
    @csrf
    @php $coupon = new \App\Models\Coupon(); $submitLabel = 'Tạo mã'; @endphp
    @include('admin.coupons._form')
</form>
@endsection
