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
        Schema::create('aluno_anotacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->foreignId('escola_id')->constrained('escolas');
            $table->foreignId('usuario_id')->constrained('users');
            $table->string('tipo')->default('comum'); // comum, grave, elogio, advertencia
            $table->string('titulo');
            $table->text('descricao');
            $table->date('data_ocorrencia');
            $table->timestamps();

            // Índices para performance e isolamento
            $table->index(['aluno_id', 'escola_id']);
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aluno_anotacoes');
    }
};
