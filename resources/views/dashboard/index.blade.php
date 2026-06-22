@extends('layouts.app')
@section('title', 'Dashboard Analitik')
@section('page-title', 'Dashboard Analitik')
@section('page-subtitle', 'Performa Momiasi & Little Mommies — Shopee & TikTok Shop')

@section('topbar-actions')
    <div style="position:relative;">
        <button type="button" id="periodFilterBtn" class="btn-ghost-momi d-flex align-items-center gap-2"
            style="min-width:180px;justify-content:space-between;font-size:12px;">
            <span><i class="bi bi-calendar3 me-1" style="color:var(--clr-teal);"></i>
                @if (count($selectedPeriods) === count($availablePeriods))
                    Semua Periode
                @elseif(count($selectedPeriods) === 1)
                    @php
                        $selectedDate = $selectedPeriods[0] ?? '';
                        echo $selectedDate
                            ? \Carbon\Carbon::createFromFormat('Y-m', $selectedDate)->isoFormat('MMM Y')
                            : '';
                    @endphp
                @else
                    {{ count($selectedPeriods) }} Bulan Dipilih
                @endif
            </span>
            <i class="bi bi-chevron-down" style="font-size:10px;color:var(--clr-teal);"></i>
        </button>

        <div id="periodDropdown"
            style="display:none;position:absolute;top:calc(100%+6px);right:0;background:white;border:1px solid var(--border);border-radius:12px;padding:12px;min-width:230px;z-index:999;box-shadow:0 8px 32px rgba(0,139,139,0.15);">
            <form method="GET" action="{{ route('dashboard') }}">
                <div
                    style="font-size:10px;color:var(--clr-teal);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;font-weight:700;">
                    Pilih Periode</div>
                <div style="max-height:220px;overflow-y:auto;">
                    <label
                        style="display:flex;align-items:center;gap:8px;padding:6px 4px;cursor:pointer;font-size:13px;color:#333;">
                        <input type="checkbox" id="checkAll" style="accent-color:var(--clr-teal);">
                        <strong>Semua Periode</strong>
                    </label>
                    <div style="border-top:1px solid var(--border);margin:4px 0;"></div>
                    @foreach ($availablePeriods as $p)
                        <label
                            style="display:flex;align-items:center;gap:8px;padding:6px 4px;cursor:pointer;font-size:13px;color:#555;">
                            <input type="checkbox" name="periods[]" value="{{ $p }}" class="period-check"
                                style="accent-color:var(--clr-teal);" {{ in_array($p, $selectedPeriods) ? 'checked' : '' }}>
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $p)->isoFormat('MMMM YYYY') }}
                        </label>
                    @endforeach
                </div>
                <button type="submit" class="btn-primary-momi w-100 mt-2"
                    style="justify-content:center;padding:8px;font-size:12px;">
                    <i class="bi bi-check2 me-1"></i>Terapkan
                </button>
            </form>
        </div>
    </div>
@endsection

@php
    // ============================================================
    // 🔥 METRIK UTAMA - SEMUA DARI CONTROLLER
    // ============================================================

    // 💰 REVENUE - DUA VERSI
    $revenueAll = $totalRevenueAll ?? 0;
    $revenueCompleted = $totalRevenueCompleted ?? 0;

    // 💵 PROFIT - DUA VERSI
    $profitAll = $totalProfitAll ?? 0;
    $profitCompleted = $totalProfitCompleted ?? 0;

    // 📦 ORDERS
    $totalOrders = $totalOrdersAll ?? 0;
    $ordersCompleted = $totalOrdersCompleted ?? 0;

    // 📢 ADS
    $totalAdspend = $totalAdspend ?? 0;

    // 📊 METRIK TURUNAN
    $profitMargin = $revenueCompleted > 0 ? round(($profitCompleted / $revenueCompleted) * 100, 1) : 0;

    $roasVal = $totalAdspend > 0 ? round($revenueAll / $totalAdspend, 2) : 0;

    $avgOrderVal = $totalOrders > 0 ? round($revenueAll / $totalOrders) : 0;

    $adspendRatio = $revenueAll > 0 ? round(($totalAdspend / $revenueAll) * 100, 2) : 0;

    $completionRate = $totalOrders > 0 ? round(($ordersCompleted / $totalOrders) * 100, 1) : 0;

    // 🔥 GROWTH dari monthlyStats
    $sorted = $monthlyStats->sortBy('period_month')->values();
    $lastM = $sorted->last();
    $prevM = $sorted->count() >= 2 ? $sorted->get($sorted->count() - 2) : null;

    $revGrowth =
        $prevM && $prevM->total_revenue > 0
            ? round((($lastM->total_revenue - $prevM->total_revenue) / $prevM->total_revenue) * 100, 1)
            : null;

    $ordGrowth =
        $prevM && $prevM->total_orders > 0
            ? round((($lastM->total_orders - $prevM->total_orders) / $prevM->total_orders) * 100, 1)
            : null;

    $adspendGrowth =
        $prevM && $prevM->total_adspend > 0
            ? round((($lastM->total_adspend - $prevM->total_adspend) / $prevM->total_adspend) * 100, 1)
            : null;

    $profGrowth =
        $prevM && $prevM->total_profit > 0
            ? round((($lastM->total_profit - $prevM->total_profit) / $prevM->total_profit) * 100, 1)
            : null;
