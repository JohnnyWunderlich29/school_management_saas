<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Aluno;
use App\Models\Responsavel;

class VerificarVinculosCommand extends Command
{
    protected $signature = 'debug:verificar-vinculos';
    protected $description = 'Verifica os vínculos entre alunos e responsáveis';

    public function handle()
    {
        $this->info('=== VERIFICAÇÃO DE VÍNCULOS ALUNO-RESPONSÁVEL ===');
        
        // Contar registros
        $totalAlunos = Aluno::count();
        $totalResponsaveis = Responsavel::count();
        $totalVinculos = DB::table('aluno_responsavel')->count();
        
        $this->info("Total de Alunos: {$totalAlunos}");
        $this->info("Total de Responsáveis: {$totalResponsaveis}");
        $this->info("Total de Vínculos: {$totalVinculos}");
        
        // Verificar vínculos na tabela pivot
        $this->info('\n=== VÍNCULOS NA TABELA PIVOT ===');
        $vinculos = DB::table('aluno_responsavel')
            ->join('alunos', 'aluno_responsavel.aluno_id', '=', 'alunos.id')
            ->join('responsaveis', 'aluno_responsavel.responsavel_id', '=', 'responsaveis.id')
            ->select(
                'aluno_responsavel.aluno_id',
                'aluno_responsavel.responsavel_id', 
                'aluno_responsavel.responsavel_principal',
                'alunos.nome as aluno_nome',
                'responsaveis.nome as responsavel_nome'
            )
            ->limit(10)
            ->get();
            
        foreach ($vinculos as $vinculo) {
            $principal = $vinculo->responsavel_principal ? 'SIM' : 'NÃO';
            $this->info("Aluno: {$vinculo->aluno_nome} (ID: {$vinculo->aluno_id}) -> Responsável: {$vinculo->responsavel_nome} (ID: {$vinculo->responsavel_id}) - Principal: {$principal}");
        }
        
        // Verificar alunos sem responsáveis
        $this->info('\n=== ALUNOS SEM RESPONSÁVEIS ===');
        $alunosSemResponsaveis = Aluno::whereDoesntHave('responsaveis')->get();
        if ($alunosSemResponsaveis->count() > 0) {
            foreach ($alunosSemResponsaveis as $aluno) {
                $this->warn("Aluno sem responsáveis: {$aluno->nome} {$aluno->sobrenome} (ID: {$aluno->id})");
            }
        } else {
            $this->info('Todos os alunos têm responsáveis vinculados.');
        }
        
        // Verificar responsáveis sem alunos
        $this->info('\n=== RESPONSÁVEIS SEM ALUNOS ===');
        $responsaveisSemAlunos = Responsavel::whereDoesntHave('alunos')->get();
        if ($responsaveisSemAlunos->count() > 0) {
            $this->info("Responsáveis sem alunos: {$responsaveisSemAlunos->count()}");
        } else {
            $this->info('Todos os responsáveis têm alunos vinculados.');
        }
        
        // Verificar alunos sem responsável principal
        $this->info('\n=== ALUNOS SEM RESPONSÁVEL PRINCIPAL ===');
        $alunosSemPrincipal = Aluno::whereDoesntHave('responsaveis', function($query) {
            $query->where('responsavel_principal', true);
        })->get();
        
        if ($alunosSemPrincipal->count() > 0) {
            foreach ($alunosSemPrincipal as $aluno) {
                $this->warn("Aluno sem responsável principal: {$aluno->nome} {$aluno->sobrenome} (ID: {$aluno->id})");
            }
        } else {
            $this->info('Todos os alunos têm responsável principal.');
        }
        
        $this->info('\n=== VERIFICAÇÃO CONCLUÍDA ===');
        
        return 0;
    }
}