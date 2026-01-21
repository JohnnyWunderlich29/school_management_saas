<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('provas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escola_id')->constrained('escolas')->onDelete('cascade');
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade');
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade');
            $table->foreignId('funcionario_id')->constrained('funcionarios')->onDelete('cascade'); // Professor
            $table->foreignId('grade_aula_id')->nullable()->constrained('grade_aulas')->onDelete('set null'); // Slot de tempo

            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->date('data_aplicacao');
            $table->enum('status', ['rascunho', 'publicada', 'finalizada'])->default('rascunho');

            $table->timestamps();
        });

        Schema::create('questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prova_id')->constrained('provas')->onDelete('cascade');
            $table->enum('tipo', ['multipla_escolha', 'descritiva']);
            $table->text('enunciado');
            $table->string('imagem_path')->nullable();
            $table->integer('ordem')->default(0);
            $table->decimal('valor', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('questao_alternativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questao_id')->constrained('questoes')->onDelete('cascade');
            $table->text('texto');
            $table->boolean('correta')->default(false);
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('questao_alternativas');
        Schema::dropIfExists('questoes');
        Schema::dropIfExists('provas');
    }
};
