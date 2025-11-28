<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('permissoes')) {
            return; // Tabela não existe neste ambiente
        }

        $permissoes = [
            ['nome' => 'grade_aulas.visualizar', 'descricao' => 'Visualizar grade de aulas', 'modulo' => 'Grade de Aulas', 'ativo' => true],
            ['nome' => 'grade_aulas.listar',     'descricao' => 'Listar grade de aulas',     'modulo' => 'Grade de Aulas', 'ativo' => true],
            ['nome' => 'grade_aulas.criar',      'descricao' => 'Criar grade de aulas',      'modulo' => 'Grade de Aulas', 'ativo' => true],
            ['nome' => 'grade_aulas.editar',     'descricao' => 'Editar grade de aulas',     'modulo' => 'Grade de Aulas', 'ativo' => true],
            ['nome' => 'grade_aulas.excluir',    'descricao' => 'Excluir grade de aulas',    'modulo' => 'Grade de Aulas', 'ativo' => true],
            ['nome' => 'grade_aulas.gerenciar',  'descricao' => 'Gerenciar grade de aulas completa', 'modulo' => 'Grade de Aulas', 'ativo' => true],
        ];

        foreach ($permissoes as $perm) {
            // Monta payload de forma dinâmica, apenas com colunas existentes
            $data = [
                'nome' => $perm['nome'],
                'descricao' => $perm['descricao'],
            ];

            if (Schema::hasColumn('permissoes', 'modulo')) {
                $data['modulo'] = $perm['modulo'];
            }

            if (Schema::hasColumn('permissoes', 'ativo')) {
                $data['ativo'] = $perm['ativo'];
            }

            DB::table('permissoes')->updateOrInsert(
                ['nome' => $perm['nome']],
                $data
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('permissoes')) {
            return; // Tabela não existe neste ambiente
        }

        DB::table('permissoes')
            ->whereIn('nome', [
                'grade_aulas.visualizar',
                'grade_aulas.listar',
                'grade_aulas.criar',
                'grade_aulas.editar',
                'grade_aulas.excluir',
                'grade_aulas.gerenciar',
            ])
            ->delete();
    }
};