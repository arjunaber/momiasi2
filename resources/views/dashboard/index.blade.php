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
    $totalRevenue = (float) ($summary->total_revenue ?? 0);
    $totalAdspend = (float) ($summary->total_adspend ?? 0);
    $totalProfit = (float) ($summary->total_profit ?? 0);
    $totalOrders = (int) ($summary->total_orders ?? 0);
    $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 1) : 0;
    $roasVal = $totalAdspend > 0 ? round($totalRevenue / $totalAdspend, 2) : 0;
    $avgOrderVal = $totalOrders > 0 ? round($totalRevenue / $totalOrders) : 0;
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

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card teal">
                <div class="stat-icon teal"><i class="bi bi-coin"></i></div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">Rp {{ number_format($totalRevenue / 1000000, 1, ',', '.') }}jt</div>
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
        <div class="col-6 col-xl-3">
            <div class="stat-card green">
                <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
                <div class="stat-label">Total Profit</div>
                <div class="stat-value" style="color:var(--green);">Rp
                    {{ number_format($totalProfit / 1000000, 1, ',', '.') }}jt</div>
                <div class="stat-sub mt-1">Margin: <strong style="color:var(--green);">{{ $profitMargin }}%</strong></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card magenta">
                <div class="stat-icon magenta"><i class="bi bi-megaphone"></i></div>
                <div class="stat-label">Total Ad Spend</div>
                <div class="stat-value" style="color:var(--clr-magenta);">Rp
                    {{ number_format($totalAdspend / 1000000, 1, ',', '.') }}jt</div>
                <div class="stat-sub mt-1">ROAS: <strong style="color:var(--clr-teal);">{{ $roasVal }}×</strong></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card yellow">
                <div class="stat-icon yellow"><i class="bi bi-bag-check"></i></div>
                <div class="stat-label">Total Order</div>
                <div class="stat-value" style="color:var(--yellow);">{{ number_format($totalOrders, 0, ',', '.') }}</div>
                <div class="stat-sub mt-1">AOV: <strong style="color:var(--clr-teal);">Rp
                        {{ number_format($avgOrderVal, 0, ',', '.') }}</strong></div>
            </div>
        </div>
    </div>

    {{-- Tren Harian + Donut --}}
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
                    <select id="donutMetric" class="form-select-light" style="width:auto;font-size:11px;padding:4px 8px;">
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
                                Rp {{ number_format($totalRevenue / 1000000, 1, ',', '.') }}jt
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
                                        {{ $totalRevenue > 0 ? round(($mp->total_revenue / $totalRevenue) * 100, 1) : 0 }}%
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

    {{-- Marketplace Cards (dengan ROAS) --}}
    <div class="row g-3 mb-4">
        @foreach ($marketplaceSummary as $mp)
            @php
                $mpRoas = $mp->total_adspend > 0 ? round($mp->total_revenue / $mp->total_adspend, 2) : 0;
                $mpMargin = $mp->total_revenue > 0 ? round(($mp->total_profit / $mp->total_revenue) * 100, 1) : 0;
                $mpShare = $totalOrders > 0 ? round(($mp->total_orders / $totalOrders) * 100, 1) : 0;
                $roasClr = $mpRoas >= 4 ? 'var(--green)' : ($mpRoas >= 2 ? 'var(--yellow)' : 'var(--red)');
                $roasLbl = $mpRoas >= 4 ? 'Efisien ✓' : ($mpRoas >= 2 ? 'Cukup' : 'Perlu Review');
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
                                        style="width:{{ $totalRevenue > 0 ? round(($mp->total_revenue / $totalRevenue) * 100) : 0 }}%;background:{{ $mp->marketplace_color }};border-radius:4px;height:6px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mp-stat-label">Profit</div>
                                <div class="mp-stat-value" style="color:var(--green);">Rp
                                    {{ number_format($mp->total_profit, 0, ',', '.') }}</div>
                                <div style="font-size:11px;color:#888;margin-top:4px;">Margin {{ $mpMargin }}%</div>
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

    {{-- Grafik Bulanan + Top 5 --}}
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
                            $pShare = $totalRevenue > 0 ? round(($p->total_revenue / $totalRevenue) * 100, 1) : 0;
                            $pMargin =
                                $p->total_revenue > 0 ? round(($p->total_profit / $p->total_revenue) * 100, 1) : 0;
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

    {{-- Tabel Ringkasan Bulanan --}}
    @if ($monthlyStats->count() > 1)
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card-light">
                    <div class="card-header-light">
                        <h3 class="card-title-light"><i class="bi bi-table me-2"></i>Ringkasan per Bulan</h3>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Ad Spend</th>
                                    <th class="text-end">Profit</th>
                                    <th class="text-end">Margin</th>
                                    <th class="text-end">ROAS</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Growth</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthlyStats->sortByDesc('period_month') as $i => $ms)
                                    @php
                                        $msPrev = $monthlyStats->sortByDesc('period_month')->get($i + 1);
                                        $msGrowth =
                                            $msPrev && $msPrev->total_revenue > 0
                                                ? round(
                                                    (($ms->total_revenue - $msPrev->total_revenue) /
                                                        $msPrev->total_revenue) *
                                                        100,
                                                    1,
                                                )
                                                : null;
                                        $msRoas =
                                            $ms->total_adspend > 0
                                                ? round($ms->total_revenue / $ms->total_adspend, 2)
                                                : 0;
                                        $msMargin =
                                            $ms->total_revenue > 0
                                                ? round(($ms->total_profit / $ms->total_revenue) * 100, 1)
                                                : 0;
                                    @endphp
                                    <tr>
                                        <td><strong
                                                style="color:var(--clr-teal);">{{ \Carbon\Carbon::createFromFormat('Y-m', $ms->period_month)->isoFormat('MMMM YYYY') }}</strong>
                                        </td>
                                        <td class="text-end">Rp {{ number_format($ms->total_revenue, 0, ',', '.') }}</td>
                                        <td class="text-end" style="color:var(--clr-magenta);">Rp
                                            {{ number_format($ms->total_adspend, 0, ',', '.') }}</td>
                                        <td class="text-end" style="color:var(--green);">Rp
                                            {{ number_format($ms->total_profit, 0, ',', '.') }}</td>
                                        <td class="text-end"><span
                                                style="color:{{ $msMargin >= 20 ? 'var(--green)' : ($msMargin >= 10 ? 'var(--yellow)' : 'var(--red)') }}">{{ $msMargin }}%</span>
                                        </td>
                                        <td class="text-end"><span
                                                style="color:{{ $msRoas >= 4 ? 'var(--green)' : ($msRoas >= 2 ? 'var(--yellow)' : 'var(--red)') }};font-weight:700;">{{ $msRoas }}×</span>
                                        </td>
                                        <td class="text-end">{{ number_format($ms->total_orders, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            @if ($msGrowth !== null)
                                                <span class="{{ $msGrowth >= 0 ? 'badge-up' : 'badge-down' }}">
                                                    <i
                                                        class="bi bi-arrow-{{ $msGrowth >= 0 ? 'up' : 'down' }}"></i>{{ abs($msGrowth) }}%
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

    {{-- Insights --}}
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
        const totRevenue = {{ $totalRevenue }};
        const totOrders = {{ $totalOrders }};
        const totAdspend = {{ $totalAdspend }};
        const totProfit = {{ $totalProfit }};

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

        // CHART 1: Tren harian — garis terpisah, fill:false
        (() => {
            const platforms = [...new Set(dailyTrend.map(d => d.marketplace_name))];
            const allDates = [...new Set(dailyTrend.map(d => d.transaction_date))].sort();
            const palette = {
                shopee: '#EE4D2D',
                tiktok: '#46B8A7'
            };

            // Clean dates for display - remove T00:00:00
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

        // CHART 2: Donut dengan switch metrik
        let donutChart;
        const donutMap = {
            revenue: {
                values: mpSummary.map(m => m.total_revenue),
                label: 'Revenue',
                total: totRevenue,
                fmt: fmtRp
            },
            orders: {
                values: mpSummary.map(m => m.total_orders),
                label: 'Orders',
                total: totOrders,
                fmt: v => Math.round(v) + ' order'
            },
            adspend: {
                values: mpSummary.map(m => m.total_adspend),
                label: 'Ad Spend',
                total: totAdspend,
                fmt: fmtRp
            },
            profit: {
                values: mpSummary.map(m => m.total_profit),
                label: 'Profit',
                total: totProfit,
                fmt: fmtRp
            },
        };
        const MP_COLORS = {
            shopee: '#EE4D2D',
            tiktok: '#46B8A7'
        };

        function buildDonut(metric) {
            const d = donutMap[metric];
            if (donutChart) donutChart.destroy();
            donutChart = new Chart(document.getElementById('donutChart'), {
                type: 'doughnut',
                data: {
                    labels: mpSummary.map(m => m.marketplace_name),
                    datasets: [{
                        data: d.values,
                        backgroundColor: mpSummary.map(m => MP_COLORS[m.marketplace_slug] || '#008B8B'),
                        borderWidth: 0,
                        hoverOffset: 8
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx =>
                                    ` ${ctx.label}: ${d.fmt(ctx.raw)} (${d.total>0?((ctx.raw/d.total)*100).toFixed(1):0}%)`
                            }
                        }
                    }
                }
            });

            document.getElementById('donutCenterLabel').textContent = 'Total ' + d.label;
            const tv = d.total;
            document.getElementById('donutCenterValue').textContent =
                metric === 'orders' ? tv + ' order' : 'Rp ' + (tv / 1000000).toFixed(1) + 'jt';

            document.querySelectorAll('.donut-pct').forEach(el => {
                const mp = mpSummary.find(m => m.marketplace_slug === el.dataset.slug);
                if (!mp) return;
                const idx = mpSummary.indexOf(mp);
                const pct = d.total > 0 ? ((d.values[idx] / d.total) * 100).toFixed(1) : '0';
                el.textContent = pct + '%';
                el.nextElementSibling.textContent = d.label;
            });
        }
        buildDonut('revenue');
        document.getElementById('donutMetric').addEventListener('change', e => buildDonut(e.target.value));

        // CHART 3: Grouped bar platform × bulan
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
                fmt: v => Math.round(v) + ' order'
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

        // Period filter dropdown
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
