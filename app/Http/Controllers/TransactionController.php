<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\CsvImport;
use App\Services\CsvImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    protected CsvImportService $csvService;

    public function __construct(CsvImportService $csvService)
    {
        $this->csvService = $csvService;
    }

    public function index(Request $request)
    {
        $allowed   = ['transaction_date', 'order_id', 'quantity', 'revenue', 'advertising_spend', 'profit'];
        $sortField = in_array($request->get('sort'), $allowed) ? $request->get('sort') : 'transaction_date';
        $sortDir   = $request->get('dir') === 'asc' ? 'asc' : 'desc';

        $query = Transaction::with(['marketplace', 'product', 'csvImport'])
            ->orderBy($sortField, $sortDir)
            ->orderBy('id', 'desc');

        if ($request->filled('marketplace_id')) $query->where('marketplace_id', $request->marketplace_id);
        if ($request->filled('period'))         $query->where('period_month', $request->period);
        if ($request->filled('status'))         $query->where('status', $request->status);
        if ($request->filled('date_from'))      $query->where('transaction_date', '>=', $request->date_from);
        if ($request->filled('date_to'))        $query->where('transaction_date', '<=', $request->date_to);
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(
                fn($q) =>
                $q->where('order_id', 'like', $s)
                    ->orWhereHas('product', fn($p) => $p->where('name', 'like', $s)->orWhere('sku', 'like', $s))
                    ->orWhere('customer_city', 'like', $s)
            );
        }

        $transactions = $query->paginate(20)->withQueryString();
        $marketplaces = Marketplace::active()->get();
        $products     = Product::active()->orderBy('name')->get();
        $periods      = Transaction::selectRaw('period_month')
            ->groupBy('period_month')->orderByDesc('period_month')->pluck('period_month');

        return view('transactions.index', compact('transactions', 'marketplaces', 'products', 'periods'));
    }

    public function create()
    {
        return view('transactions.create', [
            'marketplaces' => Marketplace::active()->get(),
            'products'     => Product::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'marketplace_id'    => 'required|exists:marketplaces,id',
            'product_id'        => 'required|exists:products,id',
            'order_id'          => 'nullable|string|max:100',
            'transaction_date'  => 'required|date',
            'quantity'          => 'required|integer|min:1',
            'unit_price'        => 'required|numeric|min:0',
            'revenue'           => 'required|numeric|min:0',
            'cogs'              => 'nullable|numeric|min:0',
            'advertising_spend' => 'nullable|numeric|min:0',
            'platform_fee'      => 'nullable|numeric|min:0',
            'shipping_subsidy'  => 'nullable|numeric|min:0',
            'discount'          => 'nullable|numeric|min:0',
            'customer_city'     => 'nullable|string|max:100',
            'status'            => 'required|in:completed,cancelled,returned,pending',
            'notes'             => 'nullable|string',
        ]);

        Transaction::create($data);
        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function show(Transaction $transaction)
    {
        return response()->json($transaction->load(['marketplace', 'product']));
    }

    public function edit(Transaction $transaction)
    {
        return view('transactions.edit', [
            'transaction'  => $transaction,
            'marketplaces' => Marketplace::active()->get(),
            'products'     => Product::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'marketplace_id'    => 'required|exists:marketplaces,id',
            'product_id'        => 'required|exists:products,id',
            'order_id'          => 'nullable|string|max:100',
            'transaction_date'  => 'required|date',
            'quantity'          => 'required|integer|min:1',
            'unit_price'        => 'required|numeric|min:0',
            'revenue'           => 'required|numeric|min:0',
            'cogs'              => 'nullable|numeric|min:0',
            'advertising_spend' => 'nullable|numeric|min:0',
            'platform_fee'      => 'nullable|numeric|min:0',
            'shipping_subsidy'  => 'nullable|numeric|min:0',
            'discount'          => 'nullable|numeric|min:0',
            'customer_city'     => 'nullable|string|max:100',
            'status'            => 'required|in:completed,cancelled,returned,pending',
            'notes'             => 'nullable|string',
        ]);

        $transaction->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil diperbarui.']);
        }
        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Transaksi berhasil dihapus.']);
        }
        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    public function importForm()
    {
        $imports = CsvImport::with('user')->orderByDesc('created_at')->paginate(15);
        return view('transactions.import', compact('imports'));
    }

    public function importCsv(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:10240']);
        $result = $this->csvService->import($request->file('csv_file'), auth()->user());

        if ($result['success']) {
            $msg = "Import berhasil! {$result['success_rows']} transaksi ditambahkan";
            if ($result['failed_rows'] > 0) $msg .= ", {$result['failed_rows']} baris gagal.";
            return redirect()->route('transactions.import')->with('success', $msg);
        }
        return redirect()->route('transactions.import')->with('error', 'Import gagal: ' . $result['message']);
    }

    public function destroyImport(CsvImport $csvImport)
    {
        $count    = $csvImport->transactions()->count();
        $filename = $csvImport->filename;
        DB::transaction(function () use ($csvImport) {
            $csvImport->transactions()->delete();
            $csvImport->delete();
        });
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => "File dan {$count} transaksi berhasil dihapus."]);
        }
        return redirect()->route('transactions.import')->with('success', "File \"{$filename}\" dan {$count} transaksi berhasil dihapus.");
    }

    public function downloadTemplate()
    {
        $cb = function () {
            $f = fopen('php://output', 'w');
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
            fputcsv($f, ['ORD-001', '2025-01-05', 'shopee', 'MOM-001', '2', '189000', '378000', '300000', '30000', '18900', '10000', '0', 'Jakarta', 'completed', '']);
            fputcsv($f, ['ORD-002', '2025-01-07', 'tiktok', 'LM-001', '3', '159000', '477000', '375000', '45000', '23850', '12000', '0', 'Bandung', 'completed', '']);
            fclose($f);
        };
        return response()->stream($cb, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_transaksi_momiasi.csv"',
        ]);
    }
}
