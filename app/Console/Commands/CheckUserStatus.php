<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckUserStatus extends Command
{
    protected $signature = 'user:check {email}';
    protected $description = 'Verifica o status de um usuário';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error('Usuário não encontrado: ' . $email);
            return Command::FAILURE;
        }
        
        $this->info('=== STATUS DO USUÁRIO ===');
        $this->line('Nome: ' . $user->name);
        $this->line('Email: ' . $user->email);
        $this->line('Ativo: ' . ($user->ativo ? 'Sim' : 'Não'));
        $this->line('isSuperAdmin(): ' . ($user->isSuperAdmin() ? 'true' : 'false'));
        
        $cargos = $user->cargos->pluck('nome')->toArray();
        $this->line('Cargos: ' . (empty($cargos) ? 'Nenhum' : implode(', ', $cargos)));
        
        return Command::SUCCESS;
    }
}