<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ModalidadeEnsino;
use App\Models\Turno;
use App\Models\Grupo;
use App\Models\Disciplina;
use App\Http\Controllers\PlanejamentoController;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\Environment\Console;

class TestDisciplinas extends Command
{
    protected $signature = 'test:disciplinas';
    protected $description = 'Testa o carregamento de disciplinas por modalidade, turno e grupo';

    public function handle()
    {
        $this->info('Testando carregamento de disciplinas...');
        
        // Pegar dados de teste
        $modalidade = ModalidadeEnsino::first();
        $turno = Turno::first();
        $grupo = Grupo::first();
        
        if (!$modalidade || !$turno || !$grupo) {
            $this->error('Dados de teste não encontrados!');
            return;
        }
        
        $this->info("Testando com:");
        $this->info("- Modalidade: {$modalidade->nome} (ID: {$modalidade->id})");
        $this->info("- Turno: {$turno->nome} (ID: {$turno->id})");
        $this->info("- Grupo: {$grupo->nome} (ID: {$grupo->id})");
        
        // Simular usuário autenticado para teste
        $user = \App\Models\User::find(3);
        if (!$user) {
            $this->error('Nenhum usuário encontrado para teste!');
            return;
        }
        
        if($user){
            \Illuminate\Support\Facades\Auth::login($user);
        }
        \Illuminate\Support\Facades\Auth::login($user, true);
        $this->info("Usuário autenticado: {$user->name} (Escola ID: {$user->escola_id})");
        
        // Testar método do controller
        $controller = new PlanejamentoController();
        $request = new \Illuminate\Http\Request([
            'modalidade_id' => $modalidade->id,
            'turno_id' => $turno->id,
            'grupo_id' => $grupo->id
        ]);
        
        try {
            $response = $controller->getDisciplinasPorModalidadeTurnoGrupo($request);
            $disciplinas = $response->getData(true); // true para retornar array
            
            $this->info("\nResultado:");
            $this->info("Disciplinas encontradas: " . count($disciplinas));
            
            foreach ($disciplinas as $index => $disciplina) {
                $this->info("- Disciplina {$index}: " . json_encode($disciplina));
            }
            
        } catch (\Exception $e) {
            $this->error("Erro ao executar método: " . $e->getMessage());
        }
        
        // Testar consulta direta com filtro de escola
        $this->info("\n--- Teste direto da consulta com filtro de escola ---");
        
        $escolaId = $user->escola_id;
        $disciplinasQuery = Disciplina::where(function($query) use ($modalidade, $turno, $grupo, $escolaId) {
            // Relacionamento antigo (um-para-muitos) - disciplina_id na tabela salas
            $query->whereExists(function($existsQuery) use ($modalidade, $turno, $grupo, $escolaId) {
                $existsQuery->select(\DB::raw(1))
                           ->from('salas')
                           ->whereColumn('disciplinas.id', 'salas.disciplina_id')
                           ->where('salas.modalidade_ensino_id', $modalidade->id)
                           ->where('salas.turno_id', $turno->id)
                           ->where('salas.grupo_id', $grupo->id)
                           ->where('salas.escola_id', $escolaId)
                           ->where('salas.ativo', true);
            })
            // Relacionamento novo (muitos-para-muitos) - tabela pivot sala_disciplinas
            ->orWhere(function($subQuery) use ($modalidade, $turno, $grupo, $escolaId) {
                $subQuery->whereExists(function($existsQuery) use ($modalidade, $turno, $grupo, $escolaId) {
                    $existsQuery->select(\DB::raw(1))
                               ->from('sala_disciplinas')
                               ->join('salas', 'salas.id', '=', 'sala_disciplinas.sala_id')
                               ->whereColumn('disciplinas.id', 'sala_disciplinas.disciplina_id')
                               ->where('salas.modalidade_ensino_id', $modalidade->id)
                               ->where('salas.turno_id', $turno->id)
                               ->where('salas.grupo_id', $grupo->id)
                               ->where('salas.escola_id', $escolaId)
                               ->where('salas.ativo', true)
                               ->where('sala_disciplinas.ativo', true);
                });
            });
        })->where('disciplinas.ativo', true)->distinct()->get();
        
        $this->info("Disciplinas encontradas (consulta direta com escola): " . $disciplinasQuery->count());
        
        foreach ($disciplinasQuery as $disciplina) {
            $this->info("- {$disciplina->nome} (ID: {$disciplina->id})");
        }
        
        // Verificar dados das salas com filtro de escola
        $this->info("\n--- Verificação de dados por escola ---");
        $salasAntigo = \App\Models\Sala::where('modalidade_ensino_id', $modalidade->id)
            ->where('turno_id', $turno->id)
            ->where('grupo_id', $grupo->id)
            ->where('escola_id', $escolaId)
            ->where('ativo', true)
            ->whereNotNull('disciplina_id')
            ->count();
            
        $salasNovo = \App\Models\Sala::where('modalidade_ensino_id', $modalidade->id)
            ->where('turno_id', $turno->id)
            ->where('grupo_id', $grupo->id)
            ->where('escola_id', $escolaId)
            ->where('ativo', true)
            ->whereHas('disciplinas')
            ->count();
            
        $this->info("Salas com disciplina_id (relacionamento antigo) na escola {$escolaId}: {$salasAntigo}");
        $this->info("Salas com disciplinas via pivot (relacionamento novo) na escola {$escolaId}: {$salasNovo}");
    }
}