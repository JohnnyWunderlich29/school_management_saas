<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;

class GradeAulasPermissoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando permissões para Grade de Aulas...');

        // Permissões da Grade de Aulas
        $permissoes = [
            ['nome' => 'grade_aulas.visualizar', 'descricao' => 'Visualizar grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.listar', 'descricao' => 'Listar grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.criar', 'descricao' => 'Criar grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.editar', 'descricao' => 'Editar grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.excluir', 'descricao' => 'Excluir grade de aulas', 'modulo' => 'Grade de Aulas'],
            ['nome' => 'grade_aulas.gerenciar', 'descricao' => 'Gerenciar grade de aulas completa', 'modulo' => 'Grade de Aulas'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::updateOrCreate(
                ['nome' => $permissao['nome']],
                $permissao
            );
        }

        $this->command->info('Permissões da Grade de Aulas criadas com sucesso!');

        // Atualizar permissões dos cargos existentes
        $this->atualizarPermissoesCargos();
    }

    /**
     * Atualizar permissões dos cargos existentes
     */
    private function atualizarPermissoesCargos(): void
    {
        $this->command->info('Atualizando permissões dos cargos...');

        // Administrador de Escola - acesso completo
        $cargoAdmin = Cargo::where('nome', 'Administrador de Escola')->first();
        if ($cargoAdmin) {
            $permissoesAdmin = Permissao::whereIn('nome', [
                'grade_aulas.visualizar',
                'grade_aulas.listar',
                'grade_aulas.criar',
                'grade_aulas.editar',
                'grade_aulas.excluir',
                'grade_aulas.gerenciar'
            ])->get();
            
            $permissoesExistentes = $cargoAdmin->permissoes()->pluck('permissoes.id')->toArray();
            $novasPermissoes = $permissoesAdmin->pluck('id')->toArray();
            $cargoAdmin->permissoes()->sync(array_unique(array_merge($permissoesExistentes, $novasPermissoes)));
        }

        // Coordenador - acesso completo
        $cargoCoordenador = Cargo::where('nome', 'Coordenador')->first();
        if ($cargoCoordenador) {
            $permissoesCoordenador = Permissao::whereIn('nome', [
                'grade_aulas.visualizar',
                'grade_aulas.listar',
                'grade_aulas.criar',
                'grade_aulas.editar',
                'grade_aulas.excluir',
                'grade_aulas.gerenciar'
            ])->get();
            
            $permissoesExistentes = $cargoCoordenador->permissoes()->pluck('permissoes.id')->toArray();
            $novasPermissoes = $permissoesCoordenador->pluck('id')->toArray();
            $cargoCoordenador->permissoes()->sync(array_unique(array_merge($permissoesExistentes, $novasPermissoes)));
        }

        // Professor - apenas visualização
        $cargoProfessor = Cargo::where('nome', 'Professor')->first();
        if ($cargoProfessor) {
            $permissoesProfessor = Permissao::whereIn('nome', [
                'grade_aulas.visualizar',
                'grade_aulas.listar'
            ])->get();
            
            $permissoesExistentes = $cargoProfessor->permissoes()->pluck('permissoes.id')->toArray();
            $novasPermissoes = $permissoesProfessor->pluck('id')->toArray();
            $cargoProfessor->permissoes()->sync(array_unique(array_merge($permissoesExistentes, $novasPermissoes)));
        }

        // Secretário - visualização e criação
        $cargoSecretario = Cargo::where('nome', 'Secretário')->first();
        if ($cargoSecretario) {
            $permissoesSecretario = Permissao::whereIn('nome', [
                'grade_aulas.visualizar',
                'grade_aulas.listar',
                'grade_aulas.criar',
                'grade_aulas.editar'
            ])->get();
            
            $permissoesExistentes = $cargoSecretario->permissoes()->pluck('permissoes.id')->toArray();
            $novasPermissoes = $permissoesSecretario->pluck('id')->toArray();
            $cargoSecretario->permissoes()->sync(array_unique(array_merge($permissoesExistentes, $novasPermissoes)));
        }

        $this->command->info('Permissões dos cargos atualizadas com sucesso!');
    }
}