<?php

namespace Database\Seeders;

use App\Models\Escala;
use App\Models\Funcionario;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EscalaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $funcionarios = Funcionario::all();
        
        if ($funcionarios->isEmpty()) {
            $this->command->info('Nenhum funcionário encontrado. Execute o FuncionarioSeeder primeiro.');
            return;
        }
        
        // Criar escalas para os próximos 30 dias
        $dataInicio = Carbon::now();
        $dataFim = Carbon::now()->addDays(30);
        
        $tiposEscala = ['Normal', 'Extra', 'Substituição'];
        $statusOptions = ['Agendada', 'Ativa', 'Concluída'];
        
        // Para cada dia nos próximos 30 dias
        for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addDay()) {
            // Pular fins de semana (opcional)
            if ($data->isWeekend()) {
                continue;
            }
            
            // Criar escalas para alguns funcionários aleatórios
            $funcionariosDoDia = $funcionarios->random(min(rand(2, 5), $funcionarios->count()));
            
            foreach ($funcionariosDoDia as $funcionario) {
                $tipoEscala = fake()->randomElement($tiposEscala);
                
                // Definir horários padrão ou variações simples
                $horariosDisponiveis = [
                    ['08:00', '12:00'], // Manhã
                    ['13:00', '17:00'], // Tarde
                    ['08:00', '17:00'], // Integral
                    ['19:00', '22:00'], // Noite
                ];
                
                $horario = fake()->randomElement($horariosDisponiveis);
                $horaInicio = $horario[0];
                $horaFim = $horario[1];
                
                Escala::create([
                    'funcionario_id' => $funcionario->id,
                    'data' => $data->format('Y-m-d'),
                    'hora_inicio' => $horaInicio,
                    'hora_fim' => $horaFim,
                    'tipo_escala' => $tipoEscala,
                    'status' => fake()->randomElement($statusOptions),
                    'observacoes' => fake()->optional(0.3)->sentence(),
                ]);
            }
        }
    }
}