@endphp

@section('content')

    {{-- Filter banner --}}
    @if (count($selectedPeriods) < count($availablePeriods))
        <div
            style="background:rgba(0,139,139,0.07);border:1px solid rgba(0,139,139,0.2);border-radius:10px;padding:9px 16px;margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-size:13px;color:var(--clr-teal);">
                <i class="bi bi-funnel-fill me-2"></i>
                Filter aktif:
                <strong>{{ implode(', ', array_map(fn($p) => \Carbon\Carbon::createFromFormat('Y-m', $p)->isoFormat('MMM Y'), $selectedPeriods)) }}</strong>
            </div>
            <a href="{{ route('dashboard') }}" style="font-size:12px;color:#888;text-decoration:none;">
                <i class="bi bi-x me-1"></i>Reset
            </a>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- 🔥 KPI CARDS - DENGAN DUA VERSI REVENUE --}}
    {{-- ================================================================ --}}
    <div class="row g-3 mb-4">
        {{-- REVENUE COMPLETED (PENDAPATAN RIIL) --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card teal">
                <div class="stat-icon teal"><i class="bi bi-coin"></i></div>
                <div class="stat-label">Revenue (Selesai)</div>
                <div class="stat-value">Rp {{ number_format($revenueCompleted / 1000000, 1, ',', '.') }}jt</div>
                <div class="stat-sub mt-1" style="font-size:10px;color:#888;">
                    Pendapatan riil dari order selesai
                </div>
                @if ($revGrowth !== null)
                    <div class="stat-sub d-flex align-items-center gap-2 mt-1">
                        <span class="{{ $revGrowth >= 0 ? 'badge-up' : 'badge-down' }}">
                            <i class="bi bi-arrow-{{ $revGrowth >= 0 ? 'up' : 'down' }}"></i>{{ abs($revGrowth) }}%
                        </span>
                        <span>vs bln lalu</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- TOTAL REVENUE (GROSS) --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card teal" style="opacity:0.75;border:1px dashed var(--border);">
                <div class="stat-icon teal" style="background:rgba(0,139,139,0.1);"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-label">Revenue (Gross)</div>
                <div class="stat-value" style="font-size:18px;">Rp
                    {{ number_format($revenueAll / 1000000, 1, ',', '.') }}jt</div>
                <div class="stat-sub mt-1" style="font-size:10px;color:#888;">
                    📊 Termasuk batal, retur, pending
                    <span style="display:block;font-size:9px;color:#aaa;">
                        {{ $completionRate }}% completion rate
                    </span>
                </div>
            </div>
        </div>

        {{-- PROFIT --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card green">
                <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-label">Profit Bersih</div>
                <div class="stat-value" style="color:var(--green);">Rp
                    {{ number_format($profitCompleted / 1000000, 1, ',', '.') }}jt</div>
                <div class="stat-sub mt-1">
                    Margin: <strong style="color:var(--green);">{{ $profitMargin }}%</strong>
                    <span style="font-size:10px;color:#888;display:block;">
                        dari revenue selesai
                    </span>
                </div>
                @if ($profGrowth !== null)
                    <div class="stat-sub d-flex align-items-center gap-2 mt-1">
                        <span class="{{ $profGrowth >= 0 ? 'badge-up' : 'badge-down' }}">
                            <i class="bi bi-arrow-{{ $profGrowth >= 0 ? 'up' : 'down' }}"></i>{{ abs($profGrowth) }}%
                        </span>
                        <span>vs bln lalu</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ORDERS --}}
        <div class="col-6 col-xl-3">
            <div class="stat-card yellow">
                <div class="stat-icon yellow"><i class="bi bi-bag-check"></i></div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value" style="color:var(--yellow);">{{ number_format($totalOrders, 0, ',', '.') }}</div>
                <div class="stat-sub mt-1" style="font-size:10px;color:#888;">
                    {{ number_format($ordersCompleted, 0, ',', '.') }} selesai ·
                    {{ number_format($totalOrders - $ordersCompleted, 0, ',', '.') }} lainnya
                    <span style="display:block;font-size:9px;color:#aaa;">
                        AOV: Rp {{ number_format($avgOrderVal, 0, ',', '.') }}
                    </span>
                </div>
                @if ($ordGrowth !== null)
                    <div class="stat-sub d-flex align-items-center gap-2 mt-1">
                        <span class="{{ $ordGrowth >= 0 ? 'badge-up' : 'badge-down' }}">
                            <i class="bi bi-arrow-{{ $ordGrowth >= 0 ? 'up' : 'down' }}"></i>{{ abs($ordGrowth) }}%
                        </span>
                        <span>vs bln lalu</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 🔥 ORDER STATUS BREAKDOWN --}}
    {{-- ================================================================ --}}
    @if ($totalOrders > 0)
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card-light">
                    <div class="card-header-light">
                        <h3 class="card-title-light">
                            <i class="bi bi-clipboard-data me-2" style="color:var(--clr-teal);"></i>
                            Ringkasan Status Order
                        </h3>
                        <span style="font-size:11px;color:#888;">Total {{ number_format($totalOrders, 0, ',', '.') }}
                            order</span>
                    </div>
                    <div class="card-body-light">
                        <div class="row g-3">
                            @foreach ($orderStatusData as $status)
                                <div class="col-md-3 col-6">
                                    <div
                                        style="background:#FAFFFE;border:1px solid var(--border);border-radius:10px;padding:14px 16px;border-top:3px solid {{ $status->color }};height:100%;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div>
                                                <div style="font-size:11px;color:#888;font-weight:600;">
                                                    {{ $status->label }}</div>
                                                <div style="font-size:22px;font-weight:800;color:{{ $status->color }};">
                                                    {{ number_format($status->count, 0, ',', '.') }}
                                                </div>
                                                <div style="font-size:11px;color:#666;margin-top:2px;">
                                                    <span
                                                        style="padding:2px 8px;border-radius:12px;font-size:10px;background:{{ $status->color }}22;color:{{ $status->color }};">
                                                        {{ $status->percentage }}%
                                                    </span>
                                                </div>
                                            </div>
                                            <div
                                                style="width:40px;height:40px;border-radius:50%;background:{{ $status->color }}22;display:flex;align-items:center;justify-content:center;">
                                                <i class="bi {{ $status->icon }}"
                                                    style="color:{{ $status->color }};font-size:18px;"></i>
                                            </div>
                                        </div>
                                        @if ($status->revenue > 0)
                                            <div
                                                style="font-size:10px;color:#888;margin-top:6px;border-top:1px solid var(--border);padding-top:6px;">
                                                Revenue: Rp {{ number_format($status->revenue, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- 🔥 SUMMARY ADS PER MARKETPLACE --}}
    {{-- ================================================================ --}}
    @if ($marketplaceSummary->count() >= 2)
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card-light">
                    <div class="card-header-light">
                        <h3 class="card-title-light">
                            <i class="bi bi-megaphone me-2" style="color:var(--clr-magenta);"></i>
                            Ringkasan Iklan per Marketplace
                        </h3>
                        <span style="font-size:11px;color:#888;">Total biaya iklan & efisiensi per platform</span>
                    </div>
                    <div class="card-body-light">
                        <div class="row g-3">
                            @foreach ($marketplaceSummary as $mp)
                                @php
                                    $mpRoas =
                                        $mp->total_adspend > 0 ? round($mp->total_revenue / $mp->total_adspend, 2) : 0;
                                    $mpMargin =
                                        $mp->total_revenue > 0
                                            ? round(($mp->total_profit / $mp->total_revenue) * 100, 1)
                                            : 0;
                                    $mpAdspendRatio =
                                        $mp->total_revenue > 0
                                            ? round(($mp->total_adspend / $mp->total_revenue) * 100, 2)
                                            : 0;
                                    $mpShare =
                                        $totalAdspend > 0 ? round(($mp->total_adspend / $totalAdspend) * 100, 1) : 0;
                                    $roasClr =
                                        $mpRoas >= 4 ? 'var(--green)' : ($mpRoas >= 2 ? 'var(--yellow)' : 'var(--red)');
                                @endphp
                                <div class="col-md-6">
                                    <div
                                        style="background:#FAFFFE;border:1px solid var(--border);border-radius:10px;padding:14px 18px;border-left:4px solid {{ $mp->marketplace_color }};">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div style="font-size:12px;font-weight:600;color:#333;">
                                                    <span
                                                        style="display:inline-block;width:10px;height:10px;border-radius:50%;background:{{ $mp->marketplace_color }};margin-right:8px;"></span>
                                                    {{ $mp->marketplace_name }}
                                                </div>
                                                <div style="font-size:11px;color:#888;margin-top:2px;">
                                                    {{ $mpShare }}% dari total ad spend
                                                </div>
                                            </div>
                                            <div style="text-align:right;">
                                                <div style="font-size:16px;font-weight:700;color:var(--clr-magenta);">
                                                    Rp {{ number_format($mp->total_adspend, 0, ',', '.') }}
                                                </div>
                                                <div style="font-size:11px;color:#888;">
                                                    {{ $mpAdspendRatio }}% dari revenue
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3 mt-2"
                                            style="border-top:1px solid var(--border);padding-top:8px;">
                                            <div>
                                                <div style="font-size:10px;color:#888;">ROAS</div>
                                                <div style="font-size:18px;font-weight:800;color:{{ $roasClr }};">
                                                    {{ $mpRoas }}×
                                                </div>
                                            </div>
                                            <div>
                                                <div style="font-size:10px;color:#888;">Margin</div>
                                                <div
                                                    style="font-size:16px;font-weight:700;color:{{ $mpMargin >= 20 ? 'var(--green)' : ($mpMargin >= 10 ? 'var(--yellow)' : 'var(--red)') }};">
                                                    {{ $mpMargin }}%
                                                </div>
                                            </div>
                                            <div>
                                                <div style="font-size:10px;color:#888;">Revenue</div>
                                                <div style="font-size:14px;font-weight:600;color:var(--clr-teal);">
                                                    Rp {{ number_format($mp->total_revenue / 1000000, 1, ',', '.') }}jt
                                                </div>
                                            </div>
                                            <div>
                                                <div style="font-size:10px;color:#888;">Orders</div>
                                                <div style="font-size:14px;font-weight:600;color:#333;">
                                                    {{ number_format($mp->total_orders, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- 🔥 TREN HARIAN + DONUT CHART --}}
    {{-- ================================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card-light h-100">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-activity me-2"></i>Tren Revenue Harian per Platform</h3>
                    <div style="font-size:11px;color:#888;">Garis terpisah per marketplace</div>
                </div>
                <div class="card-body-light">
                    <canvas id="trendChart" height="110"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card-light h-100">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-pie-chart me-2"></i>Kontribusi Platform</h3>
                    <select id="donutMetric" class="form-select-light"
                        style="width:auto;font-size:11px;padding:4px 8px;">
                        <option value="revenue">Terhadap Revenue</option>
                        <option value="orders">Terhadap Order</option>
                        <option value="adspend">Terhadap Ad Spend</option>
                        <option value="profit">Terhadap Profit</option>
                    </select>
                </div>
                <div class="card-body-light d-flex flex-column align-items-center">
                    <div style="position:relative;width:170px;height:170px;">
                        <canvas id="donutChart"></canvas>
                        <div id="donutCenter"
                            style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;text-align:center;padding:20px;">
                            <div id="donutCenterLabel" style="font-size:10px;color:#888;">Total Revenue</div>
                            <div id="donutCenterValue" style="font-size:13px;font-weight:700;color:var(--clr-teal);">
                                Rp {{ number_format($revenueAll / 1000000, 1, ',', '.') }}jt
                            </div>
                        </div>
                    </div>
                    <div class="w-100 mt-3">
                        @foreach ($marketplaceSummary as $mp)
                            <div class="d-flex align-items-center justify-content-between py-2"
                                style="border-bottom:1px solid var(--border);">
                                <div class="d-flex align-items-center gap-2">
                                    <div
                                        style="width:10px;height:10px;border-radius:50%;background:{{ $mp->marketplace_color }};flex-shrink:0;">
                                    </div>
                                    <span style="font-size:13px;color:#333;">{{ $mp->marketplace_name }}</span>
                                </div>
                                <div>
                                    <span class="donut-pct" data-slug="{{ $mp->marketplace_slug }}"
                                        style="font-size:13px;font-weight:700;color:var(--clr-teal);">
                                        {{ $revenueAll > 0 ? round(($mp->total_revenue / $revenueAll) * 100, 1) : 0 }}%
                                    </span>
                                    <span class="donut-pct-label"
                                        style="font-size:11px;color:#888;margin-left:3px;">Revenue</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 🔥 MARKETPLACE CARDS --}}
    {{-- ================================================================ --}}
    <div class="row g-3 mb-4">
        @foreach ($marketplaceSummary as $mp)
            @php
                $mpRoas = $mp->total_adspend > 0 ? round($mp->total_revenue / $mp->total_adspend, 2) : 0;
                $mpMargin = $mp->total_revenue > 0 ? round(($mp->total_profit / $mp->total_revenue) * 100, 1) : 0;
                $mpShare = $totalOrders > 0 ? round(($mp->total_orders / $totalOrders) * 100, 1) : 0;
                $mpAdspendRatio =
                    $mp->total_revenue > 0 ? round(($mp->total_adspend / $mp->total_revenue) * 100, 2) : 0;
                $roasClr = $mpRoas >= 4 ? 'var(--green)' : ($mpRoas >= 2 ? 'var(--yellow)' : 'var(--red)');
                $roasLbl = $mpRoas >= 4 ? 'Efisien ✓' : ($mpRoas >= 2 ? 'Cukup' : 'Perlu Review');
                $marginClr = $mpMargin >= 25 ? 'var(--green)' : ($mpMargin >= 15 ? 'var(--yellow)' : 'var(--red)');
            @endphp
            <div class="col-md-6">
                <div class="card-light">
                    <div class="card-header-light">
                        <div class="d-flex align-items-center gap-2">
                            <div
                                style="width:12px;height:12px;border-radius:50%;background:{{ $mp->marketplace_color }};flex-shrink:0;">
                            </div>
                            <h3 class="card-title-light mb-0">{{ $mp->marketplace_name }}</h3>
                        </div>
                        <span class="badge-{{ $mp->marketplace_slug }}"
                            style="font-size:11px;padding:4px 10px;border-radius:20px;font-weight:600;">
                            {{ $mpShare }}% orders
                        </span>
                    </div>
                    <div class="card-body-light">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="mp-stat-label">Revenue</div>
                                <div class="mp-stat-value">Rp {{ number_format($mp->total_revenue, 0, ',', '.') }}</div>
                                <div class="progress-light mt-2">
                                    <div class="progress-bar"
                                        style="width:{{ $revenueAll > 0 ? round(($mp->total_revenue / $revenueAll) * 100) : 0 }}%;background:{{ $mp->marketplace_color }};border-radius:4px;height:6px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mp-stat-label">Profit</div>
                                <div class="mp-stat-value" style="color:var(--green);">Rp
                                    {{ number_format($mp->total_profit, 0, ',', '.') }}</div>
                                <div style="font-size:11px;color:{{ $marginClr }};margin-top:4px;">
                                    Margin <strong>{{ $mpMargin }}%</strong>
                                    <span style="color:#888;">|</span>
                                    Ad Spend {{ $mpAdspendRatio }}%
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mp-stat-label">Ad Spend</div>
                                <div style="font-size:13px;font-weight:600;color:var(--clr-magenta);">Rp
                                    {{ number_format($mp->total_adspend, 0, ',', '.') }}</div>
                            </div>
                            <div class="col-4">
                                <div class="mp-stat-label">ROAS</div>
                                <div style="font-size:22px;font-weight:800;color:{{ $roasClr }};line-height:1;">
                                    {{ $mpRoas }}×</div>
                                <div style="font-size:10px;color:{{ $roasClr }};margin-top:2px;">{{ $roasLbl }}
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="mp-stat-label">Orders</div>
                                <div style="font-size:16px;font-weight:600;color:var(--clr-teal);">
                                    {{ number_format($mp->total_orders, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ================================================================ --}}
    {{-- 🔥 GRAFIK BULANAN + TOP 5 --}}
    {{-- ================================================================ --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <div class="card-light h-100">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-bar-chart-steps me-2"></i>Revenue per Bulan per Platform
                    </h3>
                    <select id="mpMonthMetric" class="form-select-light"
                        style="width:auto;font-size:11px;padding:4px 8px;">
                        <option value="revenue">Revenue</option>
                        <option value="profit">Profit</option>
                        <option value="adspend">Ad Spend</option>
                        <option value="orders">Orders</option>
                    </select>
                </div>
                <div class="card-body-light">
                    <canvas id="mpMonthChart" height="140"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card-light h-100">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-trophy me-2" style="color:var(--yellow);"></i>Top 5
                        Produk Terlaris</h3>
                    <span style="font-size:11px;color:#888;">By revenue</span>
                </div>
                <div class="card-body-light p-0">
                    @forelse($top5Products as $i => $p)
                        @php
                            $rc = ['#008B8B', '#46B8A7', '#C61C8C', '#d97706', '#16a34a'];
                            $pShare = $revenueAll > 0 ? round(($p->total_revenue / $revenueAll) * 100, 1) : 0;
                            $pMargin =
                                $p->total_revenue > 0 ? round(($p->total_profit / $p->total_revenue) * 100, 1) : 0;
                            $pAdspend = (float) ($p->total_adspend ?? 0);
                            $pRoas = $pAdspend > 0 ? round($p->total_revenue / $pAdspend, 2) : 0;
                        @endphp
                        <div style="padding:11px 16px;border-bottom:1px solid var(--border);">
                            <div class="d-flex align-items-start gap-3">
                                <div
                                    style="width:26px;height:26px;border-radius:50%;background:{{ $rc[$i] }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:white;flex-shrink:0;margin-top:2px;">
                                    {{ $i + 1 }}
                                </div>
                                <div style="flex:1;overflow:hidden;">
                                    <div style="font-size:12px;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#333;"
                                        title="{{ $p->product_name }}">
                                        {{ $p->product_name }}
                                    </div>
                                    <div style="font-size:10px;color:#888;">{{ $p->product_category }} ·
                                        {{ number_format($p->total_qty, 0, ',', '.') }} unit</div>
                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                        <span style="font-size:12px;font-weight:700;color:var(--green);">Rp
                                            {{ number_format($p->total_revenue, 0, ',', '.') }}</span>
                                        <span style="font-size:10px;color:#888;">Margin {{ $pMargin }}%</span>
                                        @if ($pAdspend > 0)
                                            <span style="font-size:10px;color:var(--clr-magenta);">ROAS
                                                {{ $pRoas }}×</span>
                                        @endif
                                        <span
                                            style="font-size:10px;background:var(--accent-soft);color:var(--clr-teal);padding:2px 6px;border-radius:10px;">{{ $pShare }}%</span>
                                    </div>
                                    <div class="progress-light mt-1" style="height:3px;">
                                        <div class="progress-bar"
                                            style="width:{{ $pShare }}%;background:{{ $rc[$i] }};border-radius:3px;height:3px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4" style="color:#aaa;">Belum ada data produk</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- 🔥 TABEL RINGKASAN BULANAN --}}
    {{-- ================================================================ --}}
    @if ($monthlyStats->count() > 1)
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card-light">
                    <div class="card-header-light">
                        <h3 class="card-title-light"><i class="bi bi-table me-2"></i>Ringkasan per Bulan</h3>
                        <span style="font-size:11px;color:#888;">Revenue, Ad Spend per Platform, Profit, Margin,
                            ROAS</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th style="min-width:100px;">Bulan</th>
                                    <th class="text-end" style="min-width:130px;">Revenue</th>
                                    <th class="text-end" style="min-width:100px;color:var(--clr-magenta);">Ad Spend Total
                                    </th>
                                    <th class="text-end" style="min-width:100px;color:#EE4D2D;">Ad Spend Shopee</th>
                                    <th class="text-end" style="min-width:100px;color:#46B8A7;">Ad Spend TikTok</th>
                                    <th class="text-end" style="min-width:90px;">Ad %</th>
                                    <th class="text-end" style="min-width:130px;color:var(--green);">Profit (Net)</th>
                                    <th class="text-end" style="min-width:90px;">Margin (Net)</th>
                                    <th class="text-end" style="min-width:80px;">ROAS</th>
                                    <th class="text-end" style="min-width:80px;">Orders</th>
                                    <th class="text-end" style="min-width:90px;">Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyStats->sortByDesc('period_month') as $i => $ms)
                                    @php
                                        $periodMonth = $ms->period_month;

                                        $shopeeData = $marketplaceMonthData
                                            ->filter(function ($item) use ($periodMonth) {
                                                return $item->period_month == $periodMonth &&
                                                    $item->marketplace_slug == 'shopee';
                                            })
                                            ->first();

                                        $tiktokData = $marketplaceMonthData
                                            ->filter(function ($item) use ($periodMonth) {
                                                return $item->period_month == $periodMonth &&
                                                    $item->marketplace_slug == 'tiktok';
                                            })
                                            ->first();

                                        $shopeeAdspend = $shopeeData ? (float) ($shopeeData->total_adspend ?? 0) : 0;
                                        $tiktokAdspend = $tiktokData ? (float) ($tiktokData->total_adspend ?? 0) : 0;
                                        $totalAdspend = $shopeeAdspend + $tiktokAdspend;

                                        $totalRevenue = (float) ($ms->total_revenue ?? 0);
                                        $totalProfit = (float) ($ms->total_profit ?? 0);
                                        $totalOrders = (int) ($ms->total_orders ?? 0);

                                        $netMargin =
                                            $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;
                                        $adspendRatio =
                                            $totalRevenue > 0 ? round(($totalAdspend / $totalRevenue) * 100, 2) : 0;
                                        $roas = $totalAdspend > 0 ? round($totalRevenue / $totalAdspend, 2) : 0;

                                        $shopeeRoas =
                                            $shopeeAdspend > 0
                                                ? round(($shopeeData->total_revenue ?? 0) / $shopeeAdspend, 2)
                                                : 0;
                                        $tiktokRoas =
                                            $tiktokAdspend > 0
                                                ? round(($tiktokData->total_revenue ?? 0) / $tiktokAdspend, 2)
                                                : 0;

                                        $msPrev = $monthlyStats->sortByDesc('period_month')->get($i + 1);
                                        $msGrowth =
                                            $msPrev && $msPrev->total_revenue > 0
                                                ? round(
                                                    (($totalRevenue - $msPrev->total_revenue) /
                                                        $msPrev->total_revenue) *
                                                        100,
                                                    1,
                                                )
                                                : null;

                                        $marginColor =
                                            $netMargin >= 25
                                                ? 'var(--green)'
                                                : ($netMargin >= 15
                                                    ? 'var(--yellow)'
                                                    : ($netMargin >= 5
                                                        ? '#d97706'
                                                        : 'var(--red)'));
                                        $roasColor =
                                            $roas >= 5
                                                ? 'var(--green)'
                                                : ($roas >= 3
                                                    ? 'var(--yellow)'
                                                    : ($roas >= 1.5
                                                        ? '#d97706'
                                                        : 'var(--red)'));
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong style="color:var(--clr-teal);">
                                                {{ \Carbon\Carbon::createFromFormat('Y-m', $periodMonth)->isoFormat('MMMM YYYY') }}
                                            </strong>
                                            <div style="font-size:9px;color:#aaa;margin-top:2px;">
                                                {{ $totalOrders }} orders
                                            </div>
                                        </td>
                                        <td class="text-end" style="font-weight:700;color:var(--clr-teal);">
                                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end" style="font-weight:700;color:var(--clr-magenta);">
                                            Rp {{ number_format($totalAdspend, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end" style="color:#EE4D2D;">
                                            Rp {{ number_format($shopeeAdspend, 0, ',', '.') }}
                                            @if ($shopeeRoas > 0)
                                                <div style="font-size:9px;color:#888;">ROAS {{ $shopeeRoas }}×</div>
                                            @endif
                                        </td>
                                        <td class="text-end" style="color:#46B8A7;">
                                            Rp {{ number_format($tiktokAdspend, 0, ',', '.') }}
                                            @if ($tiktokRoas > 0)
                                                <div style="font-size:9px;color:#888;">ROAS {{ $tiktokRoas }}×</div>
                                            @endif
                                        </td>
                                        <td class="text-end" style="color:#888;">
                                            {{ $adspendRatio }}%
                                        </td>
                                        <td class="text-end" style="font-weight:600;color:var(--green);">
                                            Rp {{ number_format($totalProfit, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            <span style="font-weight:700;color:{{ $marginColor }};">
                                                {{ $netMargin }}%
                                            </span>
                                            <div style="font-size:9px;color:#888;margin-top:2px;">
                                                Net Profit Margin
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span style="font-weight:700;color:{{ $roasColor }};">
                                                {{ $roas }}×
                                            </span>
                                            <div style="font-size:9px;color:#888;margin-top:2px;">
                                                @if ($roas >= 4)
                                                    Efisien
                                                @elseif($roas >= 2)
                                                    Cukup
                                                @else
                                                    🔴 Perlu Review
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end" style="font-weight:600;color:var(--clr-teal);">
                                            {{ number_format($totalOrders, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end">
                                            @if ($msGrowth !== null)
                                                <span class="{{ $msGrowth >= 0 ? 'badge-up' : 'badge-down' }}">
                                                    <i class="bi bi-arrow-{{ $msGrowth >= 0 ? 'up' : 'down' }}"></i>
                                                    {{ abs($msGrowth) }}%
                                                </span>
                                            @else
                                                <span style="color:#ccc;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================ --}}
    {{-- 🔥 INSIGHTS --}}
    {{-- ================================================================ --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card-light">
                <div class="card-header-light">
                    <h3 class="card-title-light"><i class="bi bi-lightbulb me-2"
                            style="color:var(--yellow);"></i>Analisis Bisnis & Rekomendasi</h3>
                    <span style="font-size:11px;color:#888;">{{ count($insights) }} insight terdeteksi</span>
                </div>
                <div class="card-body-light">
                    <div class="row g-3">
                        @forelse($insights as $ins)
                            <div class="col-md-6 col-xl-4">
                                <div
                                    style="background:#FAFFFE;border:1px solid var(--border);border-left:3px solid {{ $ins['border'] }};border-radius:12px;padding:16px;height:100%;">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                                        <div
                                            style="width:28px;height:28px;border-radius:7px;background:{{ $ins['color'] }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="bi {{ $ins['icon'] }}"
                                                style="color:{{ $ins['color'] }};font-size:14px;"></i>
                                        </div>
                                        <div
                                            style="font-size:10px;font-weight:700;color:{{ $ins['color'] }};text-transform:uppercase;letter-spacing:0.5px;">
                                            {{ $ins['label'] }}</div>
                                    </div>
                                    <p style="font-size:12px;color:#444;line-height:1.75;margin:0;">{{ $ins['text'] }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4" style="color:#aaa;">
                                <i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                Belum cukup data untuk menghasilkan insight.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const dailyTrend = @json($dailyTrend);
        const mpSummary = @json($marketplaceSummary);
        const mpMonthRaw = @json($marketplaceMonthData);
        const totRevenue = {{ $revenueAll }};
        const totOrders = {{ $totalOrders }};
        const totAdspend = {{ $totalAdspend }};
        const totProfit = {{ $profitAll }};

        function fmtRp(v) {
            return 'Rp ' + Math.round(v).toLocaleString('id-ID');
        }

        function fmtJt(v) {
            return 'Rp ' + (v / 1000000).toFixed(1) + 'jt';
        }

        function mLbl(ym) {
            const [y, m] = ym.split('-');
            return ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'][+m] + ' ' + y;
        }

        // ============================================================
        // CHART 1: Tren Harian
        // ============================================================
        (() => {
            const platforms = [...new Set(dailyTrend.map(d => d.marketplace_name))];
            const allDates = [...new Set(dailyTrend.map(d => d.transaction_date))].sort();
            const palette = {
                shopee: '#EE4D2D',
                tiktok: '#46B8A7'
            };

            const cleanDates = allDates.map(d => {
                if (d && d.includes('T')) {
                    return d.split('T')[0].slice(5);
                }
                return d ? d.slice(5) : '';
            });

            const datasets = platforms.map(name => {
                const slug = dailyTrend.find(d => d.marketplace_name === name)?.marketplace_slug || '';
                const color = palette[slug] || '#008B8B';
                const map = Object.fromEntries(
                    dailyTrend.filter(d => d.marketplace_name === name)
                    .map(d => [d.transaction_date, parseFloat(d.daily_revenue)])
                );
                return {
                    label: name,
                    data: allDates.map(d => map[d] ?? null),
                    borderColor: color,
                    backgroundColor: 'transparent',
                    fill: false,
                    tension: 0.35,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: color,
                    borderWidth: 2.5,
                    spanGaps: false,
                };
            });

            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: cleanDates,
                    datasets
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            labels: {
                                font: {
                                    size: 12
                                },
                                boxWidth: 12,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${fmtRp(ctx.raw)}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: '#E8F5F5'
                            },
                            ticks: {
                                maxTicksLimit: 20,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: '#E8F5F5'
                            },
                            ticks: {
                                callback: fmtJt,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        })();

        // ============================================================
        // CHART 2: Donut Chart - KONSISTEN 100%
        // ============================================================
        let donutChart;

        // 🔥 HITUNG TOTAL DARI mpSummary UNTUK KONSISTENSI
        const revenueTotal = mpSummary.reduce((sum, m) => sum + (parseFloat(m.total_revenue) || 0), 0);
        const ordersTotal = mpSummary.reduce((sum, m) => sum + (parseInt(m.total_orders) || 0), 0);
        const adspendTotal = mpSummary.reduce((sum, m) => sum + (parseFloat(m.total_adspend) || 0), 0);
        const profitTotal = mpSummary.reduce((sum, m) => sum + (parseFloat(m.total_profit) || 0), 0);

        const donutMap = {
            revenue: {
                values: mpSummary.map(m => parseFloat(m.total_revenue) || 0),
                label: 'Revenue',
                total: revenueTotal,
                fmt: fmtRp
            },
            orders: {
                values: mpSummary.map(m => parseInt(m.total_orders) || 0),
                label: 'Orders',
                total: ordersTotal,
                fmt: v => Math.round(v).toLocaleString('id-ID') + ' order'
            },
            adspend: {
                values: mpSummary.map(m => parseFloat(m.total_adspend) || 0),
                label: 'Ad Spend',
                total: adspendTotal,
                fmt: fmtRp
            },
            profit: {
                values: mpSummary.map(m => parseFloat(m.total_profit) || 0),
                label: 'Profit',
                total: profitTotal,
                fmt: fmtRp
            },
        };

        const MP_COLORS = {
            shopee: '#EE4D2D',
            tiktok: '#46B8A7'
        };

        function buildDonut(metric) {
            const d = donutMap[metric];
            if (!d || d.total === 0) {
                if (donutChart) donutChart.destroy();
                document.getElementById('donutCenterValue').textContent = 'Tidak ada data';
                return;
            }

            if (donutChart) donutChart.destroy();

            const chartData = {
                labels: mpSummary.map(m => m.marketplace_name),
                datasets: [{
                    data: d.values,
                    backgroundColor: mpSummary.map(m => MP_COLORS[m.marketplace_slug] || '#008B8B'),
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            };

            donutChart = new Chart(document.getElementById('donutChart'), {
                type: 'doughnut',
                data: chartData,
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                    return ` ${ctx.label}: ${d.fmt(ctx.raw)} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });

            document.getElementById('donutCenterLabel').textContent = 'Total ' + d.label;
            const tv = d.total;
            document.getElementById('donutCenterValue').textContent =
                metric === 'orders' ? tv.toLocaleString('id-ID') + ' order' :
                'Rp ' + (tv / 1000000).toFixed(1) + 'jt';

            // Update persentase di list
            document.querySelectorAll('.donut-pct').forEach(el => {
                const mp = mpSummary.find(m => m.marketplace_slug === el.dataset.slug);
                if (!mp) return;
                const idx = mpSummary.indexOf(mp);
                const val = d.values[idx] || 0;
                const pct = d.total > 0 ? ((val / d.total) * 100).toFixed(1) : '0';
                el.textContent = pct + '%';
                const labelEl = el.nextElementSibling;
                if (labelEl) labelEl.textContent = d.label;
            });
        }

        buildDonut('revenue');
        document.getElementById('donutMetric').addEventListener('change', e => buildDonut(e.target.value));

        // ============================================================
        // CHART 3: Grouped Bar Bulanan
        // ============================================================
        let mpMonthChart;
        const mpMetricMap = {
            revenue: {
                key: 'total_revenue',
                fmt: fmtJt
            },
            profit: {
                key: 'total_profit',
                fmt: fmtJt
            },
            adspend: {
                key: 'total_adspend',
                fmt: fmtJt
            },
            orders: {
                key: 'total_orders',
                fmt: v => Math.round(v).toLocaleString('id-ID') + ' order'
            },
        };

        function buildMpMonth(metric) {
            const m = mpMetricMap[metric];
            const periods = [...new Set(mpMonthRaw.map(d => d.period_month))].sort();
            const platforms = [...new Set(mpMonthRaw.map(d => d.marketplace_name))];

            const datasets = platforms.map(name => {
                const slug = mpMonthRaw.find(d => d.marketplace_name === name)?.marketplace_slug || '';
                const color = MP_COLORS[slug] || '#008B8B';
                return {
                    label: name,
                    data: periods.map(p => {
                        const row = mpMonthRaw.find(d => d.marketplace_name === name && d.period_month ===
                            p);
                        return row ? parseFloat(row[m.key]) : 0;
                    }),
                    backgroundColor: color + 'cc',
                    borderColor: color,
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.6,
                };
            });

            if (mpMonthChart) mpMonthChart.destroy();
            mpMonthChart = new Chart(document.getElementById('mpMonthChart'), {
                type: 'bar',
                data: {
                    labels: periods.map(mLbl),
                    datasets
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            labels: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.dataset.label}: ${m.fmt(ctx.raw)}`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                color: '#E8F5F5'
                            },
                            ticks: {
                                callback: metric === 'orders' ? v => v : fmtJt,
                                font: {
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });
        }
        buildMpMonth('revenue');
        document.getElementById('mpMonthMetric').addEventListener('change', e => buildMpMonth(e.target.value));

        // ============================================================
        // PERIOD FILTER DROPDOWN
        // ============================================================
        const btn = document.getElementById('periodFilterBtn');
        const dropdown = document.getElementById('periodDropdown');
        const checkAll = document.getElementById('checkAll');
        const checks = document.querySelectorAll('.period-check');

        function syncCheckAll() {
            const n = document.querySelectorAll('.period-check:checked').length;
            checkAll.indeterminate = n > 0 && n < checks.length;
            checkAll.checked = n === checks.length;
        }
        syncCheckAll();
        btn.addEventListener('click', e => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        });
        document.addEventListener('click', e => {
            if (!dropdown.contains(e.target) && e.target !== btn) dropdown.style.display = 'none';
        });
        checkAll.addEventListener('change', () => checks.forEach(c => c.checked = checkAll.checked));
        checks.forEach(c => c.addEventListener('change', syncCheckAll));
    </script>
@endpush
