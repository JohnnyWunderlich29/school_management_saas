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

        // Em produção, criar menos alunos para evitar exaustão de memória (128MB Railway)
        $totalAlunos = config('app.env') === 'production' ? 50 : 300;

        $this->command->info("Iniciando a criação de {$totalAlunos} alunos...");

        // Pré-carregar contagem de alunos por sala para otimizar
        $contagemPorSala = Aluno::select('sala_id', \DB::raw('count(*) as total'))
            ->whereNotNull('sala_id')
            ->groupBy('sala_id')
            ->pluck('total', 'sala_id')
            ->toArray();

        for ($i = 1; $i <= $totalAlunos; $i++) {
            // Distribuir alunos nas salas respeitando capacidade
            $salaId = null;
            if ($salas->isNotEmpty()) {
                // Tentar encontrar uma sala com capacidade disponível
                $salasDisponiveis = $salas->filter(function ($sala) use ($contagemPorSala) {
                    $atual = $contagemPorSala[$sala->id] ?? 0;
                    return $atual < $sala->capacidade;
                });

                if ($salasDisponiveis->isNotEmpty()) {
                    $salaEscolhida = $salasDisponiveis->random();
                    $salaId = $salaEscolhida->id;
                    $contagemPorSala[$salaId] = ($contagemPorSala[$salaId] ?? 0) + 1;
                }
            }

            // Gerar idade apropriada baseada no tipo de sala
            $idadeMin = 6;
            $idadeMax = 72;

            if ($salaId) {
                $sala = $salas->find($salaId);
                if ($sala) {
                    if (str_contains($sala->codigo, 'BER')) {
                        $idadeMin = 6;
                        $idadeMax = 12;
                    } elseif (str_contains($sala->codigo, 'MAT')) {
                        $idadeMin = 12;
                        $idadeMax = 24;
                    } elseif (str_contains($sala->codigo, 'INF1')) {
                        $idadeMin = 24;
                        $idadeMax = 36;
                    } elseif (str_contains($sala->codigo, 'INF2')) {
                        $idadeMin = 36;
                        $idadeMax = 48;
                    } elseif (str_contains($sala->codigo, 'PRE')) {
                        $idadeMin = 48;
                        $idadeMax = 60;
                    } elseif (str_contains($sala->codigo, 'JAR')) {
                        $idadeMin = 60;
                        $idadeMax = 72;
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
                'ativo' => $faker->boolean(98),
            ]);

            if ($i % 10 === 0) {
                $this->command->comment("Criados {$i} alunos...");
            }
        }

        $this->command->info("{$totalAlunos} alunos criados com sucesso!");
    }
}