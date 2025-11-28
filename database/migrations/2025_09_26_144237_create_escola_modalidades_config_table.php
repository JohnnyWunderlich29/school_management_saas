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
        Schema::create('escola_modalidades_config', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com escola
            $table->unsignedBigInteger('escola_id');
            $table->foreign('escola_id')->references('id')->on('escolas')->onDelete('cascade');
            
            // Relacionamento com modalidade de ensino
            $table->unsignedBigInteger('modalidade_ensino_id');
            $table->foreign('modalidade_ensino_id')->references('id')->on('modalidades_ensino')->onDelete('cascade');
            
            // Configurações específicas da escola para esta modalidade
            $table->boolean('ativo')->default(true)->comment('Se a modalidade está ativa para esta escola');
            $table->integer('capacidade_maxima_turma')->nullable()->comment('Capacidade máxima personalizada para turmas desta modalidade');
            $table->integer('capacidade_minima_turma')->default(1)->comment('Capacidade mínima para formar turma');
            
            // Configurações de turnos permitidos para esta escola/modalidade
            $table->boolean('permite_turno_matutino')->default(true);
            $table->boolean('permite_turno_vespertino')->default(true);
            $table->boolean('permite_turno_noturno')->default(false);
            $table->boolean('permite_turno_integral')->default(false);
            
            // Configurações específicas da escola
            $table->json('configuracoes_extras')->nullable()->comment('Configurações específicas em JSON');
            $table->text('observacoes')->nullable()->comment('Observações específicas da escola para esta modalidade');
            
            // Data de ativação/desativação
            $table->date('data_ativacao')->nullable();
            $table->date('data_desativacao')->nullable();
            
            // Auditoria
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Índices únicos e compostos
            $table->unique(['escola_id', 'modalidade_ensino_id'], 'unique_escola_modalidade');
            $table->index(['escola_id', 'ativo'], 'idx_escola_modalidade_ativo');
            $table->index('data_ativacao');
            $table->index('data_desativacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('escola_modalidades_config');
    }
};
