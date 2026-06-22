<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csv_imports', function (Blueprint $table) {
            $table->integer('duplicate_rows')->default(0)->after('failed_rows');
            $table->timestamp('started_at')->nullable()->after('processed_at');
            $table->timestamp('completed_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('csv_imports', function (Blueprint $table) {
            $table->dropColumn(['duplicate_rows', 'started_at', 'completed_at']);
        });
    }
};
