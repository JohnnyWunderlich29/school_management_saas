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
        Schema::create('grade_horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade');
            $table->foreignId('funcionario_id')->constrained('funcionarios')->onDelete('cascade');
            $table->foreignId('disciplina_id')->nullable()->constrained('disciplinas')->onDelete('set null');
            $table->foreignId('sala_id')->nullable()->constrained('salas')->onDelete('set null');
            $table->integer('dia_semana'); // 1 = Segunda, 2 = Terça, etc.
            $table->integer('tempo_aula'); // 1 = Primeiro tempo, 2 = Segundo tempo, etc.
            $table->timestamps();
            
            // Índices para melhorar a performance de consultas
            $table->index(['turma_id', 'dia_semana', 'tempo_aula']);
            $table->index(['funcionario_id', 'dia_semana', 'tempo_aula']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_horarios');
    }
};
