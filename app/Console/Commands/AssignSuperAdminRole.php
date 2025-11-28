<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cargo;

class AssignSuperAdminRole extends Command
{
    protected $signature = 'app:assign-super-admin-role {email}';
    protected $description = 'Associa o cargo Super Administrador a um usuário';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error('Usuário não encontrado: ' . $email);
            return Command::FAILURE;
        }

        $cargoSuperAdmin = Cargo::where('nome', 'Super Administrador')->first();
        if (!$cargoSuperAdmin) {
            $this->error('Cargo Super Administrador não encontrado!');
            return Command::FAILURE;
        }

        // Verificar se já possui o cargo
        if ($user->cargos()->where('cargo_id', $cargoSuperAdmin->id)->exists()) {
            $this->info('Usuário já possui o cargo Super Administrador!');
            return Command::SUCCESS;
        }

        // Associar o cargo
        $user->cargos()->attach($cargoSuperAdmin->id);
        
        $this->info('Cargo Super Administrador associado com sucesso ao usuário: ' . $email);
        $this->info('Verificando: isSuperAdmin() = ' . ($user->fresh()->isSuperAdmin() ? 'true' : 'false'));
        
        return Command::SUCCESS;
    }
}