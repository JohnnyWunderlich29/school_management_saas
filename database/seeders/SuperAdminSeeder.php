<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Escola;
use App\Models\User;
use App\Models\Cargo;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar ou buscar escola principal
        $escola = Escola::firstOrCreate(
            ['codigo' => 'PRINCIPAL'],
            [
                'nome' => 'Escola Principal',
                'codigo' => 'PRINCIPAL',
            ]
        );

        // Criar ou encontrar o cargo Super Administrador
        $cargoSuperAdmin = Cargo::firstOrCreate(
            ['nome' => 'Super Administrador'],
            [
                'descricao' => 'Super Administrador do sistema',
                'ativo' => true
            ]
        );

        // Criar usuário super administrador
        $user = User::firstOrCreate(
            ['email' => 'johnny@superadmin.com.br'],
            [
                'name' => 'Johnny Super Admin',
                'password' => Hash::make('12345'),
                'escola_id' => null, // Super admin não está vinculado a uma escola específica
                'ativo' => true
            ]
        );

        // Associar o cargo ao usuário
        if (!$user->cargos()->where('cargo_id', $cargoSuperAdmin->id)->exists()) {
            $user->cargos()->attach($cargoSuperAdmin->id);
        }

        $this->command->info('Super administrador criado com sucesso!');
        $this->command->info('Email: johnny@superadmin.com.br');
        $this->command->info('Senha: 12345');
    }
}
