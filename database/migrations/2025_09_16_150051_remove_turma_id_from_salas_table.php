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
        Schema::table('salas', function (Blueprint $table) {
            if (Schema::hasColumn('salas', 'turma_id')) {
                try { $table->dropForeign(['turma_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['turma_id']); } catch (\Throwable $e) {}
                $table->dropColumn('turma_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            if (!Schema::hasColumn('salas', 'turma_id')) {
                // Restaurar sem depender da coluna 'turma'
                $table->foreignId('turma_id')->nullable()->constrained('turmas')->onDelete('set null');
                $table->index(['turma_id']);
            }
        });
    }
};
