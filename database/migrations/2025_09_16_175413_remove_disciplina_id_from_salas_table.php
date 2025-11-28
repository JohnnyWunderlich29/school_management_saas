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
            // Remove a coluna disciplina_id se existir
            if (Schema::hasColumn('salas', 'disciplina_id')) {
                $table->dropForeign(['disciplina_id']);
                $table->dropColumn('disciplina_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            // Restaura a coluna disciplina_id
            $table->unsignedBigInteger('disciplina_id')->nullable();
            $table->foreign('disciplina_id')->references('id')->on('disciplinas')->onDelete('set null');
        });
    }
};
