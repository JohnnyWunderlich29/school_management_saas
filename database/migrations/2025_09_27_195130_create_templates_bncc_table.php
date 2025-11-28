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
        Schema::create('templates_bncc', function (Blueprint $table) {
            $table->id();
            $table->string('categoria'); // Educação Infantil, Ensino Fundamental, Ensino Médio
            $table->string('subcategoria')->nullable(); // Creche, Pré-escola, Anos Iniciais, Anos Finais
            $table->string('nome'); // Nome do nível (ex: "Grupo 1", "1º ano", "1ª série")
            $table->string('codigo')->unique(); // Código único (ex: "EI_CRECHE_G1", "EF_AI_1ANO")
            $table->text('descricao'); // Descrição detalhada
            $table->integer('idade_minima'); // Idade mínima em meses
            $table->integer('idade_maxima'); // Idade máxima em meses
            $table->integer('capacidade_padrao')->default(25); // Capacidade padrão da turma
            $table->integer('capacidade_minima')->default(15); // Capacidade mínima
            $table->integer('capacidade_maxima')->default(30); // Capacidade máxima
            $table->integer('carga_horaria_semanal')->default(20); // Carga horária semanal em horas
            $table->integer('numero_aulas_dia')->default(4); // Número de aulas por dia
            $table->integer('duracao_aula_minutos')->default(50); // Duração da aula em minutos
            $table->boolean('turno_matutino')->default(true);
            $table->boolean('turno_vespertino')->default(true);
            $table->boolean('turno_noturno')->default(false);
            $table->boolean('turno_integral')->default(false);
            $table->json('modalidades_compativeis')->nullable(); // Modalidades compatíveis
            $table->text('observacoes')->nullable(); // Observações específicas
            $table->boolean('ativo')->default(true);
            $table->integer('ordem')->default(0); // Para ordenação na exibição
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates_bncc');
    }
};
