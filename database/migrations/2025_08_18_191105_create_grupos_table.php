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
        Schema::create('grupos', function (Blueprint $table) {
            $table->id();
            $table->string('nome'); // Ex: Maternal I, 1º Ano, 6º Ano, 1º Ano EM
            $table->string('codigo')->unique(); // Ex: MAT1, 1ANO, 6ANO, 1EM
            $table->foreignId('modalidade_ensino_id')->constrained('modalidades_ensino');
            $table->integer('idade_minima')->nullable(); // Para Educação Infantil
            $table->integer('idade_maxima')->nullable(); // Para Educação Infantil
            $table->integer('ano_serie')->nullable(); // Para Fundamental e Médio (1-9 para Fund, 1-3 para Médio)
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0); // Para ordenação
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos');
    }
};
