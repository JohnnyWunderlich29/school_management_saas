<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Planejamento;
use Carbon\Carbon;

class VerificarConflitoPlanejamentos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'planejamentos:verificar-conflitos {--limpar : Limpar planejamentos conflitantes} {--data= : Data específica para verificar (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica e opcionalmente limpa conflitos de planejamentos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = $this->option('data');
        $limpar = $this->option('limpar');

        if ($data) {
            $this->verificarDataEspecifica($data, $limpar);
        } else {
            $this->verificarTodosConflitos($limpar);
        }
    }

    private function verificarDataEspecifica($data, $limpar = false)
    {
        try {
            $dataCarbon = Carbon::parse($data);
            
            $this->info("Verificando conflitos para a data: {$dataCarbon->format('d/m/Y')}");
            
            $planejamentos = Planejamento::where(function($query) use ($dataCarbon) {
                $query->where('data_inicio', '<=', $dataCarbon)
                      ->where('data_fim', '>=', $dataCarbon);
            })->get();

            if ($planejamentos->isEmpty()) {
                $this->info('✅ Nenhum planejamento encontrado para esta data.');
                return;
            }

            $this->info("📋 Encontrados {$planejamentos->count()} planejamentos para esta data:");
            
            foreach ($planejamentos as $planejamento) {
                $this->line("ID: {$planejamento->id} | Período: {$planejamento->data_inicio->format('d/m/Y')} - {$planejamento->data_fim->format('d/m/Y')} | Turma: {$planejamento->turma_id} | Professor: {$planejamento->user_id} | Disciplina: {$planejamento->tipo_professor} | Status: {$planejamento->status}");
            }

            if ($limpar) {
                if ($this->confirm('Deseja realmente excluir estes planejamentos?')) {
                    foreach ($planejamentos as $planejamento) {
                        $planejamento->delete();
                        $this->info("🗑️ Planejamento ID {$planejamento->id} excluído.");
                    }
                    $this->info('✅ Planejamentos excluídos com sucesso!');
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Erro: {$e->getMessage()}");
        }
    }

    private function verificarTodosConflitos($limpar = false)
    {
        $this->info('🔍 Verificando todos os conflitos de planejamentos...');
        
        // Buscar planejamentos agrupados por turma, user_id e tipo_professor
        $grupos = Planejamento::selectRaw('turma_id, user_id, tipo_professor, COUNT(*) as total')
            ->groupBy('turma_id', 'user_id', 'tipo_professor')
            ->having('total', '>', 1)
            ->get();

        if ($grupos->isEmpty()) {
            $this->info('✅ Nenhum grupo com múltiplos planejamentos encontrado.');
            return;
        }

        foreach ($grupos as $grupo) {
            $this->info("\n📋 Verificando grupo: Turma {$grupo->turma_id}, Professor {$grupo->user_id}, Disciplina {$grupo->tipo_professor}");
            
            $planejamentos = Planejamento::where('turma_id', $grupo->turma_id)
                ->where('user_id', $grupo->user_id)
                ->where('tipo_professor', $grupo->tipo_professor)
                ->orderBy('data_inicio')
                ->get();

            $conflitos = [];
            
            for ($i = 0; $i < $planejamentos->count() - 1; $i++) {
                $atual = $planejamentos[$i];
                $proximo = $planejamentos[$i + 1];
                
                // Verificar sobreposição
                if ($atual->data_fim >= $proximo->data_inicio) {
                    $conflitos[] = [
                        'atual' => $atual,
                        'proximo' => $proximo
                    ];
                }
            }

            if (!empty($conflitos)) {
                $this->warn("⚠️ Encontrados {count($conflitos)} conflitos:");
                
                foreach ($conflitos as $conflito) {
                    $atual = $conflito['atual'];
                    $proximo = $conflito['proximo'];
                    
                    $this->line("   Conflito: ID {$atual->id} ({$atual->data_inicio->format('d/m/Y')} - {$atual->data_fim->format('d/m/Y')}) sobrepõe com ID {$proximo->id} ({$proximo->data_inicio->format('d/m/Y')} - {$proximo->data_fim->format('d/m/Y')})");
                }
                
                if ($limpar) {
                    if ($this->confirm("Deseja excluir os planejamentos mais recentes deste grupo?")) {
                        foreach ($conflitos as $conflito) {
                            $conflito['proximo']->delete();
                            $this->info("🗑️ Planejamento ID {$conflito['proximo']->id} excluído.");
                        }
                    }
                }
            } else {
                $this->info('✅ Nenhum conflito encontrado neste grupo.');
            }
        }
    }
}