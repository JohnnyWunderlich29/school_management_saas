<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;

class BibliotecaPermissoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando permissões para o módulo Biblioteca Digital...');

        // Permissões do módulo Biblioteca Digital
        $permissoes = [
            // Catálogo e Acervo
            ['nome' => 'biblioteca.ver', 'descricao' => 'Visualizar catálogo da biblioteca', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.criar', 'descricao' => 'Adicionar itens ao acervo', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.editar', 'descricao' => 'Editar itens do acervo', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.excluir', 'descricao' => 'Excluir itens do acervo', 'modulo' => 'Biblioteca Digital'],
            
            // Empréstimos
            ['nome' => 'biblioteca.emprestimos.ver', 'descricao' => 'Visualizar empréstimos', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.emprestimos.criar', 'descricao' => 'Realizar empréstimos', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.emprestimos.devolver', 'descricao' => 'Processar devoluções', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.emprestimos.renovar', 'descricao' => 'Renovar empréstimos', 'modulo' => 'Biblioteca Digital'],
            
            // Reservas
            ['nome' => 'biblioteca.reservas.ver', 'descricao' => 'Visualizar reservas', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.reservas.criar', 'descricao' => 'Fazer reservas', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.reservas.cancelar', 'descricao' => 'Cancelar reservas', 'modulo' => 'Biblioteca Digital'],
            
            // Arquivos Digitais
            ['nome' => 'biblioteca.digitais.ver', 'descricao' => 'Visualizar arquivos digitais', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.digitais.upload', 'descricao' => 'Fazer upload de arquivos digitais', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.digitais.download', 'descricao' => 'Fazer download de arquivos digitais', 'modulo' => 'Biblioteca Digital'],
            
            // Políticas e Configurações
            ['nome' => 'biblioteca.politicas.ver', 'descricao' => 'Visualizar políticas de acesso', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.politicas.editar', 'descricao' => 'Editar políticas de acesso', 'modulo' => 'Biblioteca Digital'],
            
            // Multas
            ['nome' => 'biblioteca.multas.ver', 'descricao' => 'Visualizar multas', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.multas.gerenciar', 'descricao' => 'Gerenciar multas e regras', 'modulo' => 'Biblioteca Digital'],
            
            // Relatórios
            ['nome' => 'biblioteca.relatorios.ver', 'descricao' => 'Visualizar relatórios da biblioteca', 'modulo' => 'Biblioteca Digital'],
            ['nome' => 'biblioteca.relatorios.exportar', 'descricao' => 'Exportar relatórios da biblioteca', 'modulo' => 'Biblioteca Digital'],
        ];

        foreach ($permissoes as $permissao) {
            Permissao::updateOrCreate(
                ['nome' => $permissao['nome']],
                $permissao
            );
        }

        $this->command->info('Permissões do módulo Biblioteca Digital criadas com sucesso!');

        // Atualizar permissões dos cargos existentes
        $this->atualizarPermissoesCargos();
    }

    /**
     * Atualizar permissões dos cargos existentes
     */
    private function atualizarPermissoesCargos(): void
    {
        $this->command->info('Atualizando permissões dos cargos existentes...');

        // Permissões para Administrador de Escola
        $cargoAdminEscola = Cargo::where('nome', 'Administrador de Escola')->first();
        if ($cargoAdminEscola) {
            $permissoesAdminEscola = Permissao::whereIn('nome', [
                'biblioteca.ver',
                'biblioteca.criar',
                'biblioteca.editar',
                'biblioteca.excluir',
                'biblioteca.emprestimos.ver',
                'biblioteca.emprestimos.criar',
                'biblioteca.emprestimos.devolver',
                'biblioteca.emprestimos.renovar',
                'biblioteca.reservas.ver',
                'biblioteca.reservas.criar',
                'biblioteca.reservas.cancelar',
                'biblioteca.digitais.ver',
                'biblioteca.digitais.upload',
                'biblioteca.digitais.download',
                'biblioteca.politicas.ver',
                'biblioteca.politicas.editar',
                'biblioteca.multas.ver',
                'biblioteca.multas.gerenciar',
                'biblioteca.relatorios.ver',
                'biblioteca.relatorios.exportar',
            ])->pluck('id');

            $cargoAdminEscola->permissoes()->syncWithoutDetaching($permissoesAdminEscola);
        }

        // Permissões para Secretário
        $cargoSecretario = Cargo::where('nome', 'Secretário')->first();
        if ($cargoSecretario) {
            $permissoesSecretario = Permissao::whereIn('nome', [
                'biblioteca.ver',
                'biblioteca.emprestimos.ver',
                'biblioteca.emprestimos.criar',
                'biblioteca.emprestimos.devolver',
                'biblioteca.emprestimos.renovar',
                'biblioteca.reservas.ver',
                'biblioteca.reservas.criar',
                'biblioteca.reservas.cancelar',
                'biblioteca.digitais.ver',
                'biblioteca.digitais.download',
                'biblioteca.multas.ver',
                'biblioteca.relatorios.ver',
            ])->pluck('id');

            $cargoSecretario->permissoes()->syncWithoutDetaching($permissoesSecretario);
        }

        // Permissões para Professor
        $cargoProfessor = Cargo::where('nome', 'Professor')->first();
        if ($cargoProfessor) {
            $permissoesProfessor = Permissao::whereIn('nome', [
                'biblioteca.ver',
                'biblioteca.reservas.ver',
                'biblioteca.reservas.criar',
                'biblioteca.digitais.ver',
                'biblioteca.digitais.download',
            ])->pluck('id');

            $cargoProfessor->permissoes()->syncWithoutDetaching($permissoesProfessor);
        }

        // Super Administrador já tem todas as permissões automaticamente

        $this->command->info('Permissões dos cargos atualizadas com sucesso!');
    }
}