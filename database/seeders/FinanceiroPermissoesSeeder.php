<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permissao;
use App\Models\Cargo;

class FinanceiroPermissoesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Garantir que a permissão 'financeiro.admin' exista
        $perm = Permissao::firstOrCreate(
            ['nome' => 'financeiro.admin'],
            [
                'nome' => 'financeiro.admin',
                'descricao' => 'Administrar configurações financeiras',
                'modulo' => 'Financeiro',
                'ativo' => true,
            ]
        );

        // Associar a cargos relevantes se existirem
        $cargos = Cargo::whereIn('nome', ['Administrador', 'Suporte', 'Suporte Técnico'])->get();
        foreach ($cargos as $cargo) {
            $cargo->permissoes()->syncWithoutDetaching([$perm->id]);
        }

        $this->command->info("Permissão 'financeiro.admin' garantida e associada aos cargos: Administrador, Suporte, Suporte Técnico (se existentes).");
    }
}