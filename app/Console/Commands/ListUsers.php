<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    protected $signature = 'user:list';
    protected $description = 'Lista todos os usuários do sistema';

    public function handle()
    {
        $users = User::select('id', 'name', 'email', 'ativo')->get();
        
        if ($users->isEmpty()) {
            $this->info('Nenhum usuário encontrado.');
            return;
        }
        
        $this->info('Usuários encontrados:');
        $this->table(
            ['ID', 'Nome', 'Email', 'Ativo'],
            $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->ativo ? 'Sim' : 'Não'
                ];
            })->toArray()
        );
        
        return 0;
    }
}