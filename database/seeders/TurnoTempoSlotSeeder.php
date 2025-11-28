<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Turno;
use App\Models\TempoSlot;
use App\Models\Escola;

class TurnoTempoSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar todas as escolas para criar turnos específicos
        $escolas = Escola::all();
        
        if ($escolas->isEmpty()) {
            $this->command->warn('Nenhuma escola encontrada. Criando turnos globais (escola_id = null).');
            $this->criarTurnosParaEscola(null);
        } else {
            foreach ($escolas as $escola) {
                $this->criarTurnosParaEscola($escola->id);
            }
        }
    }

    /**
     * Cria turnos padrão para uma escola específica
     */
    private function criarTurnosParaEscola($escolaId)
    {
        $turnos = [
            [
                'nome' => 'Matutino',
                'codigo' => 'MAT',
                'hora_inicio' => '07:00:00',
                'hora_fim' => '11:00:00',
                'descricao' => 'Turno da manhã - 07h às 11h',
                'ordem' => 1,
                'escola_id' => $escolaId
            ],
            [
                'nome' => 'Vespertino',
                'codigo' => 'VES',
                'hora_inicio' => '13:00:00',
                'hora_fim' => '17:00:00',
                'descricao' => 'Turno da tarde - 13h às 17h',
                'ordem' => 2,
                'escola_id' => $escolaId
            ],
            [
                'nome' => 'Noturno',
                'codigo' => 'NOT',
                'hora_inicio' => '19:00:00',
                'hora_fim' => '22:00:00',
                'descricao' => 'Turno da noite - 19h às 22h',
                'ordem' => 3,
                'escola_id' => $escolaId
            ],
            [
                'nome' => 'Integral',
                'codigo' => 'INT',
                'hora_inicio' => '07:00:00',
                'hora_fim' => '17:00:00',
                'descricao' => 'Turno integral - 07h às 17h',
                'ordem' => 4,
                'escola_id' => $escolaId
            ]
        ];

        foreach ($turnos as $turnoData) {
            // Verificar se o turno já existe para esta escola
            $turnoExistente = Turno::where('codigo', $turnoData['codigo'])
                                  ->where('escola_id', $escolaId)
                                  ->first();

            if (!$turnoExistente) {
                $turno = Turno::create($turnoData);
                $this->criarTempoSlotsParaTurno($turno);
                
                $escolaInfo = $escolaId ? "escola ID {$escolaId}" : "global";
                $this->command->info("Turno {$turno->nome} criado para {$escolaInfo} com tempo slots.");
            } else {
                $escolaInfo = $escolaId ? "escola ID {$escolaId}" : "global";
                $this->command->warn("Turno {$turnoData['nome']} já existe para {$escolaInfo}.");
            }
        }
    }

    /**
     * Cria tempo slots para um turno específico
     */
    private function criarTempoSlotsParaTurno(Turno $turno)
    {
        // Verificar se já existem tempo slots para este turno
        if ($turno->tempoSlots()->count() > 0) {
            return;
        }

        $slots = [];
        
        switch (strtolower($turno->codigo)) {
            case 'mat': // Matutino
                $slots = [
                    ['nome' => '1º Tempo', 'tipo' => 'aula', 'hora_inicio' => '07:00', 'hora_fim' => '07:50', 'ordem' => 1],
                    ['nome' => '2º Tempo', 'tipo' => 'aula', 'hora_inicio' => '07:50', 'hora_fim' => '08:40', 'ordem' => 2],
                    ['nome' => 'Recreio', 'tipo' => 'recreio', 'hora_inicio' => '08:40', 'hora_fim' => '09:00', 'ordem' => 3],
                    ['nome' => '3º Tempo', 'tipo' => 'aula', 'hora_inicio' => '09:00', 'hora_fim' => '09:50', 'ordem' => 4],
                    ['nome' => '4º Tempo', 'tipo' => 'aula', 'hora_inicio' => '09:50', 'hora_fim' => '10:40', 'ordem' => 5],
                ];
                break;
                
            case 'ves': // Vespertino
                $slots = [
                    ['nome' => '1º Tempo', 'tipo' => 'aula', 'hora_inicio' => '13:00', 'hora_fim' => '13:50', 'ordem' => 1],
                    ['nome' => '2º Tempo', 'tipo' => 'aula', 'hora_inicio' => '13:50', 'hora_fim' => '14:40', 'ordem' => 2],
                    ['nome' => 'Recreio', 'tipo' => 'recreio', 'hora_inicio' => '14:40', 'hora_fim' => '15:00', 'ordem' => 3],
                    ['nome' => '3º Tempo', 'tipo' => 'aula', 'hora_inicio' => '15:00', 'hora_fim' => '15:50', 'ordem' => 4],
                    ['nome' => '4º Tempo', 'tipo' => 'aula', 'hora_inicio' => '15:50', 'hora_fim' => '16:40', 'ordem' => 5],
                ];
                break;
                
            case 'not': // Noturno
                $slots = [
                    ['nome' => '1º Tempo', 'tipo' => 'aula', 'hora_inicio' => '19:00', 'hora_fim' => '19:45', 'ordem' => 1],
                    ['nome' => '2º Tempo', 'tipo' => 'aula', 'hora_inicio' => '19:45', 'hora_fim' => '20:30', 'ordem' => 2],
                    ['nome' => 'Intervalo', 'tipo' => 'intervalo', 'hora_inicio' => '20:30', 'hora_fim' => '20:45', 'ordem' => 3],
                    ['nome' => '3º Tempo', 'tipo' => 'aula', 'hora_inicio' => '20:45', 'hora_fim' => '21:30', 'ordem' => 4],
                ];
                break;
                
            case 'int': // Integral
                $slots = [
                    ['nome' => '1º Tempo', 'tipo' => 'aula', 'hora_inicio' => '07:00', 'hora_fim' => '07:50', 'ordem' => 1],
                    ['nome' => '2º Tempo', 'tipo' => 'aula', 'hora_inicio' => '07:50', 'hora_fim' => '08:40', 'ordem' => 2],
                    ['nome' => 'Recreio', 'tipo' => 'recreio', 'hora_inicio' => '08:40', 'hora_fim' => '09:00', 'ordem' => 3],
                    ['nome' => '3º Tempo', 'tipo' => 'aula', 'hora_inicio' => '09:00', 'hora_fim' => '09:50', 'ordem' => 4],
                    ['nome' => '4º Tempo', 'tipo' => 'aula', 'hora_inicio' => '09:50', 'hora_fim' => '10:40', 'ordem' => 5],
                    ['nome' => 'Almoço', 'tipo' => 'almoco', 'hora_inicio' => '11:00', 'hora_fim' => '12:00', 'ordem' => 6],
                    ['nome' => '5º Tempo', 'tipo' => 'aula', 'hora_inicio' => '13:00', 'hora_fim' => '13:50', 'ordem' => 7],
                    ['nome' => '6º Tempo', 'tipo' => 'aula', 'hora_inicio' => '13:50', 'hora_fim' => '14:40', 'ordem' => 8],
                    ['nome' => 'Recreio', 'tipo' => 'recreio', 'hora_inicio' => '14:40', 'hora_fim' => '15:00', 'ordem' => 9],
                    ['nome' => '7º Tempo', 'tipo' => 'aula', 'hora_inicio' => '15:00', 'hora_fim' => '15:50', 'ordem' => 10],
                ];
                break;
        }

        foreach ($slots as $slotData) {
            $slotData['turno_id'] = $turno->id;
            TempoSlot::create($slotData);
        }
    }
}
