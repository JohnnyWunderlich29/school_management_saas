<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FuncionarioTemplate;
use App\Models\Funcionario;
use App\Models\Escola;

class FuncionarioTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar escola e funcionários existentes
        $escola = Escola::first();
        
        if (!$escola) {
            $this->command->warn('Nenhuma escola encontrada. Execute o EscolaSeeder primeiro.');
            return;
        }
        
        $funcionarios = Funcionario::where('escola_id', $escola->id)->take(3)->get();
        
        if ($funcionarios->isEmpty()) {
            // Criar funcionários de teste se não existirem
            $funcionarios = collect();
            
            for ($i = 1; $i <= 3; $i++) {
                // Criar usuário primeiro
                $user = \App\Models\User::create([
                    'name' => "Funcionário Teste {$i}",
                    'email' => "funcionario{$i}@teste.com",
                    'password' => bcrypt('password'),
                ]);
                
                $funcionario = Funcionario::create([
                    'user_id' => $user->id,
                    'escola_id' => $escola->id,
                    'nome' => "Funcionário",
                    'sobrenome' => "Teste {$i}",
                    'email' => "funcionario{$i}@teste.com",
                    'cpf' => str_pad($i, 11, '0', STR_PAD_LEFT),
                    'telefone' => "(11) 9999-000{$i}",
                    'data_nascimento' => now()->subYears(25 + $i),
                    'endereco' => "Rua Teste, {$i}00",
                    'cidade' => 'São Paulo',
                    'estado' => 'SP',
                    'cep' => '01000-00' . $i,
                    'data_contratacao' => now()->subMonths($i),
                    'cargo' => 'Professor',
                    'salario' => 3000.00 + ($i * 500),
                    'ativo' => true,
                ]);
                
                $funcionarios->push($funcionario);
            }
            
            $this->command->info('Funcionários de teste criados.');
        }
        
        // Criar templates para cada funcionário
        foreach ($funcionarios as $index => $funcionario) {
            $templates = [
                [
                    'funcionario_id' => $funcionario->id,
                    'nome_template' => 'Template Manhã',
                    'ativo' => $index === 0, // Apenas o primeiro ativo
                    'segunda_inicio' => '08:00',
                    'segunda_fim' => '12:00',
                    'segunda_tipo' => 'Normal',
                    'terca_inicio' => '08:00',
                    'terca_fim' => '12:00',
                    'terca_tipo' => 'Normal',
                    'quarta_inicio' => '08:00',
                    'quarta_fim' => '12:00',
                    'quarta_tipo' => 'Normal',
                    'quinta_inicio' => '08:00',
                    'quinta_fim' => '12:00',
                    'quinta_tipo' => 'Normal',
                    'sexta_inicio' => '08:00',
                    'sexta_fim' => '12:00',
                    'sexta_tipo' => 'Normal',
                ],
                [
                    'funcionario_id' => $funcionario->id,
                    'nome_template' => 'Template Tarde',
                    'ativo' => false,
                    'segunda_inicio' => '13:00',
                    'segunda_fim' => '17:00',
                    'segunda_tipo' => 'Normal',
                    'terca_inicio' => '13:00',
                    'terca_fim' => '17:00',
                    'terca_tipo' => 'Normal',
                    'quarta_inicio' => '13:00',
                    'quarta_fim' => '17:00',
                    'quarta_tipo' => 'Normal',
                    'quinta_inicio' => '13:00',
                    'quinta_fim' => '17:00',
                    'quinta_tipo' => 'Normal',
                    'sexta_inicio' => '13:00',
                    'sexta_fim' => '17:00',
                    'sexta_tipo' => 'Normal',
                ],
                [
                    'funcionario_id' => $funcionario->id,
                    'nome_template' => 'Template Integral',
                    'ativo' => false,
                    'segunda_inicio' => '08:00',
                    'segunda_fim' => '17:00',
                    'segunda_tipo' => 'Normal',
                    'terca_inicio' => '08:00',
                    'terca_fim' => '17:00',
                    'terca_tipo' => 'Normal',
                    'quarta_inicio' => '08:00',
                    'quarta_fim' => '17:00',
                    'quarta_tipo' => 'Normal',
                    'quinta_inicio' => '08:00',
                    'quinta_fim' => '17:00',
                    'quinta_tipo' => 'Normal',
                    'sexta_inicio' => '08:00',
                    'sexta_fim' => '17:00',
                    'sexta_tipo' => 'Normal',
                ],
            ];
            
            foreach ($templates as $templateData) {
                FuncionarioTemplate::create($templateData);
            }
        }
        

        
        $this->command->info('Templates de funcionários criados com sucesso!');
    }
}
