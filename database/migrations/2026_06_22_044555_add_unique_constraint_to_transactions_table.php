<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Unique constraint untuk cegah duplikat
            // Kombinasi: tanggal + marketplace + produk + order_id (jika ada)
            $table->unique(
                ['transaction_date', 'marketplace_id', 'product_id', 'order_id'],
                'unique_transaction'
            )->whereNotNull('order_id');

            // Alternatif jika order_id null, gunakan kombinasi lain
            // Ini akan mencegah duplikat data yang sama persis
            $table->unique(
                ['transaction_date', 'marketplace_id', 'product_id', 'quantity', 'revenue'],
                'unique_transaction_no_order'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique('unique_transaction');
            $table->dropUnique('unique_transaction_no_order');
        });
    }
};
