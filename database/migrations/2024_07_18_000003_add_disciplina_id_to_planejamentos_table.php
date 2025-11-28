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
        Schema::table('planejamentos', function (Blueprint $table) {
            // Adicionar coluna disciplina_id de forma resiliente
            if (!Schema::hasColumn('planejamentos', 'disciplina_id')) {
                $table->foreignId('disciplina_id')
                    ->nullable()
                    ->constrained('disciplinas')
                    ->onDelete('set null');
                // Índice para consultas por disciplina
                $table->index(['disciplina_id']);
            }

            // Não remover tipo_professor imediatamente para permitir migração de dados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planejamentos', function (Blueprint $table) {
            if (Schema::hasColumn('planejamentos', 'disciplina_id')) {
                // Remover FK e coluna com segurança
                try { $table->dropForeign(['disciplina_id']); } catch (\Throwable $e) { /* ignore */ }
                try { $table->dropIndex(['disciplina_id']); } catch (\Throwable $e) { /* ignore */ }
                $table->dropColumn('disciplina_id');
            }
        });
    }
};