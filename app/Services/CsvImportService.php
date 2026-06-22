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
    protected array $duplicateRows = [];
    protected int $duplicateCount = 0;
    protected array $existingKeys = [];

    public function import(UploadedFile $file, $user): array
    {
        // Reset counters
        $this->errors = [];
        $this->duplicateRows = [];
        $this->duplicateCount = 0;
        $this->existingKeys = [];

        $storedFilename = 'csv_imports/' . uniqid() . '_' . $file->getClientOriginalName();
        $filePath = Storage::disk('local')->putFileAs('', $file, $storedFilename);

        // Create import record
        $csvImport = CsvImport::create([
            'user_id' => $user->id,
            'filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'file_path' => $filePath,
            'file_size' => $file->getSize(),
            'status' => 'processing',
            'import_type' => 'transactions',
            'started_at' => now(),
        ]);

        try {
            // Load existing transactions untuk cek duplikat
            $this->loadExistingTransactionKeys();

            $result = $this->processFile($file->getRealPath(), $csvImport);

            $csvImport->update([
                'status' => 'completed',
                'total_rows' => $result['total'],
                'success_rows' => $result['success'],
                'failed_rows' => $result['failed'],
                'duplicate_rows' => $this->duplicateCount,
                'error_log' => $result['failed'] > 0 ? implode("\n", $this->errors) : null,
                'processed_at' => now(),
                'completed_at' => now(),
            ]);

            $message = "Import berhasil! {$result['success']} transaksi ditambahkan";
            if ($result['failed'] > 0) {
                $message .= ", {$result['failed']} baris gagal.";
            }
            if ($this->duplicateCount > 0) {
                $message .= " {$this->duplicateCount} baris duplikat dilewati.";
            }

            return [
                'success' => true,
                'success_rows' => $result['success'],
                'failed_rows' => $result['failed'],
                'duplicate_rows' => $this->duplicateCount,
                'import_id' => $csvImport->id,
                'message' => $message,
            ];
        } catch (\Exception $e) {
            $csvImport->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Load existing transaction keys
     */
    protected function loadExistingTransactionKeys(): void
    {
        $transactions = Transaction::all();

        foreach ($transactions as $tx) {
            $key = $this->generateKey(
                $tx->transaction_date,
                $tx->marketplace_id,
                $tx->product_id,
                $tx->order_id ?? ''
            );
            $this->existingKeys[] = $key;
        }
    }

    /**
     * Generate unique key
     */
    protected function generateKey($date, $marketplaceId, $productId, $orderId = ''): string
    {
        if (!empty($orderId)) {
            return 'order|' . $orderId;
        }
        return 'trans|' . $date . '|' . $marketplaceId . '|' . $productId;
    }

    /**
     * Check duplicate
     */
    protected function isDuplicate(array $data, $marketplaces, $products): bool
    {
        $slug = strtolower(trim($data['marketplace_slug'] ?? ''));
        $sku = strtoupper(trim($data['product_sku'] ?? ''));

        if (empty($slug) || empty($sku)) {
            return false;
        }

        if (!isset($marketplaces[$slug]) || !isset($products[$sku])) {
            return false;
        }

        $marketplaceId = $marketplaces[$slug];
        $productId = $products[$sku];
        $orderId = $data['order_id'] ?? '';

        try {
            $date = $this->parseDate($data['transaction_date'] ?? '');
        } catch (\Exception $e) {
            return false;
        }

        $key = $this->generateKey($date, $marketplaceId, $productId, $orderId);

        if (in_array($key, $this->existingKeys)) {
            return true;
        }

        $this->existingKeys[] = $key;
        return false;
    }

    /**
     * 🔥 AUTO-DETECT DELIMITER
     */
    protected function detectDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
        $firstLine = fgets($handle);
        fclose($handle);

        $delimiters = [',', ';', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $delimiter) {
            $counts[$delimiter] = substr_count($firstLine, $delimiter);
        }

        // Return delimiter with highest count
        arsort($counts);
        return key($counts);
    }

    /**
     * 🔥 PARSE DATE - Support multiple formats
     */
    protected function parseDate(string $dateStr): string
    {
        $dateStr = trim($dateStr);

        if (empty($dateStr)) {
            throw new \Exception("Tanggal kosong");
        }

        // Format YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        // Format DD/MM/YYYY
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateStr)) {
            $parts = explode('/', $dateStr);
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }

        // Format DD-MM-YYYY
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateStr)) {
            $parts = explode('-', $dateStr);
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }

        // Format MM/DD/YYYY
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateStr)) {
            $parts = explode('/', $dateStr);
            return $parts[2] . '-' . $parts[0] . '-' . $parts[1];
        }

        // Coba pakai Carbon
        try {
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Format tanggal tidak valid: {$dateStr}");
        }
    }

    protected function processFile(string $filePath, CsvImport $csvImport): array
    {
        // 🔥 AUTO-DETECT DELIMITER
        $delimiter = $this->detectDelimiter($filePath);

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \Exception('Tidak dapat membuka file CSV.');
        }

        // Read header row with detected delimiter
        $headers = fgetcsv($handle, 0, $delimiter);
        if (!$headers) {
            throw new \Exception('File CSV kosong atau format tidak valid.');
        }

        // Normalize headers
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        // 🔥 LOG untuk debugging
        \Log::info('CSV Headers detected:', $headers);
        \Log::info('Delimiter detected: ' . $delimiter);

        // Validasi header wajib
        $required = ['transaction_date', 'marketplace_slug', 'product_sku', 'quantity', 'revenue'];
        $missing = array_diff($required, $headers);
        if (!empty($missing)) {
            fclose($handle);
            throw new \Exception('Header CSV tidak lengkap. Kolom yang hilang: ' . implode(', ', $missing) . '. Detected headers: ' . implode(', ', $headers));
        }

        $total = 0;
        $success = 0;
        $failed = 0;
        $this->errors = [];

        $marketplaces = Marketplace::pluck('id', 'slug');
        $products = Product::pluck('id', 'sku');

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $total++;

                // Pastikan jumlah kolom sesuai
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                }

                $data = array_combine($headers, $row);

                // 🔥 CEK DUPLIKAT
                if ($this->isDuplicate($data, $marketplaces, $products)) {
                    $this->duplicateCount++;
                    $this->duplicateRows[] = $total;
                    continue;
                }

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
            fclose($handle);
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

        // 🔥 Parse date - support multiple formats
        try {
            $date = $this->parseDate($data['transaction_date']);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $quantity = (int) ($data['quantity'] ?? 0);
        $revenue = (float) ($data['revenue'] ?? 0);

        if ($quantity <= 0) {
            throw new \Exception("Quantity harus lebih dari 0.");
        }
        if ($revenue < 0) {
            throw new \Exception("Revenue tidak boleh negatif.");
        }

        // Handle empty advertising_spend
        $advertisingSpend = (float) ($data['advertising_spend'] ?? 0);
        if (empty($data['advertising_spend']) || trim($data['advertising_spend']) === '') {
            $advertisingSpend = 0;
        }

        // Double check duplicate
        $existing = Transaction::where('transaction_date', $date)
            ->where('marketplace_id', $marketplaces[$slug])
            ->where('product_id', $products[$sku])
            ->when(!empty($data['order_id']), function ($query) use ($data) {
                return $query->where('order_id', $data['order_id']);
            })
            ->exists();

        if ($existing) {
            throw new \Exception("Data sudah ada (duplikat).");
        }

        Transaction::create([
            'marketplace_id' => $marketplaces[$slug],
            'product_id' => $products[$sku],
            'csv_import_id' => $importId,
            'order_id' => $data['order_id'] ?? null,
            'transaction_date' => $date,
            'quantity' => $quantity,
            'unit_price' => (float) ($data['unit_price'] ?? ($revenue / $quantity)),
            'revenue' => $revenue,
            'cogs' => (float) ($data['cogs'] ?? 0),
            'advertising_spend' => $advertisingSpend,
            'platform_fee' => (float) ($data['platform_fee'] ?? 0),
            'shipping_subsidy' => (float) ($data['shipping_subsidy'] ?? 0),
            'discount' => (float) ($data['discount'] ?? 0),
            'customer_city' => $data['customer_city'] ?? null,
            'status' => in_array($data['status'] ?? '', ['completed', 'cancelled', 'returned', 'pending'])
                ? $data['status']
                : 'completed',
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
