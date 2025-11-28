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
        Schema::create('tempo_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turno_id')->constrained('turnos')->onDelete('cascade');
            $table->string('nome'); // Ex: 1º Tempo, 2º Tempo, Recreio, Almoço
            $table->string('tipo')->default('aula'); // aula, recreio, almoco, intervalo
            $table->time('hora_inicio');
            $table->time('hora_fim');
            $table->integer('ordem'); // Ordem sequencial dentro do turno
            $table->integer('duracao_minutos'); // Duração em minutos (calculado automaticamente)
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            
            // Índices para performance
            $table->index(['turno_id', 'ordem']);
            $table->index(['turno_id', 'ativo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tempo_slots');
    }
};
