<?php

namespace Database\Seeders;

use App\Models\Aluno;
use App\Models\Sala;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AlunoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        
        // Buscar salas para distribuir alunos
        $salas = Sala::all();
        
        // Criar 300 alunos
        for ($i = 1; $i <= 300; $i++) {
            // Distribuir alunos nas salas respeitando capacidade
            $salaId = null;
            if ($salas->isNotEmpty()) {
                // Tentar encontrar uma sala com capacidade disponível
                $salasDisponiveis = $salas->filter(function ($sala) {
                    $alunosNaSala = Aluno::where('sala_id', $sala->id)->count();
                    return $alunosNaSala < $sala->capacidade;
                });
                
                if ($salasDisponiveis->isNotEmpty()) {
                    $salaId = $salasDisponiveis->random()->id;
                }
            }
            
            // Gerar idade apropriada baseada no tipo de sala
            $idadeMin = 6; // meses
            $idadeMax = 72; // meses (6 anos)
            
            if ($salaId) {
                $sala = $salas->find($salaId);
                if ($sala) {
                    // Ajustar idade baseada no tipo de sala
                    if (str_contains($sala->codigo, 'BER')) {
                        $idadeMin = 6; $idadeMax = 12; // 6 meses a 1 ano
                    } elseif (str_contains($sala->codigo, 'MAT')) {
                        $idadeMin = 12; $idadeMax = 24; // 1 a 2 anos
                    } elseif (str_contains($sala->codigo, 'INF1')) {
                        $idadeMin = 24; $idadeMax = 36; // 2 a 3 anos
                    } elseif (str_contains($sala->codigo, 'INF2')) {
                        $idadeMin = 36; $idadeMax = 48; // 3 a 4 anos
                    } elseif (str_contains($sala->codigo, 'PRE')) {
                        $idadeMin = 48; $idadeMax = 60; // 4 a 5 anos
                    } elseif (str_contains($sala->codigo, 'JAR')) {
                        $idadeMin = 60; $idadeMax = 72; // 5 a 6 anos
                    }
                }
            }
            
            $dataNascimento = $faker->dateTimeBetween("-{$idadeMax} months", "-{$idadeMin} months");
            
            Aluno::create([
                'nome' => $faker->firstName,
                'sobrenome' => $faker->lastName,
                'data_nascimento' => $dataNascimento->format('Y-m-d'),
                'cpf' => $faker->cpf(false),
                'rg' => $faker->rg(false),
                'endereco' => $faker->streetAddress,
                'cidade' => $faker->city,
                'estado' => $faker->stateAbbr,
                'cep' => $faker->postcode,
                'telefone' => $faker->cellphone(false),
                'email' => $faker->unique()->safeEmail,
                'genero' => $faker->randomElement(['M', 'F']),
                'tipo_sanguineo' => $faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
                'alergias' => $faker->optional(0.3)->sentence(3),
                'medicamentos' => $faker->optional(0.2)->sentence(2),
                'observacoes' => $faker->optional(0.4)->paragraph(1),
                'sala_id' => $salaId,
                'ativo' => $faker->boolean(98), // 98% ativos
            ]);
        }
        
        $this->command->info('300 alunos criados com sucesso!');
    }
}