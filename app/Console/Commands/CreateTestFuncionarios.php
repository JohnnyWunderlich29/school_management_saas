<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Funcionario;
use Faker\Factory as Faker;

class CreateTestFuncionarios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-funcionarios {--count=3}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria funcionários de teste sem usuário associado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $faker = Faker::create('pt_BR');
        $count = $this->option('count');
        
        $cargos = [
            'Professor',
            'Coordenador',
            'Secretário',
            'Auxiliar de Ensino',
            'Monitor'
        ];
        
        $departamentos = [
            'Educação',
            'Administração',
            'Coordenação'
        ];
        
        $this->info("Criando {$count} funcionários de teste sem usuário...");
        
        for ($i = 1; $i <= $count; $i++) {
            $nome = $faker->firstName;
            $sobrenome = $faker->lastName;
            $email = strtolower($nome . '.' . $sobrenome . '.teste@escola.com');
            
            // Verificar se já existe funcionário com este email
            if (Funcionario::where('email', $email)->exists()) {
                $email = strtolower($nome . '.' . $sobrenome . '.' . $i . '.teste@escola.com');
            }
            
            $funcionario = Funcionario::create([
                'user_id' => null, // Sem usuário associado
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'data_nascimento' => $faker->dateTimeBetween('-50 years', '-25 years')->format('Y-m-d'),
                'cpf' => $faker->cpf(false),
                'rg' => $faker->rg(false),
                'telefone' => $faker->cellphone(false),
                'email' => $email,
                'endereco' => $faker->streetAddress,
                'cidade' => $faker->city,
                'estado' => $faker->stateAbbr,
                'cep' => $faker->postcode,
                'cargo' => $faker->randomElement($cargos),
                'departamento' => $faker->randomElement($departamentos),
                'data_contratacao' => $faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'salario' => $faker->randomFloat(2, 3000, 7000),
                'ativo' => true,
                'observacoes' => 'Funcionário de teste para criação de usuários'
            ]);
            
            $this->line("✓ Criado: {$funcionario->nome_completo} ({$funcionario->cargo})");
        }
        
        $this->info("\n{$count} funcionários de teste criados com sucesso!");
        $this->info('Agora você pode acessar /usuarios/create para criar usuários para estes funcionários.');
        
        return 0;
    }
}
