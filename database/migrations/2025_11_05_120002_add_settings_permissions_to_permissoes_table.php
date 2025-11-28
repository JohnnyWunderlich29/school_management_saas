<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissoes')) {
            return;
        }

        $permissoes = [
            ['nome' => 'finance.admin', 'descricao' => 'Administrar configurações financeiras', 'modulo' => 'Financeiro', 'ativo' => true],
            ['nome' => 'usuarios.editar', 'descricao' => 'Editar usuário existente', 'modulo' => 'Usuários', 'ativo' => true],
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

    public function down(): void
    {
        if (!Schema::hasTable('permissoes')) {
            return;
        }

        DB::table('permissoes')
            ->whereIn('nome', ['finance.admin', 'usuarios.editar'])
            ->delete();
    }
};