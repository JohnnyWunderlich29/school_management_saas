<?php

namespace Database\Seeders;

use App\Models\Presenca;
use App\Models\Aluno;
use App\Models\Funcionario;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Faker\Factory as Faker;

class PresencaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        $alunos = Aluno::all();
        $funcionarios = Funcionario::all();
        
        if ($alunos->isEmpty() || $funcionarios->isEmpty()) {
            $this->command->info('Não há alunos ou funcionários suficientes para criar presenças.');
            return;
        }
        
        // Criar presenças para os últimos 7 dias apenas (para reduzir uso de memória)
        $dataInicio = Carbon::now()->subDays(7);
        $dataFim = Carbon::now();
        
        $contadorPresencas = 0;
        
        // Limitar a 50 alunos para evitar problemas de memória
        $alunosLimitados = $alunos->take(50);
        
        // Para cada dia nos últimos 7 dias
        for ($data = $dataInicio->copy(); $data->lte($dataFim); $data->addDay()) {
            // Pular fins de semana (opcional)
            if ($data->isWeekend()) {
                continue;
            }
            
            // Para cada aluno limitado, criar presença com 85% de chance de estar presente
            foreach ($alunosLimitados as $aluno) {
                // Verificar se já existe presença para este aluno nesta data
                $existePresenca = Presenca::where('aluno_id', $aluno->id)
                    ->where('data', $data->format('Y-m-d'))
                    ->exists();
                    
                if ($existePresenca) {
                    continue;
                }
                
                $presente = $faker->boolean(85); // 85% de chance de estar presente
                $funcionario = $funcionarios->random();
                
                $presencaData = [
                    'aluno_id' => $aluno->id,
                    'funcionario_id' => $funcionario->id,
                    'data' => $data->format('Y-m-d'),
                    'presente' => $presente,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                if ($presente) {
                    // Se presente, definir horários
                    $horaEntrada = $faker->time('H:i', '09:00'); // Entrada até 9h
                    $presencaData['hora_entrada'] = $horaEntrada;
                    
                    // 20% de chance de ter saída mais cedo
                    if ($faker->boolean(20)) {
                        $horaSaida = $faker->time('H:i', '16:00'); // Saída até 16h
                        $presencaData['hora_saida'] = $horaSaida;
                        $presencaData['justificativa'] = $faker->randomElement([
                            'Consulta médica',
                            'Compromisso familiar',
                            'Mal estar',
                            'Autorização dos pais'
                        ]);
                    }
                } else {
                    // Se ausente, definir justificativa
                    $presencaData['justificativa'] = $faker->randomElement([
                        'Doença',
                        'Consulta médica',
                        'Viagem familiar',
                        'Problemas familiares',
                        'Falta não justificada'
                    ]);
                }
                
                // Adicionar observações ocasionalmente
                if ($faker->boolean(15)) {
                    $presencaData['observacoes'] = $faker->sentence();
                }
                
                Presenca::create($presencaData);
                $contadorPresencas++;
            }
        }
        
        $this->command->info("Criadas {$contadorPresencas} presenças de teste.");
        
        // Mostrar estatísticas
        $totalPresencas = Presenca::count();
        $presencasHoje = Presenca::where('data', Carbon::today())->count();
        $percentualPresencaHoje = $alunosLimitados->count() > 0 ? round(($presencasHoje / $alunosLimitados->count()) * 100, 1) : 0;
        
        $this->command->info("Total de presenças no sistema: {$totalPresencas}");
        $this->command->info("Presenças registradas hoje: {$presencasHoje}/{$alunosLimitados->count()} ({$percentualPresencaHoje}%)");
    }
}