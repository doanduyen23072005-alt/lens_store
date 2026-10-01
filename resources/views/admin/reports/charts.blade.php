{{-- resources/views/admin/reports/charts.blade.php --}}
@extends('layouts.admin')
@section('title', 'Biểu đồ báo cáo doanh thu')

@section('content')
<style>
    .viz-root {
        /* Bảng màu categorical đã kiểm định CVD (skill dataviz) — slot 1 (blue) cho chuỗi đơn,
           slot 1+2 (blue/orange) khi cần phân biệt 2 danh mục (MoMo/COD). */
        --series-1: #2a78d6;
        --series-1-soft: rgba(42, 120, 214, .14);
        --series-2: #eb6834;
        --text-primary: #1B1D21;
        --text-secondary: #6B7280;
        --grid-line: #EFF1F3;
        --axis-line: #DEE1E6;
    }

    .chart-wrap { min-height: 320px; position: relative; }
    .chart-wrap canvas { width: 100% !important; height: 320px !important; }

    .chart-empty {
        position: absolute; inset: 0;
        display: grid; place-items: center;
        color: var(--text-secondary);
        font-size: 13px;
    }
</style>

<div class="topbar">
    <div>
        <h1 class="page-title">Biểu đồ báo cáo doanh thu</h1>
        <p class="page-sub">Chỉ gồm đơn đã thanh toán, không bị hủy. Doanh thu tính theo ngày tạo đơn.</p>
    </div>
</div>

<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.reports.index') }}">Bảng số liệu</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.reports.charts') }}">Biểu đồ</a>
    </li>
</ul>

<div id="report-chart-error" class="alert alert-danger d-none" role="alert">
    Không tải được thư viện biểu đồ. Bạn có thể xem số liệu tại trang
    <a href="{{ route('admin.reports.index') }}">Bảng số liệu</a>.
</div>

