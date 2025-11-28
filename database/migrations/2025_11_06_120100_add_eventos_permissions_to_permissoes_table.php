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
            ['nome' => 'eventos.ver', 'descricao' => 'Visualizar eventos escolares', 'modulo' => 'Eventos', 'ativo' => true],
            ['nome' => 'eventos.criar', 'descricao' => 'Criar eventos escolares', 'modulo' => 'Eventos', 'ativo' => true],
            ['nome' => 'eventos.editar', 'descricao' => 'Editar eventos escolares', 'modulo' => 'Eventos', 'ativo' => true],
            ['nome' => 'eventos.excluir', 'descricao' => 'Excluir eventos escolares', 'modulo' => 'Eventos', 'ativo' => true],
        ];

        foreach ($permissoes as $perm) {
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
                'eventos.ver',
                'eventos.criar',
                'eventos.editar',
                'eventos.excluir',
            ])
            ->delete();
    }
};