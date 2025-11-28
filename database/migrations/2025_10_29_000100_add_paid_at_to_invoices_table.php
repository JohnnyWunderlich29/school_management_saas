<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dateTime('paid_at')->nullable()->after('status');
            $table->index(['school_id', 'paid_at']);
        });

        // Backfill: para faturas já pagas, usar updated_at como proxy de data de pagamento
        DB::table('invoices')
            ->where('status', 'paid')
            ->whereNull('paid_at')
            ->update(['paid_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'paid_at']);
            $table->dropColumn('paid_at');
        });
    }
};