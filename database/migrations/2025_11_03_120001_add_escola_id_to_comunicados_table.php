<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('comunicados') && !Schema::hasColumn('comunicados', 'escola_id')) {
            Schema::table('comunicados', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->after('autor_id')->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });

            // Backfill: set escola_id based on autor_id's escola_id
            try {
                DB::statement('UPDATE comunicados SET escola_id = (
                    SELECT users.escola_id FROM users WHERE users.id = comunicados.autor_id
                ) WHERE escola_id IS NULL');
            } catch (\Throwable $e) {
                // Ignore backfill errors to keep migration forward-only
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('comunicados') && Schema::hasColumn('comunicados', 'escola_id')) {
            Schema::table('comunicados', function (Blueprint $table) {
                try { $table->dropForeign(['escola_id']); } catch (\Throwable $e) { /* ignore */ }
                try { $table->dropIndex(['escola_id']); } catch (\Throwable $e) { /* ignore */ }
                $table->dropColumn('escola_id');
            });
        }
    }
};