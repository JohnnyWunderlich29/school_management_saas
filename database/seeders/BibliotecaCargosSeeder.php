<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cargo;
use App\Models\Permissao;

class BibliotecaCargosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando cargos para acesso à Biblioteca...');

        // Cargo: Bibliotecário
        $bibliotecario = Cargo::firstOrCreate(
            ['nome' => 'Bibliotecário'],
            [
                'nome' => 'Bibliotecário',
                'descricao' => 'Responsável pela gestão da biblioteca',
                'ativo' => true,
            ]
        );

        $permissoesBibliotecario = Permissao::whereIn('nome', [
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
        $bibliotecario->permissoes()->sync($permissoesBibliotecario);

        // Cargo: Auxiliar de Biblioteca
        $auxiliar = Cargo::firstOrCreate(
            ['nome' => 'Auxiliar de Biblioteca'],
            [
                'nome' => 'Auxiliar de Biblioteca',
                'descricao' => 'Apoio operacional da biblioteca',
                'ativo' => true,
            ]
        );

        $permissoesAuxiliar = Permissao::whereIn('nome', [
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
        $auxiliar->permissoes()->sync($permissoesAuxiliar);

        $this->command->info('Cargos da biblioteca criados e configurados.');
    }
}