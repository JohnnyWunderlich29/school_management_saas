<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Aluno;

class AlunoEscola5Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('pt_BR');

        $registros = [];
        $now = now();

        for ($i = 0; $i < 500; $i++) {
            $nome = $faker->firstName;
            $sobrenome = $faker->lastName;
            $matricula = 'ES5-' . strtoupper(substr(md5(uniqid((string) $i, true)), 0, 10));

            $registros[] = [
                'escola_id' => 5,
                'nome' => $nome,
                'sobrenome' => $sobrenome,
                'matricula' => $matricula,
                'data_nascimento' => $faker->dateTimeBetween('-18 years', '-5 years')->format('Y-m-d'),
                'cpf' => null, // manter nulo para não conflitar com unique
                'rg' => null,
                'endereco' => $faker->streetAddress,
                'cidade' => $faker->city,
                'estado' => $faker->stateAbbr,
                'cep' => preg_replace('/\D/', '', $faker->postcode),
                'telefone' => $faker->phoneNumber,
                'email' => null,
                'genero' => $faker->randomElement(['M', 'F']),
                'tipo_sanguineo' => $faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
                'alergias' => null,
                'medicamentos' => null,
                'observacoes' => null,
                'ativo' => true,
                'sala_id' => null,
                'turma_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Inserção em lote para performance
        foreach (array_chunk($registros, 100) as $lote) {
            Aluno::insert($lote);
        }
    }
}