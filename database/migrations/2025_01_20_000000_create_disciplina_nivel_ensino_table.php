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
        Schema::create('disciplina_nivel_ensino', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade');
            $table->foreignId('nivel_ensino_id')->constrained('niveis_ensino')->onDelete('cascade');
            $table->integer('carga_horaria_semanal')->default(2); // Horas por semana
            $table->integer('carga_horaria_anual')->default(80); // Horas por ano
            $table->boolean('obrigatoria')->default(true);
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->timestamps();
            
            // Índices
            $table->unique(['disciplina_id', 'nivel_ensino_id']);
            $table->index(['nivel_ensino_id', 'ordem']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplina_nivel_ensino');
    }
};