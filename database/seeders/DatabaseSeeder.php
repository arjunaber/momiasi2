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
        $tx1 = [];

        // ── TRANSACTIONS Bulan 2 (Februari 2025) ─────────────────────────
        $tx2 = [];

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
