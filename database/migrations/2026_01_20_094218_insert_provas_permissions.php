<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $modulo = 'Provas';
        $permissoes = [
            ['nome' => 'provas.visualizar', 'modulo' => $modulo, 'descricao' => 'Visualizar listagem de provas', 'ativo' => true],
            ['nome' => 'provas.criar', 'modulo' => $modulo, 'descricao' => 'Criar novas provas', 'ativo' => true],
            ['nome' => 'provas.editar', 'modulo' => $modulo, 'descricao' => 'Editar provas existentes', 'ativo' => true],
            ['nome' => 'provas.excluir', 'modulo' => $modulo, 'descricao' => 'Excluir provas', 'ativo' => true],
            ['nome' => 'provas.exportar', 'modulo' => $modulo, 'descricao' => 'Exportar provas para PDF', 'ativo' => true],
        ];

        foreach ($permissoes as $permissao) {
            $permissaoId = DB::table('permissoes')->insertGetId(array_merge($permissao, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Atribuir a cargos comuns (Professor e Admin)
            $cargos = DB::table('cargos')->whereIn('nome', ['Professor', 'Admin', 'Administrador', 'Diretor', 'Coordenador'])->get();
            foreach ($cargos as $cargo) {
                DB::table('cargo_permissoes')->insert([
                    'cargo_id' => $cargo->id,
                    'permissao_id' => $permissaoId,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $nomes = ['provas.visualizar', 'provas.criar', 'provas.editar', 'provas.excluir', 'provas.exportar'];
        $permissaoIds = DB::table('permissoes')->whereIn('nome', $nomes)->pluck('id');

        DB::table('cargo_permissoes')->whereIn('permissao_id', $permissaoIds)->delete();
        DB::table('permissoes')->whereIn('id', $permissaoIds)->delete();
    }
};
