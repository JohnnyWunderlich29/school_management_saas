<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('disciplinas') && !Schema::hasColumn('disciplinas', 'escola_id')) {
            Schema::table('disciplinas', function (Blueprint $table) {
                $table->foreignId('escola_id')->nullable()->constrained('escolas')->onDelete('cascade');
                $table->index(['escola_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('disciplinas') && Schema::hasColumn('disciplinas', 'escola_id')) {
            Schema::table('disciplinas', function (Blueprint $table) {
                try { $table->dropForeign(['escola_id']); } catch (\Throwable $e) { /* ignore */ }
                try { $table->dropIndex(['escola_id']); } catch (\Throwable $e) { /* ignore */ }
                $table->dropColumn('escola_id');
            });
        }
    }
};