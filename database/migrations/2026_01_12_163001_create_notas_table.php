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
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->foreignId('disciplina_id')->constrained('disciplinas');
            $table->foreignId('professor_id')->nullable()->constrained('funcionarios');
            $table->foreignId('escola_id')->constrained('escolas');
            $table->decimal('valor', 5, 2);
            $table->string('referencia'); // ex: 1º Bimestre, AV1, Recuperação
            $table->date('data_lancamento');
            $table->text('observacoes')->nullable();
            $table->timestamps();

            // Índices para performance e isolamento
            $table->index(['aluno_id', 'escola_id']);
            $table->index(['disciplina_id', 'escola_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
