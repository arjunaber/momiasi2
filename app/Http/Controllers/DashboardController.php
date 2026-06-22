<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $availablePeriods = Transaction::selectRaw('period_month')
            ->groupBy('period_month')->orderByDesc('period_month')->pluck('period_month');

        $selectedPeriods = $request->filled('periods')
            ? collect($request->input('periods'))->sort()->values()->toArray()
            : $availablePeriods->toArray();

        $marketplaces = Marketplace::active()->get();
        $base         = fn() => Transaction::completed()->whereIn('period_month', $selectedPeriods);

        $summary = $base()->selectRaw('
            SUM(revenue) AS total_revenue, SUM(advertising_spend) AS total_adspend,
            SUM(profit) AS total_profit, SUM(quantity) AS total_qty, COUNT(*) AS total_orders
        ')->first();

        $marketplaceSummary = $base()
            ->join('marketplaces', 'transactions.marketplace_id', '=', 'marketplaces.id')
            ->groupBy('transactions.marketplace_id', 'marketplaces.name', 'marketplaces.slug', 'marketplaces.color')
            ->selectRaw('transactions.marketplace_id, marketplaces.name AS marketplace_name,
                marketplaces.slug AS marketplace_slug, marketplaces.color AS marketplace_color,
                SUM(transactions.revenue) AS total_revenue, SUM(transactions.advertising_spend) AS total_adspend,
                SUM(transactions.profit) AS total_profit, COUNT(*) AS total_orders, SUM(transactions.quantity) AS total_qty')
            ->get();

        $monthlyStats = $base()->groupBy('period_month')
            ->selectRaw('period_month, SUM(revenue) AS total_revenue, SUM(advertising_spend) AS total_adspend,
                SUM(profit) AS total_profit, COUNT(*) AS total_orders, SUM(quantity) AS total_qty')
            ->orderBy('period_month')->get();

        $marketplaceMonthData = $base()
            ->join('marketplaces', 'transactions.marketplace_id', '=', 'marketplaces.id')
            ->groupBy('transactions.marketplace_id', 'marketplaces.name', 'marketplaces.slug', 'marketplaces.color', 'transactions.period_month')
            ->selectRaw('transactions.marketplace_id, marketplaces.name AS marketplace_name,
                marketplaces.slug AS marketplace_slug, marketplaces.color AS marketplace_color,
                transactions.period_month, SUM(transactions.revenue) AS total_revenue,
                SUM(transactions.advertising_spend) AS total_adspend, SUM(transactions.profit) AS total_profit,
                COUNT(*) AS total_orders')
            ->orderBy('transactions.period_month')->get();

        $top5Products = $base()
            ->join('products', 'transactions.product_id', '=', 'products.id')
            ->groupBy('transactions.product_id', 'products.name', 'products.sku', 'products.category')
            ->selectRaw('transactions.product_id, products.name AS product_name, products.sku AS product_sku,
                products.category AS product_category, SUM(transactions.revenue) AS total_revenue,
                SUM(transactions.quantity) AS total_qty, SUM(transactions.profit) AS total_profit, COUNT(*) AS total_orders')
            ->orderByDesc('total_revenue')->limit(5)->get();

        $dailyTrend = $base()
            ->join('marketplaces', 'transactions.marketplace_id', '=', 'marketplaces.id')
            ->groupBy('transaction_date', 'transactions.marketplace_id', 'marketplaces.name', 'marketplaces.slug', 'marketplaces.color')
            ->selectRaw('transaction_date, transactions.marketplace_id, marketplaces.name AS marketplace_name,
                marketplaces.slug AS marketplace_slug, marketplaces.color AS marketplace_color,
                SUM(transactions.revenue) AS daily_revenue, SUM(transactions.profit) AS daily_profit, COUNT(*) AS daily_orders')
            ->orderBy('transaction_date')->get();

        $insights = $this->generateInsights($summary, $marketplaceSummary, $monthlyStats, $top5Products);

        return view('dashboard.index', compact(
            'summary',
            'marketplaceSummary',
            'monthlyStats',
            'marketplaceMonthData',
            'top5Products',
            'dailyTrend',
            'insights',
            'selectedPeriods',
            'availablePeriods',
            'marketplaces'
        ));
    }

    private function generateInsights($summary, $marketplaceSummary, $monthlyStats, $top5Products): array
    {
        $insights     = [];
        $totalRevenue = (float)($summary->total_revenue ?? 0);
        $totalProfit  = (float)($summary->total_profit  ?? 0);
        $totalAdspend = (float)($summary->total_adspend ?? 0);
        $totalOrders  = (int)($summary->total_orders    ?? 0);
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
        $roas         = $totalAdspend > 0 ? $totalRevenue / $totalAdspend : 0;
        $avgOrderVal  = $totalOrders  > 0 ? $totalRevenue / $totalOrders  : 0;

        // ① DOMINASI PLATFORM
        if ($marketplaceSummary->count() >= 2) {
            $sorted   = $marketplaceSummary->sortByDesc('total_revenue');
            $top      = $sorted->first();
            $bottom   = $sorted->last();
            $topShare = $totalRevenue > 0 ? round(($top->total_revenue / $totalRevenue) * 100, 1) : 0;
            $botShare = $totalRevenue > 0 ? round(($bottom->total_revenue / $totalRevenue) * 100, 1) : 0;
            $topRoas  = $top->total_adspend > 0 ? round($top->total_revenue / $top->total_adspend, 2) : 0;
            $botRoas  = $bottom->total_adspend > 0 ? round($bottom->total_revenue / $bottom->total_adspend, 2) : 0;

            if ($topShare >= 65) {
                $sev  = 'warning';
                $text = "{$top->marketplace_name} mendominasi {$topShare}% revenue. Ketergantungan tinggi — diversifikasi ke {$bottom->marketplace_name} ({$botShare}%) perlu diprioritaskan segera.";
            } elseif ($topShare >= 50) {
                $sev  = 'info';
                $text = "{$top->marketplace_name} unggul di {$topShare}% vs {$bottom->marketplace_name} {$botShare}%. Alokasikan SKU terlaris ke {$top->marketplace_name} dan coba produk baru di {$bottom->marketplace_name}.";
            } else {
                $sev  = 'success';
                $text = "Distribusi seimbang: {$top->marketplace_name} {$topShare}% vs {$bottom->marketplace_name} {$botShare}%. Strategi dua platform berjalan optimal.";
            }
            if ($topRoas > 0 && $botRoas > 0 && abs($topRoas - $botRoas) >= 1) {
                $better = $topRoas >= $botRoas ? $top->marketplace_name : $bottom->marketplace_name;
                $text  .= " ROAS {$better} lebih efisien — pertimbangkan realokasi budget iklan ke sana.";
            }
            $insights[] = ['icon' => 'bi-arrow-left-right', 'color' => match ($sev) {
                'warning' => 'var(--clr-magenta)',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-teal)'
            }, 'border' => match ($sev) {
                'warning' => 'var(--clr-magenta)',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-teal)'
            }, 'label' => '① Dominasi & Distribusi Platform', 'text' => $text, 'severity' => $sev];
        }

        // ② TREN PERTUMBUHAN
        if ($monthlyStats->count() >= 2) {
            $ms        = $monthlyStats->sortBy('period_month')->values();
            $last      = $ms->last();
            $prev      = $ms->get($ms->count() - 2);
            $revGrowth = $prev->total_revenue > 0 ? round((($last->total_revenue - $prev->total_revenue) / $prev->total_revenue) * 100, 1) : 0;
            $ordGrowth = $prev->total_orders  > 0 ? round((($last->total_orders  - $prev->total_orders)  / $prev->total_orders)  * 100, 1) : 0;
            $profGrowth = $prev->total_profit  > 0 ? round((($last->total_profit  - $prev->total_profit)  / $prev->total_profit)  * 100, 1) : null;
            $lastLabel = Carbon::parse($last->period_month . '-01')->isoFormat('MMM Y');
            $prevLabel = Carbon::parse($prev->period_month . '-01')->isoFormat('MMM Y');

            if ($revGrowth <= -15) {
                $sev = 'danger';
                $text = "Revenue turun signifikan {$revGrowth}% dari {$prevLabel} ke {$lastLabel}. Periksa perubahan algoritma platform, stok, atau kompetitor baru di kategori maternity.";
            } elseif ($revGrowth < 0) {
                $sev = 'warning';
                $text = "Revenue sedikit turun {$revGrowth}% di {$lastLabel}. " . ($ordGrowth >= 0 ? "Order masih stabil ({$ordGrowth}%) — kemungkinan penurunan average order value." : "Cek traffic dan konversi listing produk.");
            } elseif ($revGrowth >= 25) {
                $sev = 'success';
                $text = "Pertumbuhan luar biasa +{$revGrowth}% dari {$prevLabel} ke {$lastLabel}! Order naik {$ordGrowth}%." . ($profGrowth !== null ? " Profit tumbuh {$profGrowth}% — bisnis berskala sangat sehat." : " Jaga margin agar tidak tergerus biaya operasional.");
            } else {
                $sev = 'info';
                $text = "Revenue tumbuh moderat +{$revGrowth}% dari {$prevLabel} ke {$lastLabel}. " . ($profGrowth !== null && $profGrowth < $revGrowth - 10 ? "Profit tumbuh lebih lambat ({$profGrowth}%) — audit biaya iklan dan HPP." : "Pertumbuhan stabil. Dorong dengan bundling produk Momiasi + Little Mommies.");
            }

            $insights[] = ['icon' => $revGrowth >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow', 'color' => match ($sev) {
                'danger' => '#C61C8C',
                'warning' => '#D000A8',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-logo)'
            }, 'border' => match ($sev) {
                'danger' => '#C61C8C',
                'warning' => '#D000A8',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-logo)'
            }, 'label' => '② Tren Pertumbuhan Bisnis', 'text' => $text, 'severity' => $sev];
        }

        // ③ ROAS & EFISIENSI IKLAN
        if ($totalAdspend > 0) {
            $r = round($roas, 2);
            $adRatio = round(($totalAdspend / $totalRevenue) * 100, 1);
            if ($roas >= 6) {
                $sev = 'success';
                $text = "ROAS luar biasa {$r}× — iklan sangat efisien (biaya hanya {$adRatio}% dari revenue). Waktu yang tepat untuk scale up anggaran iklan produk unggulan.";
            } elseif ($roas >= 4) {
                $sev = 'success';
                $text = "ROAS sehat {$r}× (benchmark 4×). Biaya iklan {$adRatio}% revenue. Lakukan A/B test konten video TikTok vs foto Shopee untuk optimasi lebih lanjut.";
            } elseif ($roas >= 2.5) {
                $sev = 'info';
                $text = "ROAS {$r}× masih profitable tapi ada ruang optimasi. Biaya iklan {$adRatio}% revenue. Fokuskan budget pada produk dengan konversi tertinggi (Lullaby Set & Korset).";
            } elseif ($roas >= 1) {
                $sev = 'warning';
                $text = "ROAS rendah {$r}× — iklan hampir tidak balik modal ({$adRatio}% revenue). Hentikan kampanye ROAS < 2×, perbaiki targeting, dan optimalkan creative.";
            } else {
                $sev = 'danger';
                $text = "ROAS di bawah 1× ({$r}×) — iklan merugi! Hentikan semua kampanye berbayar, audit strategi dari nol, fokus pada traffic organik (rating & ulasan).";
            }

            $m = round($profitMargin, 1);
            if ($profitMargin < 10 && $roas < 4) $text .= " ⚡ Kritis: margin tipis ({$m}%) + ROAS rendah = bisnis hampir tidak untung. Negosiasi HPP atau naikkan harga jual.";
            elseif ($profitMargin >= 25) $text .= " Margin solid ({$m}%) memberikan buffer yang baik untuk investasi pertumbuhan.";

            $insights[] = ['icon' => 'bi-lightning-charge', 'color' => match ($sev) {
                'danger' => '#C61C8C',
                'warning' => '#D000A8',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-logo)'
            }, 'border' => match ($sev) {
                'danger' => '#C61C8C',
                'warning' => '#D000A8',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-logo)'
            }, 'label' => '③ Efisiensi Iklan & ROAS', 'text' => $text, 'severity' => $sev];
        }

        // ④ KONSENTRASI PRODUK
        if ($top5Products->count() > 0 && $totalRevenue > 0) {
            $top1      = $top5Products->first();
            $top1Share = round(($top1->total_revenue / $totalRevenue) * 100, 1);
            $top3Share = round(($top5Products->take(3)->sum('total_revenue') / $totalRevenue) * 100, 1);
            $top1Margin = $top1->total_revenue > 0 ? round(($top1->total_profit / $top1->total_revenue) * 100, 1) : 0;

            if ($top1Share >= 40) {
                $sev = 'warning';
                $text = "\"{$top1->product_name}\" menyumbang {$top1Share}% revenue — konsentrasi tinggi. Kembangkan produk pelengkap agar portofolio lebih diversifikasi.";
            } elseif ($top3Share >= 75) {
                $sev = 'info';
                $text = "Top 3 produk menyumbang {$top3Share}% revenue. Kategori " . ($top1->product_category) . " mendominasi — pertimbangkan ekspansi lini produk baru.";
            } else {
                $sev = 'success';
                $text = "Portofolio sehat — top 3 SKU berkontribusi {$top3Share}%. Tidak ada ketergantungan berlebihan pada satu produk.";
            }

            if ($top1Margin < 15) $text .= " ⚠ Margin produk terlaris hanya {$top1Margin}% — evaluasi harga jual atau HPP.";
            elseif ($top1Margin >= 35) $text .= " Margin tinggi ({$top1Margin}%) pada produk terlaris — pertahankan!";

            $insights[] = ['icon' => 'bi-box-seam', 'color' => match ($sev) {
                'warning' => 'var(--clr-magenta)',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-logo)'
            }, 'border' => match ($sev) {
                'warning' => 'var(--clr-magenta)',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-logo)'
            }, 'label' => '④ Konsentrasi Portofolio Produk', 'text' => $text, 'severity' => $sev];
        }

        // ⑤ AOV
        if ($avgOrderVal > 0 && $marketplaceSummary->count() >= 2) {
            $aovs = $marketplaceSummary->map(fn($m) => ['name' => $m->marketplace_name, 'aov' => $m->total_orders > 0 ? round($m->total_revenue / $m->total_orders) : 0])->sortByDesc('aov');
            $hi   = $aovs->first();
            $lo   = $aovs->last();
            $gap  = $lo['aov'] > 0 ? round((($hi['aov'] - $lo['aov']) / $lo['aov']) * 100, 1) : 0;
            $aovFmt = 'Rp ' . number_format($avgOrderVal, 0, ',', '.');

            if ($gap >= 40) {
                $sev = 'info';
                $text = "AOV {$hi['name']} (Rp " . number_format($hi['aov'], 0, ',', '.') . "} lebih tinggi {$gap}% dari {$lo['name']}. Buat bundle Momiasi + Little Mommies eksklusif di {$hi['name']} untuk mendorong AOV lebih tinggi.";
            } elseif ($avgOrderVal < 100000) {
                $sev = 'warning';
                $text = "AOV rata-rata ({$aovFmt}) masih di bawah Rp 100.000. Dorong dengan: paket bundling, minimum pembelian gratis ongkir, atau diskon beli 2 hemat 20%.";
            } else {
                $sev = 'success';
                $text = "AOV sehat di {$aovFmt}. Kedua platform relatif seimbang. Cross-sell produk komplementer (contoh: Dress + Korset) dapat mendorong AOV lebih tinggi.";
            }

            $insights[] = ['icon' => 'bi-cart-plus', 'color' => match ($sev) {
                'warning' => 'var(--clr-magenta)',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-logo)'
            }, 'border' => match ($sev) {
                'warning' => 'var(--clr-magenta)',
                'success' => 'var(--clr-teal)',
                default => 'var(--clr-logo)'
            }, 'label' => '⑤ Average Order Value & Upsell', 'text' => $text, 'severity' => $sev];
        }

        return $insights;
    }
}
