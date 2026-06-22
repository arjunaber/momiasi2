<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // ========== GET AVAILABLE PERIODS ==========
        $availablePeriods = Transaction::selectRaw('period_month')
            ->groupBy('period_month')
            ->orderByDesc('period_month')
            ->pluck('period_month')
            ->toArray();

        // ========== SELECTED PERIODS ==========
        $selectedPeriods = $request->filled('periods')
            ? collect($request->input('periods'))->sort()->values()->toArray()
            : $availablePeriods;

        // ========== IF NO DATA ==========
        if (empty($selectedPeriods)) {
            return view('dashboard.index', [
                'availablePeriods' => $availablePeriods,
                'selectedPeriods' => [],
                'marketplaces' => Marketplace::active()->get(),
                'summary' => null,
                'marketplaceSummary' => collect(),
                'monthlyStats' => collect(),
                'marketplaceMonthData' => collect(),
                'top5Products' => collect(),
                'dailyTrend' => collect(),
                'insights' => [],
                'totalRevenue' => 0,
                'totalProfit' => 0,
                'totalAdspend' => 0,
                'totalOrdersAll' => 0,
                'totalCost' => 0,
                'avgProfitMargin' => 0,
                'orderStatusData' => collect(),
                'completedOrders' => 0,
                'totalRevenueCompleted' => 0,
                'totalRevenueAll' => 0,
            ]);
        }

        $marketplaces = Marketplace::active()->get();

        // ========== BASE QUERIES ==========
        $baseAll = fn() => Transaction::whereIn('period_month', $selectedPeriods);
        $baseCompleted = fn() => Transaction::completed()->whereIn('period_month', $selectedPeriods);

        // ========== 1. ORDER STATUS SUMMARY ==========
        $orderStatusRaw = $baseAll()
            ->selectRaw('
                status,
                COUNT(*) as total_orders,
                SUM(revenue) as total_revenue,
                SUM(profit) as total_profit
            ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // ========== 2. SUMMARY ALL STATUS ==========
        $summaryAll = $baseAll()->selectRaw('
            SUM(revenue) AS total_revenue_all,
            SUM(advertising_spend) AS total_adspend_all,
            SUM(profit) AS total_profit_all,
            SUM(quantity) AS total_qty_all,
            SUM(total_cost) AS total_cost_all,
            AVG(profit_margin) AS avg_profit_margin_all,
            COUNT(*) AS total_orders_all
        ')->first();

        // ========== 3. SUMMARY COMPLETED ONLY ==========
        $summaryCompleted = $baseCompleted()->selectRaw('
            SUM(revenue) AS total_revenue_completed,
            SUM(profit) AS total_profit_completed,
            SUM(quantity) AS total_qty_completed,
            SUM(total_cost) AS total_cost_completed,
            AVG(profit_margin) AS avg_profit_margin_completed,
            COUNT(*) AS total_orders_completed
        ')->first();

        // ========== 4. EXTRACT METRICS ==========
        // 🔥 REVENUE - DUA VERSI
        $totalRevenueAll = (float)($summaryAll->total_revenue_all ?? 0);
        $totalRevenueCompleted = (float)($summaryCompleted->total_revenue_completed ?? 0);

        // 🔥 PROFIT - DUA VERSI
        $totalProfitAll = (float)($summaryAll->total_profit_all ?? 0);
        $totalProfitCompleted = (float)($summaryCompleted->total_profit_completed ?? 0);

        // 🔥 METRIK LAINNYA
        $totalAdspend = (float)($summaryAll->total_adspend_all ?? 0);
        $totalCost = (float)($summaryAll->total_cost_all ?? 0);
        $totalOrdersAll = (int)($summaryAll->total_orders_all ?? 0);
        $totalOrdersCompleted = (int)($summaryCompleted->total_orders_completed ?? 0);
        $avgProfitMarginAll = (float)($summaryAll->avg_profit_margin_all ?? 0);
        $avgProfitMarginCompleted = (float)($summaryCompleted->avg_profit_margin_completed ?? 0);

        // ========== 5. ORDER STATUS BREAKDOWN ==========
        $statusColors = [
            'completed' => '#22c55e',
            'cancelled' => '#ef4444',
            'returned' => '#f59e0b',
            'pending' => '#3b82f6'
        ];

        $statusLabels = [
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'returned' => 'Dikembalikan',
            'pending' => 'Pending'
        ];

        $statusIcons = [
            'completed' => 'bi-check-circle-fill',
            'cancelled' => 'bi-x-circle-fill',
            'returned' => 'bi-arrow-return-left',
            'pending' => 'bi-clock-fill'
        ];

        $orderStatusData = collect(['completed', 'cancelled', 'returned', 'pending'])
            ->map(function ($status) use ($orderStatusRaw, $statusLabels, $statusColors, $statusIcons, $totalOrdersAll) {
                $data = $orderStatusRaw->get($status);
                $count = $data ? (int)$data->total_orders : 0;
                $percentage = $totalOrdersAll > 0 ? round(($count / $totalOrdersAll) * 100, 1) : 0;

                return (object)[
                    'status' => $status,
                    'label' => $statusLabels[$status] ?? ucfirst($status),
                    'color' => $statusColors[$status] ?? '#6b7280',
                    'icon' => $statusIcons[$status] ?? 'bi-circle',
                    'count' => $count,
                    'percentage' => $percentage,
                    'revenue' => $data ? (float)$data->total_revenue : 0,
                    'profit' => $data ? (float)$data->total_profit : 0
                ];
            });

        // ========== 6. MARKETPLACE SUMMARY ==========
        $marketplaceSummaryRaw = $baseAll()
            ->join('marketplaces', 'transactions.marketplace_id', '=', 'marketplaces.id')
            ->whereNotNull('marketplaces.id')
            ->groupBy(
                'transactions.marketplace_id',
                'marketplaces.name',
                'marketplaces.slug',
                'marketplaces.color'
            )
            ->selectRaw('
                transactions.marketplace_id, 
                marketplaces.name AS marketplace_name,
                marketplaces.slug AS marketplace_slug, 
                marketplaces.color AS marketplace_color,
                SUM(transactions.revenue) AS total_revenue,
                SUM(transactions.advertising_spend) AS total_adspend,
                SUM(transactions.profit) AS total_profit, 
                SUM(transactions.total_cost) AS total_cost,
                SUM(transactions.quantity) AS total_qty,
                AVG(transactions.profit_margin) AS avg_profit_margin,
                COUNT(*) AS total_orders
            ')->get();

        // ========== 7. PROCESS MARKETPLACE SUMMARY ==========
        $marketplaceSummary = $marketplaceSummaryRaw->map(function ($item) {
            $revenue = (float)($item->total_revenue ?? 0);
            $profit = (float)($item->total_profit ?? 0);
            $adspend = (float)($item->total_adspend ?? 0);
            $orders = (int)($item->total_orders ?? 0);

            $item->profit_margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
            $item->roas = $adspend > 0 ? round($revenue / $adspend, 2) : 0;
            $item->adspend_ratio = $revenue > 0 ? round(($adspend / $revenue) * 100, 2) : 0;
            $item->total_orders = $orders;

            return $item;
        });

        // ========== 8. VERIFY DATA CONSISTENCY ==========
        $totalRevenueFromMarketplace = $marketplaceSummary->sum('total_revenue');
        $totalOrdersFromMarketplace = $marketplaceSummary->sum('total_orders');
        $totalAdspendFromMarketplace = $marketplaceSummary->sum('total_adspend');
        $totalProfitFromMarketplace = $marketplaceSummary->sum('total_profit');

        // 🔥 GUNAKAN DATA DARI MARKETPLACE SUMMARY UNTUK KONSISTENSI
        $totalRevenue = $totalRevenueFromMarketplace > 0 ? $totalRevenueFromMarketplace : $totalRevenueAll;
        $totalProfit = $totalProfitFromMarketplace > 0 ? $totalProfitFromMarketplace : $totalProfitAll;
        $totalOrdersAll = $totalOrdersFromMarketplace > 0 ? $totalOrdersFromMarketplace : $totalOrdersAll;

        // ========== 9. MARKETPLACE ORDERS PER STATUS (Optional) ==========
        $marketplaceOrdersByStatus = $baseAll()
            ->join('marketplaces', 'transactions.marketplace_id', '=', 'marketplaces.id')
            ->groupBy('transactions.marketplace_id', 'marketplaces.name', 'transactions.status')
            ->selectRaw('
                transactions.marketplace_id,
                marketplaces.name AS marketplace_name,
                transactions.status,
                COUNT(*) AS total_orders
            ')->get()
            ->groupBy('marketplace_id');

        // ========== 10. MONTHLY STATS ==========
        $monthlyStatsRaw = $baseAll()
            ->groupBy('period_month')
            ->selectRaw('
                period_month, 
                SUM(revenue) AS total_revenue,
                SUM(advertising_spend) AS total_adspend,
                SUM(profit) AS total_profit, 
                SUM(total_cost) AS total_cost,
                SUM(quantity) AS total_qty,
                AVG(profit_margin) AS avg_profit_margin,
                COUNT(*) AS total_orders
            ')
            ->orderBy('period_month')
            ->get();

        $monthlyStats = $monthlyStatsRaw->map(function ($item) {
            $revenue = (float)($item->total_revenue ?? 0);
            $profit = (float)($item->total_profit ?? 0);
            $adspend = (float)($item->total_adspend ?? 0);
            $orders = (int)($item->total_orders ?? 0);

            $item->profit_margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
            $item->roas = $adspend > 0 ? round($revenue / $adspend, 2) : 0;
            $item->adspend_ratio = $revenue > 0 ? round(($adspend / $revenue) * 100, 2) : 0;
            $item->total_orders = $orders;

            return $item;
        });

        // ========== 11. MARKETPLACE MONTHLY DATA ==========
        $marketplaceMonthData = $baseAll()
            ->join('marketplaces', 'transactions.marketplace_id', '=', 'marketplaces.id')
            ->groupBy(
                'transactions.marketplace_id',
                'marketplaces.name',
                'marketplaces.slug',
                'marketplaces.color',
                'transactions.period_month'
            )
            ->selectRaw('
                transactions.marketplace_id, 
                marketplaces.name AS marketplace_name,
                marketplaces.slug AS marketplace_slug, 
                marketplaces.color AS marketplace_color,
                transactions.period_month, 
                SUM(transactions.revenue) AS total_revenue,
                SUM(transactions.advertising_spend) AS total_adspend, 
                SUM(transactions.profit) AS total_profit,
                SUM(transactions.total_cost) AS total_cost,
                COUNT(*) AS total_orders,
                AVG(transactions.profit_margin) AS avg_profit_margin
            ')
            ->orderBy('transactions.period_month')
            ->get()
            ->map(function ($item) {
                $revenue = (float)($item->total_revenue ?? 0);
                $profit = (float)($item->total_profit ?? 0);
                $adspend = (float)($item->total_adspend ?? 0);

                $item->profit_margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
                $item->roas = $adspend > 0 ? round($revenue / $adspend, 2) : 0;

                return $item;
            });

        // ========== 12. TOP 5 PRODUCTS ==========
        $top5Products = $baseAll()
            ->join('products', 'transactions.product_id', '=', 'products.id')
            ->whereNotNull('products.id')
            ->groupBy('transactions.product_id', 'products.name', 'products.sku', 'products.category')
            ->selectRaw('
                transactions.product_id, 
                products.name AS product_name, 
                products.sku AS product_sku,
                products.category AS product_category, 
                SUM(transactions.revenue) AS total_revenue,
                SUM(transactions.quantity) AS total_qty, 
                SUM(transactions.profit) AS total_profit,
                SUM(transactions.advertising_spend) AS total_adspend,
                COUNT(*) AS total_orders,
                AVG(transactions.profit_margin) AS avg_profit_margin
            ')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $revenue = (float)($item->total_revenue ?? 0);
                $profit = (float)($item->total_profit ?? 0);
                $adspend = (float)($item->total_adspend ?? 0);

                $item->profit_margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
                $item->roas = $adspend > 0 ? round($revenue / $adspend, 2) : 0;

                return $item;
            });

        // ========== 13. DAILY TREND ==========
        $dailyTrend = $baseAll()
            ->join('marketplaces', 'transactions.marketplace_id', '=', 'marketplaces.id')
            ->groupBy(
                'transaction_date',
                'transactions.marketplace_id',
                'marketplaces.name',
                'marketplaces.slug',
                'marketplaces.color'
            )
            ->selectRaw('
                transaction_date, 
                transactions.marketplace_id, 
                marketplaces.name AS marketplace_name,
                marketplaces.slug AS marketplace_slug, 
                marketplaces.color AS marketplace_color,
                SUM(transactions.revenue) AS daily_revenue, 
                SUM(transactions.profit) AS daily_profit, 
                SUM(transactions.advertising_spend) AS daily_adspend,
                COUNT(*) AS daily_orders
            ')
            ->orderBy('transaction_date')
            ->get();

        // ========== 14. INSIGHTS ==========
        $insights = $this->generateInsights(
            $marketplaceSummary,
            $monthlyStats,
            $top5Products,
            $totalRevenue,
            $totalProfit,
            $totalAdspend,
            $totalOrdersAll,
            $totalCost,
            $orderStatusData,
            $totalRevenueCompleted,
            $totalOrdersCompleted
        );

        // ========== 15. CALCULATE METRICS FOR VIEW ==========
        $profitMarginCompleted = $totalRevenueCompleted > 0
            ? round(($totalProfitCompleted / $totalRevenueCompleted) * 100, 1)
            : 0;

        $roasVal = $totalAdspend > 0 ? round($totalRevenue / $totalAdspend, 2) : 0;
        $avgOrderVal = $totalOrdersAll > 0 ? round($totalRevenue / $totalOrdersAll) : 0;
        $adspendRatio = $totalRevenue > 0 ? round(($totalAdspend / $totalRevenue) * 100, 2) : 0;

        // ========== 16. RETURN VIEW ==========
        return view('dashboard.index', compact(
            'availablePeriods',
            'selectedPeriods',
            'marketplaces',
            'marketplaceSummary',
            'monthlyStats',
            'marketplaceMonthData',
            'top5Products',
            'dailyTrend',
            'insights',
            'orderStatusData',
            // Metrik utama
            'totalRevenue',
            'totalRevenueCompleted',
            'totalRevenueAll',
            'totalProfit',
            'totalProfitCompleted',
            'totalAdspend',
            'totalCost',
            'totalOrdersAll',
            'totalOrdersCompleted',
            'avgProfitMarginAll',
            'avgProfitMarginCompleted',
            'profitMarginCompleted',
            'roasVal',
            'avgOrderVal',
            'adspendRatio'
        ));
    }

    /**
     * 🔥 GENERATE INSIGHTS - PROFESSIONAL VERSION
     * Tanpa emoji dan format berlebihan, fokus pada data dan rekomendasi bisnis
     */
    private function generateInsights(
        $marketplaceSummary,
        $monthlyStats,
        $top5Products,
        $totalRevenue,
        $totalProfit,
        $totalAdspend,
        $totalOrdersAll,
        $totalCost,
        $orderStatusData,
        $totalRevenueCompleted,
        $totalOrdersCompleted
    ): array {
        $insights = [];

        // ================================================================
        // ① ORDER STATUS BREAKDOWN
        // ================================================================
        if ($totalOrdersAll > 0) {
            $completed = $orderStatusData->firstWhere('status', 'completed');
            $cancelled = $orderStatusData->firstWhere('status', 'cancelled');
            $returned = $orderStatusData->firstWhere('status', 'returned');
            $pending = $orderStatusData->firstWhere('status', 'pending');

            $completedPct = $completed ? $completed->percentage : 0;
            $cancelledPct = $cancelled ? $cancelled->percentage : 0;
            $returnedPct = $returned ? $returned->percentage : 0;
            $pendingPct = $pending ? $pending->percentage : 0;

            $completionRate = $totalOrdersAll > 0 ? round(($totalOrdersCompleted / $totalOrdersAll) * 100, 1) : 0;
            $cancellationRate = $cancelledPct;
            $returnRate = $returnedPct;

            $text = "Total orders: " . number_format($totalOrdersAll, 0, ',', '.') . ". ";
            $text .= "Status: Selesai {$completedPct}% (completion rate {$completionRate}%), ";
            $text .= "Pending {$pendingPct}%, ";
            $text .= "Dibatalkan {$cancelledPct}%, ";
            $text .= "Dikembalikan {$returnedPct}%. ";

            $severity = 'success';
            if ($cancellationRate > 15) {
                $severity = 'danger';
                $text .= "Tingkat pembatalan tinggi ({$cancellationRate}%) — periksa stok, harga, atau proses checkout.";
            } elseif ($cancellationRate > 8) {
                $severity = 'warning';
                $text .= "Tingkat pembatalan {$cancellationRate}% — optimasi listing dan proses pembayaran.";
            } elseif ($returnRate > 5) {
                $severity = 'warning';
                $text .= "Tingkat pengembalian {$returnRate}% — periksa kualitas produk dan deskripsi.";
            } else {
                $text .= "Tingkat penyelesaian tinggi ({$completionRate}%) — operasional berjalan baik.";
            }

            // Tambahkan info revenue
            if ($totalRevenueCompleted > 0 && $totalRevenue > 0) {
                $revenueFromCompleted = round(($totalRevenueCompleted / $totalRevenue) * 100, 1);
                $text .= " Pendapatan dari order selesai: {$revenueFromCompleted}% dari total revenue.";
            }

            $insights[] = [
                'icon' => 'bi-clipboard-check',
                'color' => $this->getSeverityColor($severity),
                'border' => $this->getSeverityColor($severity),
                'label' => 'Status Order & Completion Rate',
                'text' => $text,
                'severity' => $severity
            ];
        }

        // ================================================================
        // ② DOMINASI PLATFORM
        // ================================================================
        if ($marketplaceSummary->count() >= 2) {
            $sorted = $marketplaceSummary->sortByDesc('total_revenue');
            $top = $sorted->first();
            $bottom = $sorted->last();

            $topShare = $totalRevenue > 0 ? round(($top->total_revenue / $totalRevenue) * 100, 1) : 0;
            $bottomShare = $totalRevenue > 0 ? round(($bottom->total_revenue / $totalRevenue) * 100, 1) : 0;

            $topOrders = (int)($top->total_orders ?? 0);
            $bottomOrders = (int)($bottom->total_orders ?? 0);
            $topOrdersShare = $totalOrdersAll > 0 ? round(($topOrders / $totalOrdersAll) * 100, 1) : 0;
            $bottomOrdersShare = $totalOrdersAll > 0 ? round(($bottomOrders / $totalOrdersAll) * 100, 1) : 0;

            $topAds = (float)($top->total_adspend ?? 0);
            $bottomAds = (float)($bottom->total_adspend ?? 0);
            $topAdspendRatio = $top->total_revenue > 0 ? round(($topAds / $top->total_revenue) * 100, 2) : 0;
            $bottomAdspendRatio = $bottom->total_revenue > 0 ? round(($bottomAds / $bottom->total_revenue) * 100, 2) : 0;

            $topRoas = $topAds > 0 ? round($top->total_revenue / $topAds, 2) : 0;
            $bottomRoas = $bottomAds > 0 ? round($bottom->total_revenue / $bottomAds, 2) : 0;

            $topMargin = $top->total_revenue > 0 ? round(($top->total_profit / $top->total_revenue) * 100, 1) : 0;
            $bottomMargin = $bottom->total_revenue > 0 ? round(($bottom->total_profit / $bottom->total_revenue) * 100, 1) : 0;

            $text = "Revenue: {$top->marketplace_name} (Rp " . number_format($top->total_revenue, 0, ',', '.') . ", {$topShare}%) vs {$bottom->marketplace_name} (Rp " . number_format($bottom->total_revenue, 0, ',', '.') . ", {$bottomShare}%). ";
            $text .= "Orders: {$top->marketplace_name} {$topOrdersShare}% vs {$bottom->marketplace_name} {$bottomOrdersShare}%. ";

            if ($topAds > 0 || $bottomAds > 0) {
                $text .= "Biaya iklan: {$top->marketplace_name} Rp " . number_format($topAds, 0, ',', '.') . " ({$topAdspendRatio}% dari revenue, ROAS {$topRoas}x), ";
                $text .= "{$bottom->marketplace_name} Rp " . number_format($bottomAds, 0, ',', '.') . " ({$bottomAdspendRatio}% dari revenue, ROAS {$bottomRoas}x). ";

                if ($topRoas > 0 && $bottomRoas > 0) {
                    $betterRoas = $topRoas >= $bottomRoas ? $top->marketplace_name : $bottom->marketplace_name;
                    $text .= "{$betterRoas} lebih efisien secara iklan. ";
                }
            }

            $text .= "Margin: {$top->marketplace_name} {$topMargin}%, {$bottom->marketplace_name} {$bottomMargin}%. ";

            if ($topShare >= 65) {
                $severity = 'warning';
                $text .= "Ketergantungan tinggi pada {$top->marketplace_name} ({$topShare}%) — diversifikasi ke {$bottom->marketplace_name} perlu diprioritaskan.";
            } elseif ($topShare >= 50) {
                $severity = 'info';
                $text .= "{$top->marketplace_name} unggul ({$topShare}%), tapi masih sehat. Alokasikan produk terlaris ke {$top->marketplace_name} dan coba produk baru di {$bottom->marketplace_name}.";
            } else {
                $severity = 'success';
                $text .= "Distribusi seimbang ({$topShare}% : {$bottomShare}%) — strategi multi-platform berjalan optimal.";
            }

            $insights[] = [
                'icon' => 'bi-arrow-left-right',
                'color' => $this->getSeverityColor($severity),
                'border' => $this->getSeverityColor($severity),
                'label' => 'Dominasi & Distribusi Platform',
                'text' => $text,
                'severity' => $severity
            ];
        }

        // ================================================================
        // ③ TREN PERTUMBUHAN BULANAN
        // ================================================================
        if ($monthlyStats->count() >= 2) {
            $ms = $monthlyStats->sortBy('period_month')->values();
            $last = $ms->last();
            $prev = $ms->get($ms->count() - 2);

            $revGrowth = $prev->total_revenue > 0 ? round((($last->total_revenue - $prev->total_revenue) / $prev->total_revenue) * 100, 1) : 0;
            $ordGrowth = $prev->total_orders > 0 ? round((($last->total_orders - $prev->total_orders) / $prev->total_orders) * 100, 1) : 0;
            $profGrowth = $prev->total_profit > 0 ? round((($last->total_profit - $prev->total_profit) / $prev->total_profit) * 100, 1) : 0;
            $adspendGrowth = $prev->total_adspend > 0 ? round((($last->total_adspend - $prev->total_adspend) / $prev->total_adspend) * 100, 1) : 0;

            $lastLabel = Carbon::parse($last->period_month . '-01')->isoFormat('MMM Y');
            $prevLabel = Carbon::parse($prev->period_month . '-01')->isoFormat('MMM Y');

            $text = "Revenue: Rp " . number_format($last->total_revenue, 0, ',', '.') . " ({$revGrowth}% dari {$prevLabel}). ";
            $text .= "Orders: " . number_format($last->total_orders) . " ({$ordGrowth}%). ";
            $text .= "Profit: Rp " . number_format($last->total_profit, 0, ',', '.') . " ({$profGrowth}%). ";

            if ($last->total_adspend > 0) {
                $lastRoas = round($last->total_revenue / $last->total_adspend, 2);
                $text .= "Biaya iklan: Rp " . number_format($last->total_adspend, 0, ',', '.') . " ({$adspendGrowth}%), ROAS {$lastRoas}x. ";
                $text .= "Margin: " . round($last->profit_margin, 1) . "%.";
            }

            if ($revGrowth <= -15) {
                $severity = 'danger';
                $text .= "Revenue turun signifikan ({$revGrowth}%) — periksa perubahan algoritma, stok, atau kompetitor.";
            } elseif ($revGrowth < 0) {
                $severity = 'warning';
                $text .= "Revenue sedikit turun ({$revGrowth}%). " . ($ordGrowth >= 0 ? "Order stabil — kemungkinan penurunan AOV." : "Cek traffic dan konversi listing.");
            } elseif ($revGrowth >= 25) {
                $severity = 'success';
                $text .= "Pertumbuhan luar biasa! " . ($profGrowth >= $revGrowth - 5 ? "Profit tumbuh seiring — bisnis sangat sehat." : "Profit tumbuh lebih lambat — audit biaya.");
            } else {
                $severity = 'info';
                $text .= "Pertumbuhan stabil. Dorong dengan bundling produk Momiasi + Little Mommies.";
            }

            $insights[] = [
                'icon' => $revGrowth >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow',
                'color' => $this->getSeverityColor($severity),
                'border' => $this->getSeverityColor($severity),
                'label' => 'Tren Pertumbuhan Bulanan',
                'text' => $text,
                'severity' => $severity
            ];
        }

        // ================================================================
        // ④ ROAS & EFISIENSI IKLAN
        // ================================================================
        if ($totalAdspend > 0) {
            $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;
            $roas = $totalAdspend > 0 ? round($totalRevenue / $totalAdspend, 2) : 0;
            $adspendRatio = $totalRevenue > 0 ? round(($totalAdspend / $totalRevenue) * 100, 2) : 0;

            $text = "Total Revenue: Rp " . number_format($totalRevenue, 0, ',', '.') . ". ";
            $text .= "Total Biaya Iklan: Rp " . number_format($totalAdspend, 0, ',', '.') . ". ";
            $text .= "ROAS: {$roas}x. ";
            $text .= "Biaya iklan: {$adspendRatio}% dari revenue. ";
            $text .= "Margin Bersih: {$profitMargin}%.";

            if ($roas >= 6) {
                $severity = 'success';
                $text .= " ROAS sangat baik. Pertimbangkan untuk menambah anggaran iklan produk unggulan.";
            } elseif ($roas >= 4) {
                $severity = 'success';
                $text .= " ROAS sehat (benchmark 4x). Lakukan A/B testing konten untuk optimasi.";
            } elseif ($roas >= 2.5) {
                $severity = 'info';
                $text .= " ROAS masih profitable. Fokuskan budget pada produk dengan konversi tertinggi.";
            } elseif ($roas >= 1) {
                $severity = 'warning';
                $text .= " ROAS rendah — iklan hampir tidak balik modal. Evaluasi kampanye dengan ROAS di bawah 2x.";
            } else {
                $severity = 'danger';
                $text .= " ROAS di bawah 1x — iklan merugi. Hentikan kampanye berbayar dan lakukan audit.";
            }

            // Detail per marketplace
            if ($marketplaceSummary->count() >= 2) {
                $adsDetails = $marketplaceSummary->map(function ($m) {
                    $adspend = (float)($m->total_adspend ?? 0);
                    $revenue = (float)($m->total_revenue ?? 0);
                    $roas = $adspend > 0 ? round($revenue / $adspend, 2) : 0;
                    $ratio = $revenue > 0 ? round(($adspend / $revenue) * 100, 2) : 0;
                    return "{$m->marketplace_name}: Rp " . number_format($adspend, 0, ',', '.') . " ({$ratio}%), ROAS {$roas}x";
                })->implode(' | ');
                $text .= " Detail per platform: {$adsDetails}";
            }

            $insights[] = [
                'icon' => 'bi-lightning-charge',
                'color' => $this->getSeverityColor($severity),
                'border' => $this->getSeverityColor($severity),
                'label' => 'Efisiensi Iklan & ROAS',
                'text' => $text,
                'severity' => $severity
            ];
        }

        // ================================================================
        // ⑤ PROFIT MARGIN & STRUKTUR BIAYA
        // ================================================================
        if ($totalRevenue > 0) {
            $profitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;
            $adspendRatio = $totalRevenue > 0 ? round(($totalAdspend / $totalRevenue) * 100, 2) : 0;
            $costRatio = $totalRevenue > 0 ? round(($totalCost / $totalRevenue) * 100, 2) : 0;

            $text = "Margin Bersih: {$profitMargin}%. ";
            $text .= "Revenue: Rp " . number_format($totalRevenue, 0, ',', '.') . ". ";
            $text .= "Total Biaya: Rp " . number_format($totalCost, 0, ',', '.') . " ({$costRatio}% dari revenue). ";
            $text .= "Profit: Rp " . number_format($totalProfit, 0, ',', '.') . ". ";
            $text .= "Biaya iklan: {$adspendRatio}% dari revenue.";

            if ($profitMargin >= 30) {
                $severity = 'success';
                $text .= " Margin sangat sehat. Bisnis memiliki buffer yang baik untuk investasi.";
            } elseif ($profitMargin >= 20) {
                $severity = 'success';
                $text .= " Margin sehat. Pertahankan dengan kontrol HPP dan biaya iklan.";
            } elseif ($profitMargin >= 10) {
                $severity = 'info';
                $text .= " Margin moderat. Evaluasi biaya iklan dan HPP untuk meningkatkan profit.";
            } elseif ($profitMargin >= 5) {
                $severity = 'warning';
                $text .= " Margin tipis. Negosiasi HPP atau naikkan harga jual.";
            } else {
                $severity = 'danger';
                $text .= " Margin sangat rendah. Audit biaya operasional dan strategi pricing.";
            }

            // Margin per platform
            if ($marketplaceSummary->count() >= 2) {
                $marginDetails = $marketplaceSummary->map(function ($m) {
                    $revenue = (float)($m->total_revenue ?? 0);
                    $profit = (float)($m->total_profit ?? 0);
                    $margin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;
                    return "{$m->marketplace_name}: {$margin}%";
                })->implode(' | ');
                $text .= " Margin per platform: {$marginDetails}";
            }

            $insights[] = [
                'icon' => 'bi-pie-chart',
                'color' => $this->getSeverityColor($severity),
                'border' => $this->getSeverityColor($severity),
                'label' => 'Profit Margin & Struktur Biaya',
                'text' => $text,
                'severity' => $severity
            ];
        }

        // ================================================================
        // ⑥ KONSENTRASI PRODUK & PORTOFOLIO
        // ================================================================
        if ($top5Products->count() > 0 && $totalRevenue > 0) {
            $top1 = $top5Products->first();
            $top1Share = round(($top1->total_revenue / $totalRevenue) * 100, 1);
            $top3Share = round(($top5Products->take(3)->sum('total_revenue') / $totalRevenue) * 100, 1);
            $top5Share = round(($top5Products->sum('total_revenue') / $totalRevenue) * 100, 1);
            $top1Margin = $top1->total_revenue > 0 ? round(($top1->total_profit / $top1->total_revenue) * 100, 1) : 0;

            $text = "Top 1: \"{$top1->product_name}\" (Rp " . number_format($top1->total_revenue, 0, ',', '.') . ", {$top1Share}% dari revenue). ";
            $text .= "Top 3 produk menyumbang {$top3Share}% revenue. ";
            $text .= "Top 5 produk menyumbang {$top5Share}% revenue. ";
            $text .= "Margin produk terlaris: {$top1Margin}%.";

            $top1Adspend = (float)($top1->total_adspend ?? 0);
            if ($top1Adspend > 0) {
                $top1Roas = round($top1->total_revenue / $top1Adspend, 2);
                $top1AdRatio = $top1->total_revenue > 0 ? round(($top1Adspend / $top1->total_revenue) * 100, 2) : 0;
                $text .= " Biaya iklan produk ini: Rp " . number_format($top1Adspend, 0, ',', '.') . " ({$top1AdRatio}% dari revenue, ROAS {$top1Roas}x).";
            }

            if ($top1Share >= 40) {
                $severity = 'warning';
                $text .= " Konsentrasi tinggi pada 1 produk ({$top1Share}%) — risiko jika produk ini bermasalah. Kembangkan produk pelengkap.";
            } elseif ($top3Share >= 75) {
                $severity = 'info';
                $text .= " Top 3 produk mendominasi ({$top3Share}%). Kategori " . ($top1->product_category ?? '') . " unggul — pertimbangkan ekspansi lini.";
            } elseif ($top5Share >= 90) {
                $severity = 'info';
                $text .= " Top 5 produk menyumbang {$top5Share}% revenue — portofolio cukup terkonsentrasi.";
            } else {
                $severity = 'success';
                $text .= " Portofolio sehat — tidak ada ketergantungan berlebihan pada satu produk.";
            }

            if ($top1Margin < 15) {
                $text .= " Margin produk terlaris rendah ({$top1Margin}%) — evaluasi harga jual atau HPP.";
            } elseif ($top1Margin >= 35) {
                $text .= " Margin produk terlaris tinggi ({$top1Margin}%) — pertahankan!";
            }

            $insights[] = [
                'icon' => 'bi-box-seam',
                'color' => $this->getSeverityColor($severity),
                'border' => $this->getSeverityColor($severity),
                'label' => 'Konsentrasi Portofolio Produk',
                'text' => $text,
                'severity' => $severity
            ];
        }

        // ================================================================
        // ⑦ AVERAGE ORDER VALUE (AOV)
        // ================================================================
        if ($totalOrdersAll > 0 && $marketplaceSummary->count() >= 2) {
            $overallAov = round($totalRevenue / $totalOrdersAll, 0);

            $aovs = $marketplaceSummary->map(function ($m) {
                $orders = (int)($m->total_orders ?? 0);
                $revenue = (float)($m->total_revenue ?? 0);
                return [
                    'name' => $m->marketplace_name,
                    'aov' => $orders > 0 ? round($revenue / $orders, 0) : 0,
                    'orders' => $orders,
                    'revenue' => $revenue
                ];
            })->filter(function ($item) {
                return $item['aov'] > 0;
            })->sortByDesc('aov')->values();

            if ($aovs->count() >= 2) {
                $hi = $aovs->first();
                $lo = $aovs->last();
                $gap = $lo['aov'] > 0 ? round((($hi['aov'] - $lo['aov']) / $lo['aov']) * 100, 1) : 0;

                $text = "AOV Rata-rata: Rp " . number_format($overallAov, 0, ',', '.') . ". ";
                $text .= "{$hi['name']}: Rp " . number_format($hi['aov'], 0, ',', '.') . " ({$hi['orders']} orders), ";
                $text .= "{$lo['name']}: Rp " . number_format($lo['aov'], 0, ',', '.') . " ({$lo['orders']} orders). ";

                if ($gap >= 40) {
                    $severity = 'info';
                    $text .= "AOV {$hi['name']} lebih tinggi {$gap}% dari {$lo['name']}. ";
                    $text .= "Buat bundle produk eksklusif di {$hi['name']} untuk mendorong AOV.";
                } elseif ($overallAov < 100000) {
                    $severity = 'warning';
                    $text .= "AOV masih di bawah Rp 100.000. Dorong dengan paket bundling atau minimal pembelian gratis ongkir.";
                } else {
                    $severity = 'success';
                    $text .= "AOV sehat. Cross-sell produk komplementer untuk mendorong AOV lebih tinggi.";
                }

                $insights[] = [
                    'icon' => 'bi-cart-plus',
                    'color' => $this->getSeverityColor($severity),
                    'border' => $this->getSeverityColor($severity),
                    'label' => 'Average Order Value (AOV)',
                    'text' => $text,
                    'severity' => $severity
                ];
            }
        }

        return $insights;
    }

    /**
     * Get color berdasarkan severity
     */
    private function getSeverityColor(string $severity): string
    {
        return match ($severity) {
            'danger' => '#C61C8C',
            'warning' => '#D000A8',
            'success' => 'var(--clr-teal)',
            default => 'var(--clr-logo)'
        };
    }
}