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
        // Verificar se a coluna existe antes de tentar removê-la
        if (Schema::hasColumn('grupos', 'modalidade_ensino_id')) {
            // Remover a chave estrangeira se existir
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropForeign(['modalidade_ensino_id']);
            });
            
            // Remover a coluna
            Schema::table('grupos', function (Blueprint $table) {
                $table->dropColumn('modalidade_ensino_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->unsignedBigInteger('modalidade_ensino_id')->nullable();
            $table->foreign('modalidade_ensino_id')->references('id')->on('modalidades_ensino');
        });
    }
};
