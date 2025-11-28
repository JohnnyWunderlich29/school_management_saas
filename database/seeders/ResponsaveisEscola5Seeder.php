<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Aluno;
use App\Models\Responsavel;

class ResponsaveisEscola5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $escolaId = 5;
        $faker = Faker::create('pt_BR');

        // Seleciona alunos da escola 5 que vamos garantir que possuem responsável
        $alunos = Aluno::where('escola_id', $escolaId)
            ->inRandomOrder()
            ->get();

        if ($alunos->isEmpty()) {
            $this->command?->warn('Nenhum aluno encontrado para escola_id=5.');
            return;
        }

        DB::transaction(function () use ($alunos, $faker, $escolaId) {
            foreach ($alunos as $aluno) {
                // Cria um responsável principal por aluno (se não tiver)
                $jaTemPrincipal = $aluno->responsaveis()
                    ->wherePivot('responsavel_principal', true)
                    ->exists();

                if (!$jaTemPrincipal) {
                    $responsavelPrincipal = Responsavel::create([
                        'escola_id' => $escolaId,
                        'nome' => $faker->firstName(),
                        'sobrenome' => $faker->lastName(),
                        'data_nascimento' => $faker->dateTimeBetween('-60 years', '-25 years'),
                        'genero' => $faker->randomElement(['M', 'F']),
                        'cpf' => $faker->unique()->numerify('###########'),
                        'rg' => $faker->numerify('########'),
                        'telefone_principal' => $faker->numerify('(##) 9####-####'),
                        'telefone_secundario' => $faker->numerify('(##) ####-####'),
                        'email' => $faker->safeEmail(),
                        'endereco' => $faker->streetAddress(),
                        'cidade' => $faker->city(),
                        'estado' => $faker->stateAbbr(),
                        'cep' => $faker->numerify('#####-###'),
                        'parentesco' => $faker->randomElement(['Pai', 'Mãe', 'Tio', 'Tia', 'Avô', 'Avó', 'Responsável Legal']),
                        'autorizado_buscar' => true,
                        'contato_emergencia' => true,
                        'observacoes' => null,
                        'ativo' => true,
                    ]);

                    $aluno->responsaveis()->attach($responsavelPrincipal->id, [
                        'responsavel_principal' => true,
                    ]);
                }

                // ~30% dos alunos terão um segundo responsável
                if (mt_rand(1, 100) <= 30) {
                    $responsavelSecundario = Responsavel::create([
                        'escola_id' => $escolaId,
                        'nome' => $faker->firstName(),
                        'sobrenome' => $faker->lastName(),
                        'data_nascimento' => $faker->dateTimeBetween('-60 years', '-25 years'),
                        'genero' => $faker->randomElement(['M', 'F']),
                        'cpf' => $faker->unique()->numerify('###########'),
                        'rg' => $faker->numerify('########'),
                        'telefone_principal' => $faker->numerify('(##) 9####-####'),
                        'telefone_secundario' => $faker->numerify('(##) ####-####'),
                        'email' => $faker->safeEmail(),
                        'endereco' => $faker->streetAddress(),
                        'cidade' => $faker->city(),
                        'estado' => $faker->stateAbbr(),
                        'cep' => $faker->numerify('#####-###'),
                        'parentesco' => $faker->randomElement(['Pai', 'Mãe', 'Tio', 'Tia', 'Avô', 'Avó', 'Responsável Legal']),
                        'autorizado_buscar' => true,
                        'contato_emergencia' => true,
                        'observacoes' => null,
                        'ativo' => true,
                    ]);

                    $aluno->responsaveis()->attach($responsavelSecundario->id, [
                        'responsavel_principal' => false,
                    ]);
                }
            }
        });

        $this->command?->info('Responsáveis gerados e vinculados aos alunos da escola 5.');
    }
}