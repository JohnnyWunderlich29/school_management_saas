<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Cargo;
use Illuminate\Support\Facades\Hash;

class CreateTestAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-test-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um usuário admin de teste para debug';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = 'admin@test.com';
        $password = 'password123';
        
        // Verificar se já existe
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            $this->info('Usuário admin de teste já existe!');
            $this->info('Email: ' . $email);
            $this->info('Senha: ' . $password);
            return;
        }
        
        // Criar usuário
        $user = User::create([
            'name' => 'Admin Teste',
            'email' => $email,
            'password' => Hash::make($password),
            'ativo' => true,
            'email_verified_at' => now(),
        ]);
        
        // Buscar cargo de Super Administrador
        $superAdminCargo = Cargo::where('nome', 'Super Administrador')->first();
        if ($superAdminCargo) {
            $user->cargos()->attach($superAdminCargo->id);
            $this->info('Cargo Super Administrador atribuído!');
        }
        
        $this->info('Usuário admin de teste criado com sucesso!');
        $this->info('Email: ' . $email);
        $this->info('Senha: ' . $password);
    }
}
