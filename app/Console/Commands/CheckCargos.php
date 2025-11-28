<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cargo;

class CheckCargos extends Command
{
    protected $signature = 'app:check-cargos';
    protected $description = 'Verifica cargos disponíveis e cargos do usuário';

    public function handle()
    {
        $this->info('=== CARGOS DISPONÍVEIS ===');
        $cargos = Cargo::all();
        foreach ($cargos as $cargo) {
            $this->line($cargo->id . ' - ' . $cargo->nome . ' (Ativo: ' . ($cargo->ativo ? 'Sim' : 'Não') . ')');
        }

        $this->info('\n=== VERIFICANDO USUÁRIO johnny@superadmin.com.br ===');
        $user = User::where('email', 'johnny@superadmin.com.br')->first();
        
        if ($user) {
            $this->line('Usuário encontrado: ' . $user->name);
            $this->line('Cargos do usuário:');
            
            if ($user->cargos->count() > 0) {
                foreach ($user->cargos as $cargo) {
                    $this->line('  - ' . $cargo->id . ' - ' . $cargo->nome);
                }
            } else {
                $this->error('  Usuário não possui nenhum cargo!');
            }
            
            $this->line('\nisSuperAdmin(): ' . ($user->isSuperAdmin() ? 'true' : 'false'));
        } else {
            $this->error('Usuário não encontrado!');
        }

        return Command::SUCCESS;
    }
}