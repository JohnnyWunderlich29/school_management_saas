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
            // Não depender da coluna 'ativo' para ordenação
            $table->enum('modalidade_ensino', [
                'eja',
                'educacao_especial',
                'educacao_profissional',
                'educacao_do_campo',
                'educacao_escolar_indigena',
                'educacao_a_distancia'
            ])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            if (Schema::hasColumn('salas', 'modalidade_ensino')) {
                $table->dropColumn('modalidade_ensino');
            }
        });
    }
};
