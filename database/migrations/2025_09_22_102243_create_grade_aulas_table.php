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
        Schema::create('grade_aulas', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos principais
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade');
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade');
            $table->foreignId('funcionario_id')->constrained('funcionarios')->onDelete('cascade');
            $table->foreignId('sala_id')->constrained('salas')->onDelete('cascade');
            $table->foreignId('tempo_slot_id')->constrained('tempo_slots')->onDelete('cascade');
            
            // Informações temporais
            $table->enum('dia_semana', ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado']);
            $table->date('data_inicio')->nullable(); // Para períodos específicos
            $table->date('data_fim')->nullable();    // Para períodos específicos
            
            // Controle e observações
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            // Índices para performance
            $table->index(['turma_id', 'dia_semana', 'tempo_slot_id'], 'idx_turma_horario');
            $table->index(['funcionario_id', 'dia_semana', 'tempo_slot_id'], 'idx_professor_horario');
            $table->index(['sala_id', 'dia_semana', 'tempo_slot_id'], 'idx_sala_horario');
            $table->index(['disciplina_id', 'turma_id'], 'idx_disciplina_turma');
            
            // Constraints únicos para evitar conflitos
            // Um professor não pode estar em duas salas ao mesmo tempo
            $table->unique(['funcionario_id', 'dia_semana', 'tempo_slot_id', 'data_inicio', 'data_fim'], 'uk_professor_horario');
            
            // Uma sala não pode ter duas turmas ao mesmo tempo
            $table->unique(['sala_id', 'dia_semana', 'tempo_slot_id', 'data_inicio', 'data_fim'], 'uk_sala_horario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_aulas');
    }
};
