<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\CsvImport;
use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected CsvImportService $csvService;
    protected float $remainingAdspend = 0;
    protected ?int $lastTransactionId = null;

    public function __construct(CsvImportService $csvService)
    {
        $this->csvService = $csvService;
    }

    /**
     * Display a listing of transactions with filters and sorting
     */
    public function index(Request $request)
    {
        $allowed = ['transaction_date', 'order_id', 'quantity', 'revenue', 'advertising_spend', 'profit', 'profit_margin'];
        $sortField = in_array($request->get('sort'), $allowed) ? $request->get('sort') : 'transaction_date';
        $sortDir = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $query = Transaction::with(['marketplace', 'product', 'csvImport'])
            ->orderBy($sortField, $sortDir)
            ->orderBy('id', 'desc');

        // Filters
        if ($request->filled('marketplace_id')) {
            $query->where('marketplace_id', $request->marketplace_id);
        }

        if ($request->filled('period')) {
            $query->where('period_month', $request->period);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', Carbon::parse($request->date_to));
        }

        // Search
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', $search)
                    ->orWhereHas('product', function ($p) use ($search) {
                        $p->where('name', 'like', $search)
                            ->orWhere('sku', 'like', $search);
                    })
                    ->orWhere('customer_city', 'like', $search);
            });
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Data untuk filter
        $marketplaces = Marketplace::active()->get();
        $products = Product::active()->orderBy('name')->get();
        $periods = Transaction::selectRaw('period_month')
            ->groupBy('period_month')
            ->orderByDesc('period_month')
            ->pluck('period_month');

        // Statistik ringkasan
        $summary = [
            'total_revenue' => Transaction::sum('revenue') ?? 0,
            'total_profit' => Transaction::sum('profit') ?? 0,
            'total_transactions' => Transaction::count(),
            'avg_profit_margin' => Transaction::avg('profit_margin') ?? 0,
        ];

        return view('transactions.index', compact(
            'transactions',
            'marketplaces',
            'products',
            'periods',
            'summary'
        ));
    }

    /**
     * Show form for creating new transaction
     */
    public function create()
    {
        return view('transactions.create', [
            'marketplaces' => Marketplace::active()->get(),
            'products' => Product::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'marketplace_id' => 'required|exists:marketplaces,id',
            'product_id' => 'required|exists:products,id',
            'order_id' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'revenue' => 'required|numeric|min:0',
            'cogs' => 'nullable|numeric|min:0',
            'advertising_spend' => 'nullable|numeric|min:0',
            'platform_fee' => 'nullable|numeric|min:0',
            'shipping_subsidy' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'customer_city' => 'nullable|string|max:100',
            'status' => 'required|in:completed,cancelled,returned,pending',
            'notes' => 'nullable|string',
        ]);

        $validated = $this->recalculateTransaction($validated);
        $transaction = Transaction::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditambahkan.',
                'data' => $transaction->load(['marketplace', 'product'])
            ]);
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    /**
     * Display the specified transaction (JSON)
     */
    public function show(Transaction $transaction)
    {
        return response()->json([
            'success' => true,
            'data' => $transaction->load(['marketplace', 'product', 'csvImport'])
        ]);
    }

    /**
     * Show form for editing transaction
     */
    public function edit(Transaction $transaction)
    {
        return view('transactions.edit', [
            'transaction' => $transaction,
            'marketplaces' => Marketplace::active()->get(),
            'products' => Product::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'marketplace_id' => 'required|exists:marketplaces,id',
            'product_id' => 'required|exists:products,id',
            'order_id' => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'revenue' => 'required|numeric|min:0',
            'cogs' => 'nullable|numeric|min:0',
            'advertising_spend' => 'nullable|numeric|min:0',
            'platform_fee' => 'nullable|numeric|min:0',
            'shipping_subsidy' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'customer_city' => 'nullable|string|max:100',
            'status' => 'required|in:completed,cancelled,returned,pending',
            'notes' => 'nullable|string',
        ]);

        $validated = $this->recalculateTransaction($validated);
        $transaction->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diperbarui.',
                'data' => $transaction->fresh()->load(['marketplace', 'product'])
            ]);
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction, Request $request)
    {
        $transaction->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus.'
            ]);
        }

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Show import form with history
     */
    public function importForm()
    {
        $imports = CsvImport::with('user')
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total_imports' => CsvImport::count(),
            'total_transactions_imported' => Transaction::whereNotNull('csv_import_id')->count(),
            'last_import' => CsvImport::latest()->first(),
            'total_success' => CsvImport::sum('success_rows'),
            'total_failed' => CsvImport::sum('failed_rows'),
            'total_duplicates' => CsvImport::sum('duplicate_rows'),
        ];

        return view('transactions.import', compact('imports', 'stats'));
    }

    /**
     * Process CSV import
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'import_type' => 'nullable|in:simple,full'
        ]);

        $importType = $request->input('import_type', 'simple');
        $result = $this->csvService->import(
            $request->file('csv_file'),
            auth()->user(),
            $importType
        );

        if ($result['success']) {
            $message = "Import berhasil! {$result['success_rows']} transaksi ditambahkan";

            if ($result['failed_rows'] > 0) {
                $message .= ", {$result['failed_rows']} baris gagal.";
            }

            if (isset($result['duplicate_rows']) && $result['duplicate_rows'] > 0) {
                $message .= " {$result['duplicate_rows']} baris duplikat dilewati.";
            }

            if (!empty($result['warnings'])) {
                $message .= " Peringatan: " . implode(', ', $result['warnings']);
            }

            return redirect()
                ->route('transactions.import')
                ->with('success', $message)
                ->with('duplicates', $result['duplicate_rows'] ?? 0);
        }

        return redirect()
            ->route('transactions.import')
            ->with('error', 'Import gagal: ' . $result['message']);
    }

    /**
     * Delete import batch and its transactions
     */
    public function destroyImport(CsvImport $csvImport, Request $request)
    {
        $count = $csvImport->transactions()->count();
        $filename = $csvImport->filename;

        DB::transaction(function () use ($csvImport) {
            $csvImport->transactions()->delete();
            $csvImport->delete();
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "File dan {$count} transaksi berhasil dihapus."
            ]);
        }

        return redirect()
            ->route('transactions.import')
            ->with('success', "File \"{$filename}\" dan {$count} transaksi berhasil dihapus.");
    }

    /**
     * Download CSV template (support simple & full mode)
     */
    public function downloadTemplate(Request $request)
    {
        $mode = $request->get('mode', 'simple');

        $cb = function () use ($mode) {
            $f = fopen('php://output', 'w');

            if ($mode === 'simple') {
                fputcsv($f, [
                    'transaction_date',
                    'marketplace_slug',
                    'product_sku',
                    'quantity',
                    'revenue'
                ]);
                fputcsv($f, [
                    '2026-06-01',
                    'shopee',
                    'MOM-001',
                    '2',
                    '378000'
                ]);
                fputcsv($f, [
                    '2026-06-02',
                    'tiktok',
                    'LM-001',
                    '3',
                    '477000'
                ]);
                fputcsv($f, [
                    '# Catatan:',
                    '# Kolom minimal yang dibutuhkan untuk import simple',
                    '# Marketplace yang tersedia: shopee, tiktok',
                    '# SKU produk harus sudah terdaftar di database'
                ]);
            } else {
                fputcsv($f, [
                    'order_id',
                    'transaction_date',
                    'marketplace_slug',
                    'product_sku',
                    'quantity',
                    'unit_price',
                    'revenue',
                    'cogs',
                    'advertising_spend',
                    'platform_fee',
                    'shipping_subsidy',
                    'discount',
                    'customer_city',
                    'status',
                    'notes'
                ]);
                fputcsv($f, [
                    'ORD-001',
                    '2026-06-01',
                    'shopee',
                    'MOM-001',
                    '2',
                    '189000',
                    '378000',
                    '300000',
                    '15000',
                    '18900',
                    '5000',
                    '0',
                    'Jakarta',
                    'completed',
                    ''
                ]);
                fputcsv($f, [
                    'ORD-002',
                    '2026-06-02',
                    'tiktok',
                    'LM-001',
                    '3',
                    '159000',
                    '477000',
                    '375000',
                    '25000',
                    '23850',
                    '7000',
                    '0',
                    'Bandung',
                    'completed',
                    ''
                ]);
                fputcsv($f, [
                    '# Catatan:',
                    '# Kolom status: completed, cancelled, returned, pending',
                    '# Unit price = revenue / quantity (akan otomatis dihitung)',
                    '# Biaya (cogs, advertising_spend, dll) bisa dikosongkan jika belum tahu'
                ]);
            }
            fclose($f);
        };

        $filename = $mode === 'simple'
            ? 'template_transaksi_simple.csv'
            : 'template_transaksi_full.csv';

        return response()->stream($cb, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate'
        ]);
    }

    /**
     * Show batch update costs form
     */
    public function batchCostForm()
    {
        $marketplaces = Marketplace::active()->get();
        $periods = Transaction::selectRaw('period_month')
            ->groupBy('period_month')
            ->orderByDesc('period_month')
            ->pluck('period_month');

        // 🔥 STATISTIK PER MARKETPLACE - TAMBAHKAN TOTAL TRANSAKSI
        $adsPerMarketplace = Transaction::selectRaw('marketplace_id, COUNT(*) as total_transactions, SUM(advertising_spend) as total_ads, SUM(revenue) as total_revenue')
            ->groupBy('marketplace_id')
            ->with('marketplace')
            ->get()
            ->map(function ($item) {
                return [
                    'marketplace_name' => $item->marketplace->name ?? 'Unknown',
                    'total_transactions' => $item->total_transactions ?? 0,
                    'total_ads' => $item->total_ads ?? 0,
                    'total_revenue' => $item->total_revenue ?? 0,
                ];
            });

        $stats = [
            'total_transactions' => Transaction::count(),
            'total_periods' => Transaction::distinct('period_month')->count('period_month'),
            'total_revenue' => Transaction::sum('revenue') ?? 0,
            'avg_profit_margin' => Transaction::avg('profit_margin') ?? 0,
            'total_adspend' => Transaction::sum('advertising_spend') ?? 0,
            'ads_per_marketplace' => $adsPerMarketplace,
        ];

        return view('transactions.batch-cost', compact('marketplaces', 'periods', 'stats'));
    }

    /**
     * 🔥 RECALCULATE TRANSACTION - Helper Method
     */
    protected function recalculateTransaction(array $data): array
    {
        $revenue = (float) ($data['revenue'] ?? 0);
        $cogs = (float) ($data['cogs'] ?? 0);
        $advertisingSpend = (float) ($data['advertising_spend'] ?? 0);
        $platformFee = (float) ($data['platform_fee'] ?? 0);
        $shippingSubsidy = (float) ($data['shipping_subsidy'] ?? 0);
        $discount = (float) ($data['discount'] ?? 0);

        $totalCost = $cogs + $advertisingSpend + $platformFee + $shippingSubsidy + $discount;
        $profit = $revenue - $totalCost;
        $profitMargin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;

        $data['total_cost'] = $totalCost;
        $data['profit'] = $profit;
        $data['profit_margin'] = $profitMargin;

        return $data;
    }

    /**
     * 🔥 RECALCULATE BULK TRANSACTIONS - Helper Method
     */
    protected function recalculateBulkTransactions(array $transactionIds): void
    {
        if (empty($transactionIds)) {
            return;
        }

        $transactions = Transaction::whereIn('id', $transactionIds)->get();

        foreach ($transactions as $transaction) {
            $revenue = (float) $transaction->revenue;
            $cogs = (float) ($transaction->cogs ?? 0);
            $advertisingSpend = (float) ($transaction->advertising_spend ?? 0);
            $platformFee = (float) ($transaction->platform_fee ?? 0);
            $shippingSubsidy = (float) ($transaction->shipping_subsidy ?? 0);
            $discount = (float) ($transaction->discount ?? 0);

            $totalCost = $cogs + $advertisingSpend + $platformFee + $shippingSubsidy + $discount;
            $profit = $revenue - $totalCost;
            $profitMargin = $revenue > 0 ? round(($profit / $revenue) * 100, 2) : 0;

            Transaction::where('id', $transaction->id)->update([
                'total_cost' => $totalCost,
                'profit' => $profit,
                'profit_margin' => $profitMargin,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * 🔥 BATCH UPDATE COSTS - PERFECT ACCURACY
     */
    public function batchUpdateCosts(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|date_format:Y-m',
            'marketplace_id' => 'nullable|exists:marketplaces,id',
            'advertising_spend' => 'nullable|numeric|min:0',
            'cogs_percentage' => 'nullable|numeric|min:0|max:100',
            'platform_fee_percentage' => 'nullable|numeric|min:0|max:100',
            'shipping_subsidy' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        $query = Transaction::where('period_month', $validated['period']);

        if ($request->filled('marketplace_id')) {
            $query->where('marketplace_id', $validated['marketplace_id']);
        }

        $totalTransactions = $query->count();

        if ($totalTransactions === 0) {
            return redirect()
                ->back()
                ->with('warning', 'Tidak ada transaksi untuk periode ' . $validated['period']);
        }

        $transactionIds = $query->pluck('id')->toArray();
        $updates = [];
        $hasUpdate = false;

        // 🔥 FIX: UPDATE ADVERTISING SPEND - REPLACE, NOT ADD!
        if ($request->filled('advertising_spend')) {
            $totalAdspend = (float) $validated['advertising_spend'];

            // 🔥 HITUNG PER TRANSACTION DENGAN 2 DESIMAL (PRESISI)
            $perTransaction = floor(($totalAdspend * 100) / $totalTransactions) / 100;
            $remaining = round($totalAdspend - ($perTransaction * $totalTransactions), 2);

            // 🔥 PASTIKAN TIDAK NEGATIF
            if ($remaining < 0) {
                $perTransaction = ceil(($totalAdspend * 100) / $totalTransactions) / 100;
                $remaining = round($totalAdspend - ($perTransaction * $totalTransactions), 2);
            }

            // 🔥 REPLACE nilai advertising_spend (bukan ditambah)
            $updates['advertising_spend'] = DB::raw("$perTransaction");
            $hasUpdate = true;

            // 🔥 SIMPAN SISA UNTUK TRANSAKSI TERAKHIR
            if ($remaining > 0) {
                $this->remainingAdspend = $remaining;
                $this->lastTransactionId = end($transactionIds);
            } else {
                $this->remainingAdspend = 0;
                $this->lastTransactionId = null;
            }
        }

        // Update COGS (percentage dari revenue) - REPLACE
        if ($request->filled('cogs_percentage')) {
            $percentage = (float) $validated['cogs_percentage'] / 100;
            $updates['cogs'] = DB::raw("ROUND(revenue * $percentage, 2)");
            $hasUpdate = true;
        }

        // Update Platform Fee (percentage dari revenue) - REPLACE
        if ($request->filled('platform_fee_percentage')) {
            $percentage = (float) $validated['platform_fee_percentage'] / 100;
            $updates['platform_fee'] = DB::raw("ROUND(revenue * $percentage, 2)");
            $hasUpdate = true;
        }

        // Update Shipping Subsidy (flat) - REPLACE
        if ($request->filled('shipping_subsidy')) {
            $totalShipping = (float) $validated['shipping_subsidy'];
            $perTransaction = floor(($totalShipping * 100) / $totalTransactions) / 100;
            $remaining = round($totalShipping - ($perTransaction * $totalTransactions), 2);

            if ($remaining < 0) {
                $perTransaction = ceil(($totalShipping * 100) / $totalTransactions) / 100;
                $remaining = round($totalShipping - ($perTransaction * $totalTransactions), 2);
            }

            $updates['shipping_subsidy'] = DB::raw("$perTransaction");
            $hasUpdate = true;

            if ($remaining > 0) {
                $this->remainingAdspend += $remaining;
            }
        }

        // Update Discount (flat) - REPLACE
        if ($request->filled('discount')) {
            $totalDiscount = (float) $validated['discount'];
            $perTransaction = floor(($totalDiscount * 100) / $totalTransactions) / 100;
            $remaining = round($totalDiscount - ($perTransaction * $totalTransactions), 2);

            if ($remaining < 0) {
                $perTransaction = ceil(($totalDiscount * 100) / $totalTransactions) / 100;
                $remaining = round($totalDiscount - ($perTransaction * $totalTransactions), 2);
            }

            $updates['discount'] = DB::raw("$perTransaction");
            $hasUpdate = true;

            if ($remaining > 0) {
                $this->remainingAdspend += $remaining;
            }
        }

        if (!$hasUpdate) {
            return redirect()
                ->back()
                ->with('warning', 'Tidak ada data biaya yang diupdate. Silakan isi minimal satu field.');
        }

        // 🔥 UPDATE DAN RECALCULATE DALAM SATU TRANSACTION
        DB::transaction(function () use ($query, $updates, $transactionIds) {
            // 1. Update biaya (REPLACE, bukan ADD)
            $query->update($updates);

            // 2. 🔥 UPDATE TRANSAKSI TERAKHIR DENGAN SISA
            if ($this->remainingAdspend > 0 && isset($this->lastTransactionId)) {
                // Ambil data transaksi terakhir untuk ditambah sisa
                $lastTransaction = Transaction::find($this->lastTransactionId);
                if ($lastTransaction) {
                    $newAdspend = round(($lastTransaction->advertising_spend ?? 0) + $this->remainingAdspend, 2);
                    Transaction::where('id', $this->lastTransactionId)->update([
                        'advertising_spend' => $newAdspend
                    ]);
                }
            }

            // 3. 🔥 RECALCULATE semua transaksi yang diupdate
            $this->recalculateBulkTransactions($transactionIds);
        });

        // 🔥 AMBIL STATISTIK TERBARU
        $updatedQuery = Transaction::where('period_month', $validated['period']);
        if ($request->filled('marketplace_id')) {
            $updatedQuery->where('marketplace_id', $validated['marketplace_id']);
        }

        $stats = [
            'total_revenue' => $updatedQuery->sum('revenue'),
            'total_profit' => $updatedQuery->sum('profit'),
            'total_cost' => $updatedQuery->sum('total_cost'),
            'total_adspend' => $updatedQuery->sum('advertising_spend'),
            'avg_margin' => $updatedQuery->avg('profit_margin'),
        ];

        // 🔥 VERIFIKASI AKURASI
        $inputAdspend = (float) ($request->advertising_spend ?? 0);
        $actualAdspend = $stats['total_adspend'] ?? 0;
        $diff = round($inputAdspend - $actualAdspend, 2);

        $message = "  Biaya berhasil diupdate untuk periode {$validated['period']}";
        if ($request->filled('marketplace_id')) {
            $marketplace = Marketplace::find($validated['marketplace_id']);
            $message .= " (Marketplace: {$marketplace->name})";
        }
        $message .= ". Total {$totalTransactions} transaksi terupdate.";
        $message .= " Total Ad Spend: Rp " . number_format($actualAdspend, 0, ',', '.');

        if (abs($diff) > 0.01) {
            $message .= " ⚠️ Selisih: Rp " . number_format(abs($diff), 0, ',', '.');
        } else {
            $message .= "   Akurat!";
        }
        $message .= " | Margin baru: " . round($stats['avg_margin'] ?? 0, 1) . "%";

        return redirect()
            ->route('transactions.index')
            ->with('success', $message);
    }

    /**
     * Show weekly costs edit form
     */
    public function editWeeklyCosts(Request $request)
    {
        $weekStart = $request->input('week_start');
        $weekEnd = $request->input('week_end');
        $marketplaceId = $request->input('marketplace_id');

        if (!$weekStart || !$weekEnd) {
            $weekStart = Carbon::now()->startOfWeek()->format('Y-m-d');
            $weekEnd = Carbon::now()->endOfWeek()->format('Y-m-d');
        }

        $query = Transaction::whereBetween('transaction_date', [$weekStart, $weekEnd])
            ->with(['marketplace', 'product']);

        if ($request->filled('marketplace_id')) {
            $query->where('marketplace_id', $marketplaceId);
        }

        $transactions = $query->orderBy('transaction_date')->get();

        $summary = [
            'total_revenue' => $transactions->sum('revenue'),
            'total_profit' => $transactions->sum('profit'),
            'total_transactions' => $transactions->count(),
            'total_advertising_spend' => $transactions->sum('advertising_spend'),
            'total_platform_fee' => $transactions->sum('platform_fee'),
            'total_discount' => $transactions->sum('discount'),
            'total_shipping_subsidy' => $transactions->sum('shipping_subsidy'),
        ];

        $marketplaces = Marketplace::active()->get();

        return view('transactions.edit-weekly', compact(
            'transactions',
            'weekStart',
            'weekEnd',
            'marketplaceId',
            'marketplaces',
            'summary'
        ));
    }

    /**
     * Update weekly costs for selected transactions
     */
    public function updateWeeklyCosts(Request $request)
    {
        $validated = $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:transactions,id',
            'advertising_spend' => 'nullable|numeric|min:0',
            'platform_fee' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'shipping_subsidy' => 'nullable|numeric|min:0',
            'cogs_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $transactionIds = $validated['transaction_ids'];
        $updates = [];
        $hasUpdate = false;

        if ($request->filled('advertising_spend')) {
            $updates['advertising_spend'] = $validated['advertising_spend'];
            $hasUpdate = true;
        }

        if ($request->filled('platform_fee')) {
            $updates['platform_fee'] = $validated['platform_fee'];
            $hasUpdate = true;
        }

        if ($request->filled('discount')) {
            $updates['discount'] = $validated['discount'];
            $hasUpdate = true;
        }

        if ($request->filled('shipping_subsidy')) {
            $updates['shipping_subsidy'] = $validated['shipping_subsidy'];
            $hasUpdate = true;
        }

        DB::transaction(function () use ($transactionIds, $updates, $request, $validated, $hasUpdate) {
            if ($request->filled('cogs_percentage')) {
                $transactions = Transaction::whereIn('id', $transactionIds)->get();
                foreach ($transactions as $transaction) {
                    Transaction::where('id', $transaction->id)->update([
                        'cogs' => $transaction->revenue * ($validated['cogs_percentage'] / 100)
                    ]);
                }
                $hasUpdate = true;
            }

            if (!empty($updates)) {
                Transaction::whereIn('id', $transactionIds)->update($updates);
            }

            if ($hasUpdate) {
                $this->recalculateBulkTransactions($transactionIds);
            }
        });

        $count = count($transactionIds);
        $transactions = Transaction::whereIn('id', $transactionIds)->get();
        $avgMargin = $transactions->avg('profit_margin') ?? 0;

        return redirect()
            ->back()
            ->with('success', "  Biaya berhasil diupdate untuk {$count} transaksi. Rata-rata margin baru: " . round($avgMargin, 1) . "%");
    }

    /**
     * Export transactions to CSV
     */
    public function export(Request $request)
    {
        $query = Transaction::with(['marketplace', 'product']);

        if ($request->filled('period')) {
            $query->where('period_month', $request->period);
        }

        if ($request->filled('marketplace_id')) {
            $query->where('marketplace_id', $request->marketplace_id);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('transaction_date')->get();

        if ($transactions->isEmpty()) {
            return redirect()
                ->back()
                ->with('warning', 'Tidak ada data untuk diexport.');
        }

        $cb = function () use ($transactions) {
            $f = fopen('php://output', 'w');

            fputcsv($f, [
                'ID',
                'Tanggal',
                'Marketplace',
                'SKU Produk',
                'Nama Produk',
                'Jumlah',
                'Harga Satuan',
                'Revenue',
                'COGS',
                'Advertising Spend',
                'Platform Fee',
                'Shipping Subsidy',
                'Discount',
                'Total Cost',
                'Profit',
                'Profit Margin %',
                'Kota',
                'Status'
            ]);

            foreach ($transactions as $tx) {
                fputcsv($f, [
                    $tx->id,
                    $tx->transaction_date->format('Y-m-d'),
                    $tx->marketplace->name ?? '-',
                    $tx->product->sku ?? '-',
                    $tx->product->name ?? '-',
                    $tx->quantity,
                    $tx->unit_price,
                    $tx->revenue,
                    $tx->cogs,
                    $tx->advertising_spend,
                    $tx->platform_fee,
                    $tx->shipping_subsidy,
                    $tx->discount,
                    $tx->total_cost,
                    $tx->profit,
                    $tx->profit_margin,
                    $tx->customer_city ?? '-',
                    $tx->status
                ]);
            }

            fclose($f);
        };

        $filename = 'transactions_' . date('Y-m-d') . '.csv';

        return response()->stream($cb, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'private, max-age=0, must-revalidate'
        ]);
    }

    /**
     * Bulk delete transactions
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
            'transaction_ids.*' => 'exists:transactions,id',
        ]);

        $count = count($request->transaction_ids);

        DB::transaction(function () use ($request) {
            Transaction::whereIn('id', $request->transaction_ids)->delete();
        });

        return redirect()
            ->route('transactions.index')
            ->with('success', "{$count} transaksi berhasil dihapus.");
    }

    /**
     * Show import detail with failed and duplicate rows
     */
    public function showImportDetail(CsvImport $csvImport)
    {
        $failedRows = [];
        $errorLog = $csvImport->error_log;

        if (!empty($errorLog)) {
            $lines = explode("\n", $errorLog);
            foreach ($lines as $line) {
                if (preg_match('/Baris (\d+):/', $line, $matches)) {
                    $failedRows[] = [
                        'row' => (int) $matches[1],
                        'error' => $line
                    ];
                }
            }
        }

        $transactions = $csvImport->transactions()
            ->with(['marketplace', 'product'])
            ->orderBy('id')
            ->get();

        return view('transactions.import-detail', compact(
            'csvImport',
            'failedRows',
            'transactions'
        ));
    }
}
