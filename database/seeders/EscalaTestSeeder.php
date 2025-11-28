<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Funcionario;
use App\Models\FuncionarioTemplate;
use App\Models\Escala;
use Carbon\Carbon;

class EscalaTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Criando dados de teste para escalas...');

        // Criar usuários e funcionários de teste
        $funcionarios = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $email = "funcionario_escala{$i}@teste.com";
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "Funcionário Escala {$i}",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            $funcionario = Funcionario::firstOrCreate(
                ['email' => $email],
                [
                    'user_id' => $user->id,
                    'nome' => "Funcionário",
                    'sobrenome' => "Escala {$i}",
                    'cpf' => str_pad($i + 1000, 11, '0', STR_PAD_LEFT),
                    'data_nascimento' => Carbon::now()->subYears(25 + $i)->format('Y-m-d'),
                    'telefone' => "(11) 9999-100{$i}",
                    'endereco' => "Rua Teste, {$i}00",
                    'cidade' => 'São Paulo',
                    'estado' => 'SP',
                    'cep' => "0100{$i}-000",
                    'cargo' => $i <= 2 ? 'Professor' : 'Coordenador',
                    'data_contratacao' => Carbon::now()->subMonths($i)->format('Y-m-d'),
                    'salario' => 3000 + ($i * 500),
                    'ativo' => true,
                ]
            );

            $funcionarios[] = $funcionario;
        }

        // Criar templates variados
        $templates = [
            [
                'nome_template' => 'Manhã Integral',
                'segunda_inicio' => '07:00:00',
                'segunda_fim' => '12:00:00',
                'segunda_tipo' => 'Normal',
                'terca_inicio' => '07:00:00',
                'terca_fim' => '12:00:00',
                'terca_tipo' => 'Normal',
                'quarta_inicio' => '07:00:00',
                'quarta_fim' => '12:00:00',
                'quarta_tipo' => 'Normal',
                'quinta_inicio' => '07:00:00',
                'quinta_fim' => '12:00:00',
                'quinta_tipo' => 'Normal',
                'sexta_inicio' => '07:00:00',
                'sexta_fim' => '12:00:00',
                'sexta_tipo' => 'Normal',
            ],
            [
                'nome_template' => 'Tarde Integral',
                'segunda_inicio' => '13:00:00',
                'segunda_fim' => '18:00:00',
                'segunda_tipo' => 'Normal',
                'terca_inicio' => '13:00:00',
                'terca_fim' => '18:00:00',
                'terca_tipo' => 'Normal',
                'quarta_inicio' => '13:00:00',
                'quarta_fim' => '18:00:00',
                'quarta_tipo' => 'Normal',
                'quinta_inicio' => '13:00:00',
                'quinta_fim' => '18:00:00',
                'quinta_tipo' => 'Normal',
                'sexta_inicio' => '13:00:00',
                'sexta_fim' => '18:00:00',
                'sexta_tipo' => 'Normal',
            ],
            [
                'nome_template' => 'Período Parcial',
                'segunda_inicio' => '08:00:00',
                'segunda_fim' => '12:00:00',
                'segunda_tipo' => 'Normal',
                'quarta_inicio' => '08:00:00',
                'quarta_fim' => '12:00:00',
                'quarta_tipo' => 'Normal',
                'sexta_inicio' => '08:00:00',
                'sexta_fim' => '12:00:00',
                'sexta_tipo' => 'Normal',
            ],
        ];

        foreach ($funcionarios as $index => $funcionario) {
            $templateData = $templates[$index % count($templates)];
            $templateData['funcionario_id'] = $funcionario->id;
            $templateData['ativo'] = true;
            
            FuncionarioTemplate::create($templateData);
        }

        // Gerar escalas para o próximo mês
        $dataInicio = Carbon::now()->startOfMonth()->addMonth();
        $dataFim = Carbon::now()->startOfMonth()->addMonth()->endOfMonth();
        
        $templates = FuncionarioTemplate::where('ativo', true)->get();
        
        foreach ($templates as $template) {
            $escalasGeradas = $template->gerarEscalas($dataInicio, $dataFim);
            
            foreach ($escalasGeradas as $escalaData) {
                Escala::create($escalaData);
            }
        }

        $this->command->info('Dados de teste criados com sucesso!');
        $this->command->info('Funcionários: ' . count($funcionarios));
        $this->command->info('Templates: ' . count($templates));
        $this->command->info('Escalas geradas: ' . Escala::count());
    }
}