<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ModalidadeEnsino;
use App\Models\EscolaModalidadeConfig;

class ConfigurarModalidadesPersonalizadas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modalidades:configurar-personalizadas {--escola_id= : ID da escola específica (opcional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configura automaticamente modalidades personalizadas que não possuem configuração na tabela escola_modalidades_config';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $escolaId = $this->option('escola_id');
        
        // Query para buscar modalidades personalizadas sem configuração
        $query = ModalidadeEnsino::whereNotNull('escola_id')
            ->whereDoesntHave('configuracaoEscola');
            
        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }
        
        $modalidadesSemConfig = $query->get();
        
        if ($modalidadesSemConfig->isEmpty()) {
            $this->info('Não foram encontradas modalidades personalizadas sem configuração.');
            return;
        }
        
        $this->info("Encontradas {$modalidadesSemConfig->count()} modalidades personalizadas sem configuração:");
        
        $configuracoesCriadas = 0;
        
        foreach ($modalidadesSemConfig as $modalidade) {
            $this->line("- {$modalidade->nome} (ID: {$modalidade->id}, Escola: {$modalidade->escola_id})");
            
            try {
                EscolaModalidadeConfig::create([
                    'escola_id' => $modalidade->escola_id,
                    'modalidade_ensino_id' => $modalidade->id,
                    'ativo' => true,
                    'capacidade_minima_turma' => 1,
                    'capacidade_maxima_turma' => 30,
                    'permite_turno_matutino' => true,
                    'permite_turno_vespertino' => true,
                    'permite_turno_noturno' => false,
                    'permite_turno_integral' => false,
                    'data_ativacao' => now(),
                    'created_by' => null, // Comando automático
                    'updated_by' => null,
                ]);
                
                $configuracoesCriadas++;
                $this->info("  ✓ Configuração criada com sucesso");
                
            } catch (\Exception $e) {
                $this->error("  ✗ Erro ao criar configuração: " . $e->getMessage());
            }
        }
        
        $this->info("\nResumo:");
        $this->info("- Modalidades encontradas: {$modalidadesSemConfig->count()}");
        $this->info("- Configurações criadas: {$configuracoesCriadas}");
        
        if ($configuracoesCriadas > 0) {
            $this->info("\n✓ Processo concluído com sucesso!");
        }
    }
}