<div class="viz-root row g-4">
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-head"><strong>Doanh thu theo danh mục</strong></div>
            <div class="panel-body chart-wrap"><canvas id="categoryRevenueChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-head"><strong>Sản phẩm bán chạy (số lượng)</strong></div>
            <div class="panel-body chart-wrap"><canvas id="bestSellerChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-head"><strong>Doanh thu theo ngày (30 ngày)</strong></div>
            <div class="panel-body chart-wrap"><canvas id="revenueByDateChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-head"><strong>Doanh thu theo tháng (12 tháng)</strong></div>
            <div class="panel-body chart-wrap"><canvas id="revenueByMonthChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card-panel">
            <div class="panel-head"><strong>Doanh thu theo năm</strong></div>
            <div class="panel-body chart-wrap"><canvas id="revenueByYearChart"></canvas></div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card-panel">
            <div class="panel-head"><strong>Doanh thu theo phương thức thanh toán</strong></div>
            <div class="panel-body chart-wrap" style="max-width:480px;margin:0 auto;">
                <canvas id="revenueByPaymentMethodChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div id="report-chart-data" hidden data-chart-data="{{ json_encode([
    'catLabels' => $catLabels ?? [],
    'catRevenue' => $catRevenue ?? [],
    'revDateLabels' => $revDateLabels ?? [],
    'revDateData' => $revDateData ?? [],
    'revMonthLabels' => $revMonthLabels ?? [],
    'revMonthData' => $revMonthData ?? [],
    'revYearLabels' => $revYearLabels ?? [],
    'revYearData' => $revYearData ?? [],
    'paymentMethodLabels' => $paymentMethodLabels ?? [],
    'paymentMethodRevenue' => $paymentMethodRevenue ?? [],
    'bestSellerLabels' => $bestSellerLabels ?? [],
    'bestSellerQty' => $bestSellerQty ?? [],
]) }}"></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') {
        document.getElementById('report-chart-error').classList.remove('d-none');
        return;
    }

    // Dữ liệu Blade nằm trong HTML; phần này chỉ sử dụng JavaScript thuần.
    const reportData = JSON.parse(document.getElementById('report-chart-data').dataset.chartData);
    const root = document.querySelector('.viz-root');
    const style = getComputedStyle(root);
    const color = (name) => style.getPropertyValue(name).trim();

    const SERIES_1 = color('--series-1');
    const SERIES_1_SOFT = color('--series-1-soft');
    const SERIES_2 = color('--series-2');
    const TEXT_SECONDARY = color('--text-secondary');
    const GRID_LINE = color('--grid-line');
    const AXIS_LINE = color('--axis-line');

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = TEXT_SECONDARY;

    const vnd = (value) => new Intl.NumberFormat('vi-VN').format(Math.round(value)) + ' đ';

    // Rút gọn số lớn trên trục để đỡ rối: 12.500.000 -> "12,5tr".
    const vndCompact = (value) => {
        const abs = Math.abs(value);
        if (abs >= 1_000_000) return (value / 1_000_000).toLocaleString('vi-VN', { maximumFractionDigits: 1 }) + 'tr';
        if (abs >= 1_000) return (value / 1_000).toLocaleString('vi-VN', { maximumFractionDigits: 1 }) + 'k';
        return String(value);
    };

    const currencyTooltip = (ctx) => `${ctx.dataset.label ?? ctx.label}: ${vnd(ctx.parsed.y ?? ctx.parsed)}`;

    /** Không có dữ liệu (mảng rỗng hoặc tổng = 0) thì hiện thông báo thay vì vẽ biểu đồ trống. */
    function isEmpty(data) {
        return !data.length || data.every((v) => !v);
    }

    function showEmpty(canvas, message) {
        canvas.style.display = 'none';
        const note = document.createElement('div');
        note.className = 'chart-empty';
        note.textContent = message;
        canvas.parentElement.appendChild(note);
    }

    const baseGrid = { color: GRID_LINE, drawTicks: false };
    const baseAxis = { color: AXIS_LINE };

    // ----- Doanh thu theo danh mục: xếp hạng nhiều danh mục -> thanh ngang, một màu. -----
    (() => {
        const canvas = document.getElementById('categoryRevenueChart');
        const labels = reportData.catLabels;
        const data = reportData.catRevenue.map(Number);

        if (isEmpty(data)) return showEmpty(canvas, 'Chưa có doanh thu theo danh mục.');

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Doanh thu',
                    data,
                    backgroundColor: SERIES_1,
                    borderRadius: 4,
                    barThickness: 22,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: currencyTooltip } },
                },
                scales: {
                    x: { beginAtZero: true, grid: baseGrid, border: baseAxis, ticks: { callback: (v) => vndCompact(v) } },
                    y: { grid: { display: false }, border: baseAxis },
                },
            },
        });
    })();

    // ----- Sản phẩm bán chạy: xếp hạng theo số lượng -> thanh ngang, một màu. -----
    (() => {
        const canvas = document.getElementById('bestSellerChart');
        const labels = reportData.bestSellerLabels;
        const data = reportData.bestSellerQty.map(Number);

        if (isEmpty(data)) return showEmpty(canvas, 'Chưa có sản phẩm nào được bán.');

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Số lượng đã bán',
                    data,
                    backgroundColor: SERIES_1,
                    borderRadius: 4,
                    barThickness: 20,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.parsed.x}` } },
                },
                scales: {
                    x: { beginAtZero: true, grid: baseGrid, border: baseAxis, ticks: { precision: 0 } },
                    y: { grid: { display: false }, border: baseAxis },
                },
            },
        });
    })();

    // ----- Doanh thu theo ngày: xu hướng theo thời gian -> đường mảnh, vùng fill nhạt. -----
    (() => {
        const canvas = document.getElementById('revenueByDateChart');
        const labels = reportData.revDateLabels.map((d) => new Date(d).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' }));
        const data = reportData.revDateData.map(Number);

        if (isEmpty(data)) return showEmpty(canvas, 'Chưa có doanh thu trong 30 ngày qua.');

        new Chart(canvas, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Doanh thu',
                    data,
                    borderColor: SERIES_1,
                    backgroundColor: SERIES_1_SOFT,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHitRadius: 10,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: SERIES_1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: currencyTooltip } },
                },
                scales: {
                    x: { grid: { display: false }, border: baseAxis, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
                    y: { beginAtZero: true, grid: baseGrid, border: baseAxis, ticks: { callback: (v) => vndCompact(v) } },
                },
            },
        });
    })();

    // ----- Doanh thu theo tháng / năm: cột mảnh, bo đầu, một màu. -----
    function barChart(canvasId, labels, data, emptyMessage) {
        const canvas = document.getElementById(canvasId);
        if (isEmpty(data)) return showEmpty(canvas, emptyMessage);

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Doanh thu',
                    data,
                    backgroundColor: SERIES_1,
                    borderRadius: 4,
                    maxBarThickness: 34,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: currencyTooltip } },
                },
                scales: {
                    x: { grid: { display: false }, border: baseAxis },
                    y: { beginAtZero: true, grid: baseGrid, border: baseAxis, ticks: { callback: (v) => vndCompact(v) } },
                },
            },
        });
    }

    barChart('revenueByMonthChart', reportData.revMonthLabels, reportData.revMonthData.map(Number), 'Chưa có doanh thu theo tháng.');
    barChart('revenueByYearChart', reportData.revYearLabels, reportData.revYearData.map(Number), 'Chưa có doanh thu theo năm.');

    // ----- Doanh thu theo phương thức: 2 danh mục -> donut, có legend vì >= 2 chuỗi. -----
    (() => {
        const canvas = document.getElementById('revenueByPaymentMethodChart');
        const labels = reportData.paymentMethodLabels;
        const data = reportData.paymentMethodRevenue.map(Number);

        if (isEmpty(data)) return showEmpty(canvas, 'Chưa có doanh thu theo phương thức thanh toán.');

        const total = data.reduce((a, b) => a + b, 0);

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: [SERIES_1, SERIES_2],
                    borderColor: '#fff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return `${ctx.label}: ${vnd(ctx.parsed)} (${pct}%)`;
                            },
                        },
                    },
                },
            },
        });
    })();
});
</script>
@endsection
