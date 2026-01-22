<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sala;
use App\Models\ModalidadeEnsino;
use App\Models\Turno;
use App\Models\Grupo;
use App\Models\Escola;
use App\Models\UnidadeEscolar;
use Faker\Factory as Faker;

class SalaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Buscar dados necessários
        $escolas = Escola::all();
        $unidadesEscolares = UnidadeEscolar::all();

        if ($escolas->isEmpty()) {
            $this->command->error('É necessário ter escolas cadastradas antes de criar salas.');
            return;
        }

        $tipos = [
            ['nome' => 'Berçário', 'codigo' => 'BER', 'capacidade_min' => 6, 'capacidade_max' => 10],
            ['nome' => 'Maternal', 'codigo' => 'MAT', 'capacidade_min' => 10, 'capacidade_max' => 15],
            ['nome' => 'Infantil I', 'codigo' => 'INF1', 'capacidade_min' => 12, 'capacidade_max' => 18],
            ['nome' => 'Infantil II', 'codigo' => 'INF2', 'capacidade_min' => 15, 'capacidade_max' => 20],
            ['nome' => 'Pré-Escolar', 'codigo' => 'PRE', 'capacidade_min' => 18, 'capacidade_max' => 25],
            ['nome' => 'Jardim', 'codigo' => 'JAR', 'capacidade_min' => 20, 'capacidade_max' => 25],
        ];

        // Criar 30 salas distribuídas entre os tipos
        for ($i = 1; $i <= 30; $i++) {
            $tipo = $tipos[($i - 1) % count($tipos)];
            $letra = chr(65 + (($i - 1) % 26)); // A, B, C, ..., Z
            $numero = intval(($i - 1) / 26) + 1;

            $sufixo = $numero > 1 ? "{$letra}{$numero}" : $letra;

            Sala::firstOrCreate(
                ['codigo' => "{$tipo['codigo']}-{$sufixo}"],
                [
                    'nome' => "Sala {$tipo['nome']} {$sufixo}",
                    'capacidade' => $faker->numberBetween($tipo['capacidade_min'], $tipo['capacidade_max']),
                    'escola_id' => $escolas->first()->id,
                    'unidade_escolar_id' => $unidadesEscolares->isNotEmpty() ? $unidadesEscolares->random()->id : null,
                    'tipo' => $tipo['nome'],
                    'ativo' => true,
                    'descricao' => "Sala destinada ao atendimento de {$tipo['nome']}",
                ]
            );
        }

        $this->command->info('30 salas criadas com sucesso!');
    }
}
