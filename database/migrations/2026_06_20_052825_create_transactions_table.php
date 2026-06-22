<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_id')->constrained('marketplaces')->onDelete('restrict');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('csv_import_id')->nullable()->constrained('csv_imports')->onDelete('set null');

            $table->string('order_id', 100)->nullable();
            $table->date('transaction_date');
            $table->string('period_month', 7);              // YYYY-MM

            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('revenue', 15, 2);

            $table->decimal('cogs', 15, 2)->default(0);
            $table->decimal('advertising_spend', 15, 2)->default(0);
            $table->decimal('platform_fee', 15, 2)->default(0);
            $table->decimal('shipping_subsidy', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->decimal('profit_margin', 8, 2)->default(0);

            $table->string('customer_city', 100)->nullable();
            $table->enum('status', ['completed', 'cancelled', 'returned', 'pending'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('transaction_date');
            $table->index('period_month');
            $table->index(['marketplace_id', 'period_month']);
            $table->index(['product_id', 'period_month']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
