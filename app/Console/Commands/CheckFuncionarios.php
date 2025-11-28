<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funcionario;

class CheckFuncionarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:funcionarios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica funcionários sem usuário';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalFuncionarios = Funcionario::where('ativo', true)->count();
        $funcionariosComUsuario = Funcionario::where('ativo', true)->whereHas('user')->count();
        $funcionariosSemUsuario = Funcionario::where('ativo', true)
            ->whereDoesntHave('user')
            ->orderBy('nome')
            ->get();
            
        $this->info('Total de funcionários ativos: ' . $totalFuncionarios);
        $this->info('Funcionários ativos com usuário: ' . $funcionariosComUsuario);
        $this->info('Funcionários ativos sem usuário: ' . $funcionariosSemUsuario->count());
        
        if ($funcionariosSemUsuario->count() > 0) {
            $this->info('\nFuncionários sem usuário:');
            foreach ($funcionariosSemUsuario as $funcionario) {
                $this->line('- ' . $funcionario->nome_completo . ' (' . $funcionario->cargo . ')');
            }
        } else {
            $this->warn('\nTodos os funcionários ativos já possuem usuário associado!');
            $this->info('Para criar novos usuários, você precisa:');
            $this->info('1. Criar novos funcionários sem marcar "Criar usuário"');
            $this->info('2. Ou desassociar usuários de funcionários existentes');
        }
        
        return 0;
    }
}
