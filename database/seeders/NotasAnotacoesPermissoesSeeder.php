<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;

class NotasAnotacoesPermissoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando permissões para Notas e Anotações...');

        // Permissões de Notas
        $permissoes = [
            ['nome' => 'notas.lancar', 'descricao' => 'Lançar notas de alunos', 'modulo' => 'Notas'],
            ['nome' => 'notas.excluir', 'descricao' => 'Excluir notas de alunos', 'modulo' => 'Notas'],
            
            ['nome' => 'anotacoes.registrar', 'descricao' => 'Registrar anotações/ocorrências de alunos', 'modulo' => 'Anotações'],
            ['nome' => 'anotacoes.excluir', 'descricao' => 'Excluir anotações/ocorrências de alunos', 'modulo' => 'Anotações'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::updateOrCreate(
                ['nome' => $permissao['nome']],
                $permissao
            );
        }

        $this->command->info('Permissões de Notas e Anotações criadas com sucesso!');

        // Atualizar permissões dos cargos existentes
        $this->atualizarPermissoesCargos();
    }

    /**
     * Atualizar permissões dos cargos existentes
     */
    private function atualizarPermissoesCargos(): void
    {
        $this->command->info('Atualizando permissões dos cargos...');

        // Cargos que terão permissão total
        $cargosFull = ['Administrador', 'Administrador de Escola', 'Coordenador', 'Secretário'];
        
        // Cargos que terão permissão apenas para lançar/registrar
        $cargosLimited = ['Professor'];

        $allPermNames = ['notas.lancar', 'notas.excluir', 'anotacoes.registrar', 'anotacoes.excluir'];
        $limitedPermNames = ['notas.lancar', 'anotacoes.registrar'];

        // Administradores e Coordenadores
        foreach ($cargosFull as $nomeCargo) {
            $cargo = Cargo::where('nome', $nomeCargo)->first();
            if ($cargo) {
                $permissoes = Permissao::whereIn('nome', $allPermNames)->get();
                $permissoesExistentes = $cargo->permissoes()->pluck('permissoes.id')->toArray();
                $novasPermissoes = $permissoes->pluck('id')->toArray();
                $cargo->permissoes()->sync(array_unique(array_merge($permissoesExistentes, $novasPermissoes)));
                $this->command->info("Permissões concedidas ao cargo: {$nomeCargo}");
            }
        }

        // Professores
        foreach ($cargosLimited as $nomeCargo) {
            $cargo = Cargo::where('nome', $nomeCargo)->first();
            if ($cargo) {
                $permissoes = Permissao::whereIn('nome', $limitedPermNames)->get();
                $permissoesExistentes = $cargo->permissoes()->pluck('permissoes.id')->toArray();
                $novasPermissoes = $permissoes->pluck('id')->toArray();
                $cargo->permissoes()->sync(array_unique(array_merge($permissoesExistentes, $novasPermissoes)));
                $this->command->info("Permissões limitadas concedidas ao cargo: {$nomeCargo}");
            }
        }

        $this->command->info('Permissões dos cargos atualizadas com sucesso!');
    }
}
