<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Retorna os nomes das FKs existentes para as colunas informadas
     */
    private function getForeignKeyConstraintNames(string $table, array $columns): array
    {
        // Em ambientes de teste usando SQLite, information_schema não existe.
        // Retorna vazio para evitar erros e permitir migração segura.
        if (DB::getDriverName() !== 'mysql') {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $bindings = array_merge([$table], $columns);
        $sql = "SELECT COLUMN_NAME, CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME IN ($placeholders)
                  AND REFERENCED_TABLE_NAME IS NOT NULL";
        $rows = DB::select($sql, $bindings);
        $map = [];
        foreach ($rows as $row) {
            $col = $row->COLUMN_NAME ?? $row->column_name ?? null;
            $name = $row->CONSTRAINT_NAME ?? $row->constraint_name ?? null;
            if ($col && $name) {
                $map[$col] = $name;
            }
        }
        return $map;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';
        $fkColumns = [
            'modalidade_ensino_id',
            'grupo_id',
            'turno_id',
            'turma_id',
            'coordenador_id',
            'unidade_escola_id',
        ];
        $fkConstraints = $this->getForeignKeyConstraintNames('salas', $fkColumns);

        Schema::table('salas', function (Blueprint $table) use ($isSqlite, $fkColumns, $fkConstraints) {
            // Adicionar campo descrição (opcional) apenas se não existir
            if (!Schema::hasColumn('salas', 'descricao')) {
                $table->text('descricao')->nullable()->after('codigo');
            }
            
            // Adicionar campo ativo se não existir
            if (!Schema::hasColumn('salas', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('descricao');
            }
            
            // Em SQLite, evite dropar colunas com FKs pois reconstrói a tabela e quebra testes
            if (!$isSqlite) {
                // Primeiro, remover foreign keys vinculadas às colunas antes de dropar
                foreach ($fkColumns as $fkColumn) {
                    if (Schema::hasColumn('salas', $fkColumn)) {
                        // Dropar somente se a constraint existir, evitando erros de nome
                        if (isset($fkConstraints[$fkColumn])) {
                            $table->dropForeign($fkConstraints[$fkColumn]);
                        }
                    }
                }

                // Remover campos desnecessários apenas se existirem
                $columnsToRemove = [
                    'capacidade',
                    'turno_matutino',
                    'turno_vespertino', 
                    'turno_noturno',
                    'turno_integral',
                    'modalidade_ensino_id',
                    'grupo_id',
                    'turno_id',
                    'turma_id',
                    'coordenador_id'
                ];
                
                foreach ($columnsToRemove as $column) {
                    if (Schema::hasColumn('salas', $column)) {
                        $table->dropColumn($column);
                    }
                }
            }
            
            // Remover unidade_escola_id se existir
            if (!$isSqlite && Schema::hasColumn('salas', 'unidade_escola_id')) {
                $table->dropColumn('unidade_escola_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salas', function (Blueprint $table) {
            // Restaurar campos removidos
            $table->integer('capacidade')->nullable();
            $table->boolean('turno_matutino')->default(false);
            $table->boolean('turno_vespertino')->default(false);
            $table->boolean('turno_noturno')->default(false);
            $table->boolean('turno_integral')->default(false);
            $table->unsignedBigInteger('modalidade_ensino_id')->nullable();
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->unsignedBigInteger('turno_id')->nullable();
            $table->unsignedBigInteger('turma_id')->nullable();
            $table->unsignedBigInteger('coordenador_id')->nullable();
            $table->unsignedBigInteger('unidade_escola_id')->nullable();
            
            // Remover campos adicionados (com segurança)
            if (Schema::hasColumn('salas', 'descricao')) {
                $table->dropColumn('descricao');
            }
            
            // Restaurar foreign keys
            $table->foreign('modalidade_ensino_id')->references('id')->on('modalidades_ensino');
            $table->foreign('grupo_id')->references('id')->on('grupos');
            $table->foreign('turno_id')->references('id')->on('turnos');
            $table->foreign('turma_id')->references('id')->on('turmas');
            $table->foreign('coordenador_id')->references('id')->on('users');
        });
    }
};
