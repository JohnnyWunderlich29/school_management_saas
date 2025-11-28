<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sala;

class SalasComTurnosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salas = [
            [
                'nome' => 'Sala de Educação Infantil A',
                'codigo' => 'EI-A',
                'descricao' => 'Sala para educação infantil - turno matutino',
                'capacidade' => 20,
                'ativo' => true,
                'turno_matutino' => true,
                'turno_vespertino' => false,
                'turno_noturno' => false,
                'turno_integral' => false,
            ],
            [
                'nome' => 'Sala de Educação Infantil B',
                'codigo' => 'EI-B',
                'descricao' => 'Sala para educação infantil - turno vespertino',
                'capacidade' => 20,
                'ativo' => true,
                'turno_matutino' => false,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => false,
            ],
            [
                'nome' => 'Sala Fundamental 1º Ano',
                'codigo' => 'F1-01',
                'descricao' => 'Sala para 1º ano do ensino fundamental',
                'capacidade' => 25,
                'ativo' => true,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => false,
            ],
            [
                'nome' => 'Sala Fundamental 2º Ano',
                'codigo' => 'F1-02',
                'descricao' => 'Sala para 2º ano do ensino fundamental',
                'capacidade' => 25,
                'ativo' => true,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => false,
                'turno_integral' => false,
            ],
            [
                'nome' => 'Sala EJA Noturno',
                'codigo' => 'EJA-N1',
                'descricao' => 'Sala para Educação de Jovens e Adultos - noturno',
                'capacidade' => 30,
                'ativo' => true,
                'turno_matutino' => false,
                'turno_vespertino' => false,
                'turno_noturno' => true,
                'turno_integral' => false,
            ],
            [
                'nome' => 'Sala Integral A',
                'codigo' => 'INT-A',
                'descricao' => 'Sala para educação integral',
                'capacidade' => 22,
                'ativo' => true,
                'turno_matutino' => false,
                'turno_vespertino' => false,
                'turno_noturno' => false,
                'turno_integral' => true,
            ],
            [
                'nome' => 'Laboratório de Informática',
                'codigo' => 'LAB-INFO',
                'descricao' => 'Laboratório de informática - todos os turnos',
                'capacidade' => 15,
                'ativo' => true,
                'turno_matutino' => true,
                'turno_vespertino' => true,
                'turno_noturno' => true,
                'turno_integral' => true,
            ],
        ];

        foreach ($salas as $salaData) {
            Sala::updateOrCreate(
                ['codigo' => $salaData['codigo']],
                $salaData
            );
        }
    }
}