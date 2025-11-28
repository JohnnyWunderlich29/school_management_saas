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
            // Verifica se turma_id não existe antes de adicionar
            if (!Schema::hasColumn('salas', 'turma_id')) {
                $table->foreignId('turma_id')->nullable()->after('turno_id')->constrained('turmas')->onDelete('set null');
                $table->index(['turma_id']);
            }
            
            // Remove o campo turma string se existir
            if (Schema::hasColumn('salas', 'turma')) {
                $table->dropColumn('turma');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            // Restaurar campo turma string
            $table->string('turma', 10)->nullable()->after('turno_id');
            
            // Remover relação com turmas
            $table->dropForeign(['turma_id']);
            $table->dropIndex(['turma_id']);
            $table->dropColumn('turma_id');
        });
    }
};
