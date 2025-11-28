<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Turno;

class TurnoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $turnos = [
            [
                'nome' => 'Matutino',
                'codigo' => 'MAT',
                'hora_inicio' => '07:00',
                'hora_fim' => '12:00',
                'descricao' => 'Turno da manhã',
                'ordem' => 1
            ],
            [
                'nome' => 'Vespertino',
                'codigo' => 'VES',
                'hora_inicio' => '13:00',
                'hora_fim' => '18:00',
                'descricao' => 'Turno da tarde',
                'ordem' => 2
            ],
            [
                'nome' => 'Noturno',
                'codigo' => 'NOT',
                'hora_inicio' => '19:00',
                'hora_fim' => '23:00',
                'descricao' => 'Turno da noite',
                'ordem' => 3
            ],
            [
                'nome' => 'Integral',
                'codigo' => 'INT',
                'hora_inicio' => '07:00',
                'hora_fim' => '17:00',
                'descricao' => 'Turno integral',
                'ordem' => 4
            ]
        ];

        foreach ($turnos as $turno) {
            Turno::updateOrCreate(
                ['codigo' => $turno['codigo']],
                $turno
            );
        }
    }
}
