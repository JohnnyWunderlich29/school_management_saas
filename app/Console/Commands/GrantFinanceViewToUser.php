<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Permissao;

class GrantFinanceViewToUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:grant-finance-view {userId : ID do usuário} {--perm=* : Permissões específicas (padrão: recebimentos.ver, recorrencias.ver)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um cargo específico para o usuário e atribui permissões de visualização do Financeiro sem afetar outros cargos.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('userId');
        $permsOption = (array) $this->option('perm');

        $defaultPerms = ['recebimentos.ver', 'recorrencias.ver'];
        $permsToGrant = !empty($permsOption) ? $permsOption : $defaultPerms;

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado.");
            return 1;
        }

        if (!$user->escola_id) {
            $this->error('Usuário não possui escola vinculada. Não é possível criar cargo específico.');
            return 1;
        }

        $cargoName = 'Professor Financeiro';
        $cargo = Cargo::firstOrCreate(
            ['nome' => $cargoName, 'escola_id' => $user->escola_id],
            [
                'nome' => $cargoName,
                'descricao' => 'Cargo específico para visualização do módulo Financeiro',
                'escola_id' => $user->escola_id,
                'ativo' => true,
            ]
        );

        $permissions = Permissao::whereIn('nome', $permsToGrant)->get();
        if ($permissions->isEmpty()) {
            $this->error('Nenhuma das permissões informadas foi encontrada.');
            return 1;
        }

        $cargo->permissoes()->syncWithoutDetaching($permissions->pluck('id'));
        $user->cargos()->syncWithoutDetaching([$cargo->id]);

        $this->info("Permissões (" . implode(', ', $permissions->pluck('nome')->toArray()) . ") atribuídas ao usuário {$user->name} via cargo '{$cargoName}'.");
        return 0;
    }
}