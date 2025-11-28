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
            // Remover coluna tipo_professor após migração de dados
            $table->dropColumn('tipo_professor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planejamentos', function (Blueprint $table) {
            // Restaurar a coluna sem depender da ordem/coluna turma_id
            if (!Schema::hasColumn('planejamentos', 'tipo_professor')) {
                $table->string('tipo_professor')->nullable();
            }
        });
    }
};