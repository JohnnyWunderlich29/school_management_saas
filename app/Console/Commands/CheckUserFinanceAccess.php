<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Escola;
use App\Services\LicenseService;

class CheckUserFinanceAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:finance-check {userId : ID do usuário}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica se o usuário tem acesso ao módulo Financeiro, mostrando escola e licenças relacionadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = (int) $this->argument('userId');

        $user = User::find($userId);
        if (!$user) {
            $this->error("Usuário com ID {$userId} não encontrado.");
            return 1;
        }

        $this->info("Usuário: {$user->name} (ID: {$user->id})");
        $this->line("Email: {$user->email}");
        $this->line("Escola ID: " . ($user->escola_id ?? 'N/A'));

        if (!$user->escola_id) {
            $this->warn('Usuário não possui escola vinculada. O Financeiro não aparecerá.');
            return 0;
        }

        $escola = Escola::find($user->escola_id);
        if (!$escola) {
            $this->error('Escola vinculada não encontrada.');
            return 1;
        }

        $this->line("Escola: {$escola->nome} (ID: {$escola->id})");

        $licenseService = app(LicenseService::class);

        $hasFinance = $licenseService->hasModuleLicense('financeiro_module', $escola);
        $status = $hasFinance ? '✅ Licenciado' : '❌ Sem licença';
        $this->info("Financeiro: {$status}");

        // Mostrar permissões-chave para visualização do menu Financeiro
        $permissions = ['despesas.ver', 'recebimentos.ver', 'recorrencias.ver'];
        $this->line('Permissões relevantes para visualizar o menu Financeiro:');
        foreach ($permissions as $perm) {
            $hasPerm = method_exists($user, 'temPermissao') ? $user->temPermissao($perm) : false;
            $this->line(" - {$perm}: " . ($hasPerm ? '✅' : '❌'));
        }

        return 0;
    }
}