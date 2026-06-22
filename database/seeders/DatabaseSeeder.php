<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── USERS ────────────────────────────────────────────────────────
        User::create(['name' => 'Administrator', 'email' => 'admin@momiasi.com',   'password' => Hash::make('password'), 'role' => 'admin',   'is_active' => true]);
        User::create(['name' => 'Manager',       'email' => 'manager@momiasi.com', 'password' => Hash::make('password'), 'role' => 'manager', 'is_active' => true]);

        // ── MARKETPLACES ─────────────────────────────────────────────────
        $shopee = Marketplace::create(['name' => 'Shopee',     'slug' => 'shopee', 'color' => '#EE4D2D', 'is_active' => true]);
        $tiktok = Marketplace::create(['name' => 'TikTok Shop', 'slug' => 'tiktok', 'color' => '#010101', 'is_active' => true]);

        // ── PRODUCTS ──────────────────────────────────────────────────────
        $productsData = [
            // Momiasi
            ['sku' => 'MOM-001', 'name' => 'Momiasi - Lullaby One Set Sleepwear',    'category' => 'Maternity & Nursing', 'size' => 'All Size', 'base_price' => 150000, 'selling_price' => 189000],
            ['sku' => 'MOM-002', 'name' => 'Momiasi - Breastpad Saver Silicone',     'category' => 'Maternity & Nursing', 'size' => 'All Size', 'base_price' => 35000, 'selling_price' => 45000],
            ['sku' => 'MOM-003', 'name' => 'Momiasi - Korset 2 in 1 Postpartum',     'category' => 'Maternity & Nursing', 'size' => 'S/M/L/XL', 'base_price' => 220000, 'selling_price' => 275000],
            ['sku' => 'MOM-004', 'name' => 'Momiasi - Breast Pad Washable Premium',  'category' => 'Maternity & Nursing', 'size' => 'All Size', 'base_price' => 50000, 'selling_price' => 65000],
            ['sku' => 'MOM-005', 'name' => 'Momiasi - Manset Ibu Hamil Menyusui',    'category' => 'Maternity & Nursing', 'size' => 'S/M/L/XL', 'base_price' => 65000, 'selling_price' => 85000],
            ['sku' => 'MOM-006', 'name' => 'Momiasi - Yuki Home Dress Premium',      'category' => 'Maternity & Nursing', 'size' => 'S/M/L/XL', 'base_price' => 130000, 'selling_price' => 165000],
            ['sku' => 'MOM-007', 'name' => 'Momiasi - Sabuk Korset Penyangga Bumil', 'category' => 'Maternity & Nursing', 'size' => 'S/M/L/XL', 'base_price' => 115000, 'selling_price' => 145000],
            ['sku' => 'MOM-008', 'name' => 'Momiasi - Home Dress Nursing Serena',    'category' => 'Maternity & Nursing', 'size' => 'S/M/L/XL', 'base_price' => 120000, 'selling_price' => 155000],
            ['sku' => 'MOM-009', 'name' => 'Momiasi - Home Dress Maternity Sienna',  'category' => 'Maternity & Nursing', 'size' => 'S/M/L/XL', 'base_price' => 115000, 'selling_price' => 149000],
            ['sku' => 'MOM-010', 'name' => 'Momiasi - Korset Sabuk Pasca Melahirkan', 'category' => 'Maternity & Nursing', 'size' => 'S/M/L/XL', 'base_price' => 155000, 'selling_price' => 195000],
            ['sku' => 'MOM-011', 'name' => 'Momiasi - Essential Breast Pump Electric', 'category' => 'Maternity & Nursing', 'size' => 'All Size', 'base_price' => 350000, 'selling_price' => 425000],
            // Little Mommies
            ['sku' => 'LM-001', 'name' => 'Little Mommies - Quinsy One Set Playsuit', 'category' => 'Baby & Kids', 'size' => '0-6M/6-12M/1-2Y', 'base_price' => 125000, 'selling_price' => 159000],
            ['sku' => 'LM-002', 'name' => 'Little Mommies - Noah Jumper Playsuit',   'category' => 'Baby & Kids', 'size' => '0-6M/6-12M/1-2Y', 'base_price' => 140000, 'selling_price' => 175000],
            ['sku' => 'LM-003', 'name' => 'Little Mommies - Bando Cotton Combed',    'category' => 'Baby & Kids', 'size' => 'All Size', 'base_price' => 20000, 'selling_price' => 25000],
            ['sku' => 'LM-004', 'name' => 'Little Mommies - Zoey Jumper Playsuit',   'category' => 'Baby & Kids', 'size' => '0-6M/6-12M/1-2Y', 'base_price' => 135000, 'selling_price' => 169000],
            ['sku' => 'LM-005', 'name' => 'Little Mommies - Swaddle Bayi Newborn',   'category' => 'Baby & Kids', 'size' => 'All Size', 'base_price' => 65000, 'selling_price' => 85000],
            ['sku' => 'LM-006', 'name' => 'Little Mommies - Jody One Set Sleepwear', 'category' => 'Baby & Kids', 'size' => '0-6M/6-12M/1-2Y', 'base_price' => 115000, 'selling_price' => 145000],
            ['sku' => 'LM-007', 'name' => 'Little Mommies - Claire Jumper Playsuit', 'category' => 'Baby & Kids', 'size' => '0-6M/6-12M/1-2Y', 'base_price' => 145000, 'selling_price' => 185000],
        ];

        $products = [];
        foreach ($productsData as $p) {
            $products[$p['sku']] = Product::create(array_merge($p, ['unit' => 'pcs', 'stock' => rand(50, 300), 'is_active' => true]));
        }

        // ── TRANSACTIONS Bulan 1 (Januari 2025) ──────────────────────────
        $tx1 = [
            // Shopee Jan
            ['ORD-SP-001', '2025-01-03', $shopee->id, 'MOM-001', 3, 189000, 567000, 450000, 45000, 28350, 15000, 0, 'Jakarta'],
            ['ORD-SP-002', '2025-01-05', $shopee->id, 'MOM-003', 2, 275000, 550000, 440000, 38000, 27500, 12000, 0, 'Surabaya'],
            ['ORD-SP-003', '2025-01-07', $shopee->id, 'MOM-011', 1, 425000, 425000, 350000, 55000, 21250, 10000, 0, 'Bandung'],
            ['ORD-SP-004', '2025-01-09', $shopee->id, 'LM-001', 4, 159000, 636000, 500000, 42000, 31800, 18000, 10000, 'Jakarta'],
            ['ORD-SP-005', '2025-01-11', $shopee->id, 'MOM-006', 3, 165000, 495000, 390000, 35000, 24750, 12000, 0, 'Medan'],
            ['ORD-SP-006', '2025-01-14', $shopee->id, 'LM-002', 2, 175000, 350000, 280000, 28000, 17500, 8000, 0, 'Yogyakarta'],
            ['ORD-SP-007', '2025-01-16', $shopee->id, 'MOM-005', 5, 85000, 425000, 325000, 40000, 21250, 10000, 5000, 'Semarang'],
            ['ORD-SP-008', '2025-01-18', $shopee->id, 'MOM-008', 2, 155000, 310000, 240000, 25000, 15500, 7000, 0, 'Bali'],
            ['ORD-SP-009', '2025-01-21', $shopee->id, 'LM-005', 6, 85000, 510000, 390000, 45000, 25500, 12000, 8000, 'Makassar'],
            ['ORD-SP-010', '2025-01-24', $shopee->id, 'MOM-002', 8, 45000, 360000, 280000, 30000, 18000, 8000, 5000, 'Jakarta'],
            ['ORD-SP-011', '2025-01-27', $shopee->id, 'MOM-010', 2, 195000, 390000, 310000, 32000, 19500, 9000, 0, 'Surabaya'],
            // TikTok Jan
            ['ORD-TT-001', '2025-01-04', $tiktok->id, 'MOM-001', 5, 189000, 945000, 750000, 85000, 47250, 22000, 0, 'Jakarta'],
            ['ORD-TT-002', '2025-01-06', $tiktok->id, 'LM-001', 6, 159000, 954000, 750000, 90000, 47700, 25000, 10000, 'Bali'],
            ['ORD-TT-003', '2025-01-08', $tiktok->id, 'MOM-006', 4, 165000, 660000, 520000, 65000, 33000, 18000, 0, 'Surabaya'],
            ['ORD-TT-004', '2025-01-10', $tiktok->id, 'MOM-011', 2, 425000, 850000, 700000, 95000, 42500, 20000, 0, 'Bandung'],
            ['ORD-TT-005', '2025-01-13', $tiktok->id, 'LM-002', 4, 175000, 700000, 560000, 72000, 35000, 18000, 5000, 'Jakarta'],
            ['ORD-TT-006', '2025-01-15', $tiktok->id, 'MOM-003', 3, 275000, 825000, 660000, 88000, 41250, 20000, 0, 'Medan'],
            ['ORD-TT-007', '2025-01-17', $tiktok->id, 'MOM-005', 7, 85000, 595000, 455000, 68000, 29750, 15000, 8000, 'Semarang'],
            ['ORD-TT-008', '2025-01-20', $tiktok->id, 'LM-006', 5, 145000, 725000, 575000, 75000, 36250, 18000, 5000, 'Malang'],
            ['ORD-TT-009', '2025-01-22', $tiktok->id, 'MOM-008', 4, 155000, 620000, 480000, 65000, 31000, 15000, 0, 'Yogyakarta'],
            ['ORD-TT-010', '2025-01-25', $tiktok->id, 'MOM-002', 12, 45000, 540000, 420000, 55000, 27000, 12000, 8000, 'Jakarta'],
            ['ORD-TT-011', '2025-01-28', $tiktok->id, 'LM-004', 4, 169000, 676000, 540000, 70000, 33800, 18000, 0, 'Surabaya'],
        ];

        // ── TRANSACTIONS Bulan 2 (Februari 2025) ─────────────────────────
        $tx2 = [
            // Shopee Feb
            ['ORD-SP-101', '2025-02-02', $shopee->id, 'MOM-001', 5, 189000, 945000, 750000, 68000, 47250, 22000, 0, 'Jakarta'],
            ['ORD-SP-102', '2025-02-04', $shopee->id, 'MOM-003', 3, 275000, 825000, 660000, 55000, 41250, 18000, 0, 'Surabaya'],
            ['ORD-SP-103', '2025-02-06', $shopee->id, 'LM-001', 6, 159000, 954000, 750000, 65000, 47700, 22000, 12000, 'Bandung'],
            ['ORD-SP-104', '2025-02-08', $shopee->id, 'MOM-011', 2, 425000, 850000, 700000, 72000, 42500, 18000, 0, 'Jakarta'],
            ['ORD-SP-105', '2025-02-10', $shopee->id, 'LM-002', 4, 175000, 700000, 560000, 45000, 35000, 15000, 0, 'Medan'],
            ['ORD-SP-106', '2025-02-12', $shopee->id, 'MOM-006', 5, 165000, 825000, 650000, 55000, 41250, 18000, 8000, 'Semarang'],
            ['ORD-SP-107', '2025-02-15', $shopee->id, 'MOM-005', 6, 85000, 510000, 390000, 48000, 25500, 12000, 5000, 'Yogyakarta'],
            ['ORD-SP-108', '2025-02-17', $shopee->id, 'LM-005', 8, 85000, 680000, 520000, 58000, 34000, 16000, 8000, 'Malang'],
            ['ORD-SP-109', '2025-02-19', $shopee->id, 'MOM-009', 4, 149000, 596000, 460000, 42000, 29800, 14000, 0, 'Bali'],
            ['ORD-SP-110', '2025-02-21', $shopee->id, 'MOM-002', 10, 45000, 450000, 350000, 38000, 22500, 10000, 5000, 'Jakarta'],
            ['ORD-SP-111', '2025-02-23', $shopee->id, 'LM-007', 3, 185000, 555000, 435000, 40000, 27750, 13000, 0, 'Makassar'],
            ['ORD-SP-112', '2025-02-25', $shopee->id, 'MOM-010', 3, 195000, 585000, 465000, 45000, 29250, 14000, 0, 'Surabaya'],
            // TikTok Feb
            ['ORD-TT-101', '2025-02-03', $tiktok->id, 'MOM-001', 8, 189000, 1512000, 1200000, 135000, 75600, 35000, 0, 'Jakarta'],
            ['ORD-TT-102', '2025-02-05', $tiktok->id, 'LM-001', 9, 159000, 1431000, 1125000, 125000, 71550, 32000, 15000, 'Bali'],
            ['ORD-TT-103', '2025-02-07', $tiktok->id, 'MOM-011', 3, 425000, 1275000, 1050000, 130000, 63750, 28000, 0, 'Surabaya'],
            ['ORD-TT-104', '2025-02-09', $tiktok->id, 'MOM-006', 7, 165000, 1155000, 910000, 110000, 57750, 26000, 10000, 'Jakarta'],
            ['ORD-TT-105', '2025-02-11', $tiktok->id, 'LM-002', 6, 175000, 1050000, 840000, 100000, 52500, 24000, 8000, 'Bandung'],
            ['ORD-TT-106', '2025-02-13', $tiktok->id, 'MOM-003', 5, 275000, 1375000, 1100000, 125000, 68750, 30000, 0, 'Medan'],
            ['ORD-TT-107', '2025-02-16', $tiktok->id, 'MOM-005', 10, 85000, 850000,  650000, 95000, 42500, 20000, 10000, 'Semarang'],
            ['ORD-TT-108', '2025-02-18', $tiktok->id, 'LM-006', 7, 145000, 1015000, 805000, 100000, 50750, 24000, 8000, 'Malang'],
            ['ORD-TT-109', '2025-02-20', $tiktok->id, 'MOM-008', 5, 155000, 775000,  600000, 82000, 38750, 18000, 0, 'Yogyakarta'],
            ['ORD-TT-110', '2025-02-22', $tiktok->id, 'MOM-002', 15, 45000, 675000,  525000, 72000, 33750, 15000, 10000, 'Jakarta'],
            ['ORD-TT-111', '2025-02-24', $tiktok->id, 'LM-004', 6, 169000, 1014000, 810000, 95000, 50700, 24000, 0, 'Surabaya'],
            ['ORD-TT-112', '2025-02-26', $tiktok->id, 'MOM-010', 4, 195000, 780000,  620000, 80000, 39000, 18000, 0, 'Palembang'],
        ];

        foreach (array_merge($tx1, $tx2) as $tx) {
            [$ordId, $date, $mpId, $sku, $qty, $unitPrice, $revenue, $cogs, $adspend, $platformFee, $shipping, $discount, $city] = $tx;
            Transaction::create([
                'marketplace_id'    => $mpId,
                'product_id'        => $products[$sku]->id,
                'order_id'          => $ordId,
                'transaction_date'  => $date,
                'quantity'          => $qty,
                'unit_price'        => $unitPrice,
                'revenue'           => $revenue,
                'cogs'              => $cogs,
                'advertising_spend' => $adspend,
                'platform_fee'      => $platformFee,
                'shipping_subsidy'  => $shipping,
                'discount'          => $discount,
                'customer_city'     => $city,
                'status'            => 'completed',
            ]);
        }
    }
}
