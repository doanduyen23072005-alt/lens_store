{{-- resources/views/loyalty/index.blade.php --}}
@extends('layouts.shop')
@section('title', 'Khách hàng thân thiết')

@section('content')
<div class="mb-4">
    <h1 class="mb-1">Khách hàng thân thiết</h1>
    <p class="text-muted mb-0">Tích điểm mỗi khi đơn hàng giao thành công — 1 điểm cho mỗi 50.000 đ.</p>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="loyalty-card">
            <div class="loyalty-label">Hạng thành viên</div>
            <div class="loyalty-tier">{{ $user->loyalty_tier }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="loyalty-card">
            <div class="loyalty-label">Tổng điểm tích luỹ</div>
            <div class="loyalty-value">{{ number_format($user->loyalty_points) }} điểm</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="loyalty-card">
            <div class="loyalty-label">Lên hạng kế tiếp</div>
            <div class="loyalty-value">
                @if ($user->points_to_next_tier !== null)
                    Còn {{ number_format($user->points_to_next_tier) }} điểm
                @else
                    Đã đạt hạng cao nhất 🎉
                @endif
            </div>
        </div>
    </div>
</div>

<div class="bg-white border rounded-4 p-4 mb-4">
    <h2 style="font-size:16px" class="mb-3">Các hạng thành viên</h2>
    <div class="tier-list">
        @foreach ($tiers as $tier => $threshold)
            <div class="tier-item {{ $user->loyalty_tier === $tier ? 'current' : '' }}">
                <strong>{{ $tier }}</strong>
                <span class="text-muted">từ {{ number_format($threshold) }} điểm</span>
            </div>
        @endforeach
    </div>
</div>

<div class="bg-white border rounded-4 p-4">
    <h2 style="font-size:16px" class="mb-3">Lịch sử điểm</h2>

    @if ($transactions->count())
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Thời gian</th>
                    <th>Lý do</th>
                    <th class="text-end">Điểm</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($transactions as $tx)
                <tr>
                    <td class="text-muted small">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $tx->reason }}</td>
                    <td class="text-end fw-semibold {{ $tx->points >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $tx->points >= 0 ? '+' : '' }}{{ number_format($tx->points) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-3">{{ $transactions->links() }}</div>
    @else
        <p class="text-muted mb-0">Chưa có điểm nào. Đặt hàng và nhận hàng thành công để bắt đầu tích điểm!</p>
    @endif
</div>

@push('styles')
<style>
    .loyalty-card {
        background: linear-gradient(135deg, var(--barrel), #262A33);
        color: #fff;
        border-radius: var(--radius-lg, 16px);
        padding: 20px 22px;
        height: 100%;
    }
    .loyalty-label { font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: #B9BEC6; }
    .loyalty-tier { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 26px; margin-top: 6px; color: var(--coating); }
    .loyalty-value { font-family: 'Space Grotesk', sans-serif; font-weight: 700; font-size: 22px; margin-top: 6px; }

    .tier-list { display: flex; gap: 12px; flex-wrap: wrap; }
    .tier-item {
        flex: 1; min-width: 150px;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 12px 14px;
        display: flex; flex-direction: column; gap: 3px;
    }
    .tier-item.current { border-color: var(--coating); background: #FEFBF3; }
</style>
@endpush
@endsection
