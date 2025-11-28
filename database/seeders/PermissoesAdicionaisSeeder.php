<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;

class PermissoesAdicionaisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Permissões que estão sendo usadas nas rotas mas não existem no seeder atual
        $permissoesAdicionais = [
            // Dashboard
            ['nome' => 'dashboard.visualizar', 'descricao' => 'Visualizar dashboard', 'modulo' => 'Dashboard'],
            
            // Relatórios (mencionados no PERMISSIONS.md mas não implementados)
            ['nome' => 'relatorios.gerar', 'descricao' => 'Gerar relatórios', 'modulo' => 'Relatórios'],
            ['nome' => 'relatorios.exportar', 'descricao' => 'Exportar relatórios', 'modulo' => 'Relatórios'],
            ['nome' => 'relatorios.visualizar', 'descricao' => 'Visualizar relatórios', 'modulo' => 'Relatórios'],
            
            // Configurações (mencionadas no PERMISSIONS.md mas não implementadas)
            ['nome' => 'configuracoes.visualizar', 'descricao' => 'Visualizar configurações', 'modulo' => 'Configurações'],
            ['nome' => 'configuracoes.editar', 'descricao' => 'Editar configurações do sistema', 'modulo' => 'Configurações'],
            
            // Histórico (usado nas rotas mas não tem permissões específicas)
            ['nome' => 'historico.visualizar', 'descricao' => 'Visualizar histórico de alterações', 'modulo' => 'Histórico'],
            
            // Perfil de usuário
            ['nome' => 'perfil.visualizar', 'descricao' => 'Visualizar próprio perfil', 'modulo' => 'Perfil'],
            ['nome' => 'perfil.editar', 'descricao' => 'Editar próprio perfil', 'modulo' => 'Perfil'],
            
            // Transferências de alunos
            ['nome' => 'transferencias.criar', 'descricao' => 'Solicitar transferência de alunos', 'modulo' => 'Transferências'],
            ['nome' => 'transferencias.aprovar', 'descricao' => 'Aprovar transferências de alunos', 'modulo' => 'Transferências'],
            ['nome' => 'transferencias.visualizar', 'descricao' => 'Visualizar transferências', 'modulo' => 'Transferências'],
        ];

        foreach ($permissoesAdicionais as $permissaoData) {
            Permissao::firstOrCreate(
                ['nome' => $permissaoData['nome']],
                $permissaoData
            );
        }

        // Atualizar permissões dos cargos existentes
        $this->atualizarPermissoesCargos();
    }

    private function atualizarPermissoesCargos(): void
    {
        // Cargo Administrador - adicionar novas permissões
        $cargoAdmin = Cargo::where('nome', 'Administrador')->first();
        if ($cargoAdmin) {
            $novasPermissoesAdmin = Permissao::whereIn('nome', [
                'dashboard.visualizar',
                'relatorios.gerar',
                'relatorios.exportar',
                'relatorios.visualizar',
                'configuracoes.visualizar',
                'configuracoes.editar',
                'historico.visualizar',
                'perfil.visualizar',
                'perfil.editar',
                'transferencias.criar',
                'transferencias.aprovar',
                'transferencias.visualizar',
                'planejamentos.aprovar',
            ])->get();
            
            // Adicionar as novas permissões sem remover as existentes
            $permissoesExistentes = $cargoAdmin->permissoes()->pluck('permissoes.id')->toArray();
            $novasPermissoesIds = $novasPermissoesAdmin->pluck('id')->toArray();
            $todasPermissoes = array_unique(array_merge($permissoesExistentes, $novasPermissoesIds));
            
            $cargoAdmin->permissoes()->sync($todasPermissoes);
        }

        // Cargo Professor - adicionar permissões básicas
        $cargoProfessor = Cargo::where('nome', 'Professor')->first();
        if ($cargoProfessor) {
            $permissoesProfessor = Permissao::whereIn('nome', [
                'dashboard.visualizar',
                'perfil.visualizar',
                'perfil.editar',
                'historico.visualizar',
            ])->get();
            
            $permissoesExistentes = $cargoProfessor->permissoes()->pluck('permissoes.id')->toArray();
            $novasPermissoesIds = $permissoesProfessor->pluck('id')->toArray();
            $todasPermissoes = array_unique(array_merge($permissoesExistentes, $novasPermissoesIds));
            
            $cargoProfessor->permissoes()->sync($todasPermissoes);
        }

        // Cargo Secretário - adicionar permissões de relatórios
        $cargoSecretario = Cargo::where('nome', 'Secretário')->first();
        if ($cargoSecretario) {
            $permissoesSecretario = Permissao::whereIn('nome', [
                'dashboard.visualizar',
                'relatorios.gerar',
                'relatorios.visualizar',
                'perfil.visualizar',
                'perfil.editar',
                'historico.visualizar',
                'transferencias.criar',
                'transferencias.visualizar',
            ])->get();
            
            $permissoesExistentes = $cargoSecretario->permissoes()->pluck('permissoes.id')->toArray();
            $novasPermissoesIds = $permissoesSecretario->pluck('id')->toArray();
            $todasPermissoes = array_unique(array_merge($permissoesExistentes, $novasPermissoesIds));
            
            $cargoSecretario->permissoes()->sync($todasPermissoes);
        }

        // Criar cargo Coordenador se não existir
        $cargoCoordenador = Cargo::firstOrCreate(
            ['nome' => 'Coordenador'],
            [
                'nome' => 'Coordenador',
                'descricao' => 'Coordenador pedagógico com acesso amplo ao sistema',
                'ativo' => true
            ]
        );

        // Permissões para Coordenador
        $permissoesCoordenador = Permissao::whereIn('nome', [
            'dashboard.visualizar',
            'alunos.listar',
            'alunos.criar',
            'alunos.editar',
            'alunos.visualizar',
            'responsaveis.listar',
            'responsaveis.criar',
            'responsaveis.editar',
            'responsaveis.visualizar',
            'funcionarios.listar',
            'funcionarios.visualizar',
            'escalas.listar',
            'escalas.criar',
            'escalas.editar',
            'escalas.visualizar',
            'presencas.listar',
            'presencas.criar',
            'presencas.editar',
            'presencas.visualizar',
            'planejamentos.listar',
            'planejamentos.visualizar',
            'planejamentos.aprovar',
            'relatorios.gerar',
            'relatorios.visualizar',
            'historico.visualizar',
            'perfil.visualizar',
            'perfil.editar',
            'transferencias.criar',
            'transferencias.aprovar',
            'transferencias.visualizar',
        ])->get();
        
        $cargoCoordenador->permissoes()->sync($permissoesCoordenador->pluck('id'));
    }
}