<?php

namespace App\Services;

use App\Models\CsvImport;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CsvImportService
{
    protected array $errors = [];

    public function import(UploadedFile $file, $user): array
    {
        $storedFilename = 'csv_imports/' . uniqid() . '_' . $file->getClientOriginalName();
        $filePath       = Storage::disk('local')->putFileAs('', $file, $storedFilename);

        // Create import record
        $csvImport = CsvImport::create([
            'user_id'         => $user->id,
            'filename'        => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'file_path'       => $filePath,
            'file_size'       => $file->getSize(),
            'status'          => 'processing',
            'import_type'     => 'transactions',
        ]);

        try {
            $result = $this->processFile($file->getRealPath(), $csvImport);
            $csvImport->update([
                'status'       => 'completed',
                'total_rows'   => $result['total'],
                'success_rows' => $result['success'],
                'failed_rows'  => $result['failed'],
                'error_log'    => $result['failed'] > 0 ? implode("\n", $this->errors) : null,
                'processed_at' => now(),
            ]);

            return [
                'success'      => true,
                'success_rows' => $result['success'],
                'failed_rows'  => $result['failed'],
                'import_id'    => $csvImport->id,
            ];
        } catch (\Exception $e) {
            $csvImport->update([
                'status'    => 'failed',
                'error_log' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function processFile(string $filePath, CsvImport $csvImport): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) throw new \Exception('Tidak dapat membuka file CSV.');

        // Read header row
        $headers = fgetcsv($handle);
        if (!$headers) throw new \Exception('File CSV kosong atau format tidak valid.');

        // Normalize headers
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $total   = 0;
        $success = 0;
        $failed  = 0;
        $this->errors = [];

        $marketplaces = Marketplace::pluck('id', 'slug');
        $products     = Product::pluck('id', 'sku');

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $total++;
                $data = array_combine($headers, $row);

                try {
                    $this->processRow($data, $csvImport->id, $marketplaces, $products);
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    $this->errors[] = "Baris {$total}: " . $e->getMessage();
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        fclose($handle);
        return compact('total', 'success', 'failed');
    }

    protected function processRow(array $data, int $importId, $marketplaces, $products): void
    {
        // Validate required fields
        $required = ['transaction_date', 'marketplace_slug', 'product_sku', 'quantity', 'revenue'];
        foreach ($required as $field) {
            if (empty($data[$field] ?? null)) {
                throw new \Exception("Kolom '{$field}' wajib diisi.");
            }
        }

        // Resolve marketplace
        $slug = strtolower(trim($data['marketplace_slug']));
        if (!isset($marketplaces[$slug])) {
            throw new \Exception("Marketplace '{$slug}' tidak ditemukan.");
        }

        // Resolve product
        $sku = strtoupper(trim($data['product_sku']));
        if (!isset($products[$sku])) {
            throw new \Exception("Produk SKU '{$sku}' tidak ditemukan.");
        }

        // Parse date
        try {
            $date = Carbon::parse($data['transaction_date'])->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Format tanggal tidak valid: {$data['transaction_date']}");
        }

        $quantity = (int) ($data['quantity'] ?? 0);
        $revenue  = (float) ($data['revenue'] ?? 0);

        if ($quantity <= 0) throw new \Exception("Quantity harus lebih dari 0.");
        if ($revenue < 0)   throw new \Exception("Revenue tidak boleh negatif.");

        Transaction::create([
            'marketplace_id'    => $marketplaces[$slug],
            'product_id'        => $products[$sku],
            'csv_import_id'     => $importId,
            'order_id'          => $data['order_id'] ?? null,
            'transaction_date'  => $date,
            'quantity'          => $quantity,
            'unit_price'        => (float) ($data['unit_price'] ?? ($revenue / $quantity)),
            'revenue'           => $revenue,
            'cogs'              => (float) ($data['cogs'] ?? 0),
            'advertising_spend' => (float) ($data['advertising_spend'] ?? 0),
            'platform_fee'      => (float) ($data['platform_fee'] ?? 0),
            'shipping_subsidy'  => (float) ($data['shipping_subsidy'] ?? 0),
            'discount'          => (float) ($data['discount'] ?? 0),
            'customer_city'     => $data['customer_city'] ?? null,
            'status'            => in_array($data['status'] ?? '', ['completed', 'cancelled', 'returned', 'pending'])
                ? $data['status']
                : 'completed',
            'notes'             => $data['notes'] ?? null,
        ]);
    }
}
