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
        Schema::create('escola_niveis_config', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com escola
            $table->unsignedBigInteger('escola_id');
            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
            
            // Relacionamento com nível de ensino
            $table->unsignedBigInteger('nivel_ensino_id');
            $table->foreign('nivel_ensino_id')->references('id')->on('niveis_ensino')->onDelete('cascade');
            
            // Configurações específicas da escola para este nível
            $table->boolean('ativo')->default(true)->comment('Se o nível está ativo para esta escola');
            $table->integer('capacidade_maxima_turma')->nullable()->comment('Capacidade máxima personalizada para turmas deste nível');
            $table->integer('capacidade_minima_turma')->default(1)->comment('Capacidade mínima para formar turma');
            $table->integer('capacidade_padrao_turma')->nullable()->comment('Capacidade padrão sugerida para este nível na escola');
            
            // Configurações de turnos permitidos para esta escola/nível
            $table->boolean('permite_turno_matutino')->default(true);
            $table->boolean('permite_turno_vespertino')->default(true);
            $table->boolean('permite_turno_noturno')->default(false);
            $table->boolean('permite_turno_integral')->default(false);
            
            // Configurações pedagógicas específicas
            $table->integer('carga_horaria_semanal')->nullable()->comment('Carga horária semanal em minutos');
            $table->integer('numero_aulas_semana')->nullable()->comment('Número de aulas por semana');
            $table->integer('duracao_aula_minutos')->nullable()->comment('Duração de cada aula em minutos');
            
            // Configurações de idade
            $table->integer('idade_minima')->nullable()->comment('Idade mínima para este nível na escola');
            $table->integer('idade_maxima')->nullable()->comment('Idade máxima para este nível na escola');
            
            // Configurações específicas da escola
            $table->json('configuracoes_extras')->nullable()->comment('Configurações específicas em JSON');
            $table->text('observacoes')->nullable()->comment('Observações específicas da escola para este nível');
            
            // Data de ativação/desativação
            $table->date('data_ativacao')->nullable();
            $table->date('data_desativacao')->nullable();
            
            // Auditoria
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Índices únicos e compostos
            $table->unique(['escola_id', 'nivel_ensino_id'], 'unique_escola_nivel');
            $table->index(['escola_id', 'ativo'], 'idx_escola_nivel_ativo');
            $table->index('data_ativacao');
            $table->index('data_desativacao');
            $table->index(['idade_minima', 'idade_maxima'], 'idx_faixa_etaria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escola_niveis_config');
    }
};
