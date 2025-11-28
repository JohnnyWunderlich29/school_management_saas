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
        Schema::table('planejamentos', function (Blueprint $table) {
            // Relacionamentos opcionais
            if (!Schema::hasColumn('planejamentos', 'turma_id')) {
                $table->foreignId('turma_id')->nullable()->constrained('turmas')->onDelete('set null');
                $table->index(['turma_id']);
            }
            if (!Schema::hasColumn('planejamentos', 'disciplina_id')) {
                $table->foreignId('disciplina_id')->nullable()->constrained('disciplinas')->onDelete('set null');
                $table->index(['disciplina_id']);
            }
            if (!Schema::hasColumn('planejamentos', 'professor_id')) {
                $table->foreignId('professor_id')->nullable()->constrained('users')->onDelete('set null');
                $table->index(['professor_id']);
            }
            if (!Schema::hasColumn('planejamentos', 'turno_id')) {
                $table->foreignId('turno_id')->nullable()->constrained('turnos')->onDelete('set null');
                $table->index(['turno_id']);
            }
            // IDs de referência (podem não ter FKs explícitas dependendo do schema do projeto)
            if (!Schema::hasColumn('planejamentos', 'modalidade_id')) {
                $table->unsignedBigInteger('modalidade_id')->nullable();
                $table->index(['modalidade_id']);
            }
            if (!Schema::hasColumn('planejamentos', 'nivel_ensino_id')) {
                $table->unsignedBigInteger('nivel_ensino_id')->nullable();
                $table->index(['nivel_ensino_id']);
            }

            // Campos de período
            if (!Schema::hasColumn('planejamentos', 'data_inicio')) {
                $table->date('data_inicio')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'data_fim')) {
                $table->date('data_fim')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'numero_dias')) {
                $table->unsignedSmallInteger('numero_dias')->nullable();
            }

            // Metadados gerais
            if (!Schema::hasColumn('planejamentos', 'status')) {
                $table->string('status')->default('rascunho');
                $table->index(['status']);
            }
            if (!Schema::hasColumn('planejamentos', 'unidade_escolar')) {
                $table->string('unidade_escolar')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'professor_responsavel')) {
                $table->string('professor_responsavel')->nullable();
            }

            // Carga horária e métricas
            if (!Schema::hasColumn('planejamentos', 'carga_horaria_aula')) {
                $table->decimal('carga_horaria_aula', 4, 2)->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'carga_horaria_total')) {
                $table->decimal('carga_horaria_total', 6, 2)->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'aulas_por_semana')) {
                $table->unsignedTinyInteger('aulas_por_semana')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'total_aulas')) {
                $table->unsignedSmallInteger('total_aulas')->nullable();
            }

            // Período letivo
            if (!Schema::hasColumn('planejamentos', 'tipo_periodo')) {
                $table->string('tipo_periodo')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'bimestre')) {
                $table->unsignedTinyInteger('bimestre')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'ano_letivo')) {
                $table->unsignedSmallInteger('ano_letivo')->nullable();
            }

            // Conteúdos pedagógicos
            if (!Schema::hasColumn('planejamentos', 'objetivo_geral')) {
                $table->text('objetivo_geral')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'objetivos_especificos')) {
                $table->json('objetivos_especificos')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'competencias_bncc')) {
                $table->json('competencias_bncc')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'habilidades_bncc')) {
                $table->json('habilidades_bncc')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'metodologia')) {
                $table->text('metodologia')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'recursos_necessarios')) {
                $table->json('recursos_necessarios')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'avaliacao_metodos')) {
                $table->json('avaliacao_metodos')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'observacoes')) {
                $table->text('observacoes')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'observacoes_finais')) {
                $table->text('observacoes_finais')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'campos_experiencia')) {
                $table->json('campos_experiencia')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'saberes_conhecimentos')) {
                $table->text('saberes_conhecimentos')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'objetivos_aprendizagem')) {
                $table->json('objetivos_aprendizagem')->nullable();
            }

            // Compatibilidade com campos textuais
            if (!Schema::hasColumn('planejamentos', 'modalidade')) {
                $table->string('modalidade')->nullable();
            }
            if (!Schema::hasColumn('planejamentos', 'nivel_ensino')) {
                $table->string('nivel_ensino')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planejamentos', function (Blueprint $table) {
            // Remover índices e colunas; ignorar erros caso não existam
            foreach ([
                'turma_id','disciplina_id','professor_id','turno_id','modalidade_id','nivel_ensino_id',
                'data_inicio','data_fim','numero_dias','status','unidade_escolar','professor_responsavel',
                'carga_horaria_aula','carga_horaria_total','aulas_por_semana','total_aulas',
                'tipo_periodo','bimestre','ano_letivo','objetivo_geral','objetivos_especificos',
                'competencias_bncc','habilidades_bncc','metodologia','recursos_necessarios','avaliacao_metodos',
                'observacoes','observacoes_finais','campos_experiencia','saberes_conhecimentos','objetivos_aprendizagem',
                'modalidade','nivel_ensino'
            ] as $col) {
                if (Schema::hasColumn('planejamentos', $col)) {
                    // Remover possíveis índices
                    try { $table->dropIndex([$col]); } catch (\Throwable $e) { /* ignore */ }
                    $table->dropColumn($col);
                }
            }

            // Remover FKs explicitamente
            foreach ([
                ['col' => 'turma_id'],
                ['col' => 'disciplina_id'],
                ['col' => 'professor_id'],
                ['col' => 'turno_id'],
            ] as $fk) {
                if (Schema::hasColumn('planejamentos', $fk['col'])) {
                    try { $table->dropForeign([$fk['col']]); } catch (\Throwable $e) { /* ignore */ }
                }
            }
        });
    }
};
