<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Aluno;
use App\Models\Sala;

class AlunoSalaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar se existem alunos e salas
        $alunos = Aluno::all();
        $salas = Sala::all();
        
        if ($alunos->isEmpty() || $salas->isEmpty()) {
            $this->command->info('Não há alunos ou salas suficientes para criar relacionamentos.');
            return;
        }
        
        // Distribuir alunos pelas salas respeitando a capacidade
        $alunosSemSala = $alunos->whereNull('sala_id');
        
        if ($alunosSemSala->isEmpty()) {
            $this->command->info('Todos os alunos já estão vinculados a salas.');
            return;
        }
        
        $contadorVinculacoes = 0;
        
        foreach ($alunosSemSala as $aluno) {
            // Encontrar uma sala com capacidade disponível
            $salaDisponivel = null;
            
            foreach ($salas as $sala) {
                $alunosNaSala = Aluno::where('sala_id', $sala->id)->count();
                
                if ($alunosNaSala < $sala->capacidade) {
                    $salaDisponivel = $sala;
                    break;
                }
            }
            
            if ($salaDisponivel) {
                $aluno->update(['sala_id' => $salaDisponivel->id]);
                $contadorVinculacoes++;
                
                $this->command->info("Aluno {$aluno->nome} {$aluno->sobrenome} vinculado à sala {$salaDisponivel->codigo}");
            } else {
                $this->command->warn("Não há salas com capacidade disponível para o aluno {$aluno->nome} {$aluno->sobrenome}");
            }
        }
        
        $this->command->info("Total de {$contadorVinculacoes} alunos vinculados às salas com sucesso!");
        
        // Mostrar estatísticas
        foreach ($salas as $sala) {
            $totalAlunos = Aluno::where('sala_id', $sala->id)->count();
            $capacidade = (int) ($sala->capacidade ?? 0);
            $percentualOcupacao = $capacidade > 0
                ? round(($totalAlunos / $capacidade) * 100, 1)
                : 0;
            $this->command->info("Sala {$sala->codigo}: {$totalAlunos}/{$capacidade} alunos ({$percentualOcupacao}%)");
        }
    }
}