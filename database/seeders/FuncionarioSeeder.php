<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Funcionario;
use App\Models\User;
use App\Models\Escola;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class FuncionarioSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $escola = Escola::first();
        
        if (!$escola) {
            return;
        }
        
        // Funcionários específicos
        $funcionarios = [
            [
                'nome' => 'Daiane Saito',
                'email' => 'daiane.saito@escola.com',
                'telefone' => '11999887766',
                'endereco' => 'Rua das Flores, 123',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01234-567'
            ],
            [
                'nome' => 'Maria Silva',
                'email' => 'maria.silva@escola.com',
                'telefone' => '11988776655',
                'endereco' => 'Av. Principal, 456',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01234-568'
            ],
            [
                'nome' => 'João Santos',
                'email' => 'joao.santos@escola.com',
                'telefone' => '11977665544',
                'endereco' => 'Rua Central, 789',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cep' => '01234-569'
            ]
        ];
        
        foreach ($funcionarios as $funcionarioData) {
            // Criar usuário
            $user = User::firstOrCreate(
                ['email' => $funcionarioData['email']],
                [
                    'name' => $funcionarioData['nome'],
                    'email' => $funcionarioData['email'],
                    'password' => Hash::make('123456'),
                ]
            );
            
            // Criar funcionário
            Funcionario::firstOrCreate(
                ['email' => $funcionarioData['email']],
                [
                    'user_id' => $user->id,
                    'nome' => $funcionarioData['nome'],
                    'email' => $funcionarioData['email'],
                    'telefone' => $funcionarioData['telefone'],
                    'endereco' => $funcionarioData['endereco'],
                    'cidade' => $funcionarioData['cidade'],
                    'estado' => $funcionarioData['estado'],
                    'cep' => $funcionarioData['cep'],
                    'escola_id' => $escola->id,
                ]
            );
        }
    }
}
