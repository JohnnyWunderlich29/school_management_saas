<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\Historico;
use Carbon\Carbon;

class OptimizeSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:optimize 
                            {--cache : Limpar e recriar cache}
                            {--database : Otimizar banco de dados}
                            {--logs : Limpar logs antigos}
                            {--all : Executar todas as otimizações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Otimizar o sistema para melhor performance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Iniciando otimização do sistema...');
        
        $startTime = microtime(true);
        
        if ($this->option('all') || $this->option('cache')) {
            $this->optimizeCache();
        }
        
        if ($this->option('all') || $this->option('database')) {
            $this->optimizeDatabase();
        }
        
        if ($this->option('all') || $this->option('logs')) {
            $this->cleanLogs();
        }
        
        if ($this->option('all')) {
            $this->optimizeApplication();
        }
        
        $endTime = microtime(true);
        $executionTime = round(($endTime - $startTime) * 1000, 2);
        
        $this->info("✅ Otimização concluída em {$executionTime}ms");
        
        return Command::SUCCESS;
    }
    
    /**
     * Otimizar cache
     */
    private function optimizeCache()
    {
        $this->info('🔄 Otimizando cache...');
        
        // Limpar cache antigo
        Cache::flush();
        $this->line('   ✓ Cache limpo');
        
        // Recriar cache de configuração
        Artisan::call('config:cache');
        $this->line('   ✓ Cache de configuração recriado');
        
        // Recriar cache de rotas
        Artisan::call('route:cache');
        $this->line('   ✓ Cache de rotas recriado');
        
        // Recriar cache de views
        Artisan::call('view:cache');
        $this->line('   ✓ Cache de views recriado');
        
        $this->info('✅ Cache otimizado com sucesso');
    }
    
    /**
     * Otimizar banco de dados
     */
    private function optimizeDatabase()
    {
        $this->info('🗄️  Otimizando banco de dados...');
        
        try {
            $driver = config('database.default');
            $connection = config("database.connections.{$driver}.driver");
            
            if ($connection === 'sqlite') {
                // Otimizações específicas para SQLite
                DB::statement('VACUUM');
                DB::statement('ANALYZE');
                $this->line('   ✓ Banco SQLite otimizado (VACUUM e ANALYZE executados)');
            } elseif ($connection === 'mysql') {
                // Analisar tabelas MySQL
                $tables = DB::select('SHOW TABLES');
                $tableCount = 0;
                
                foreach ($tables as $table) {
                    $tableName = array_values((array) $table)[0];
                    
                    // Otimizar tabela
                    DB::statement("OPTIMIZE TABLE `{$tableName}`");
                    $tableCount++;
                }
                
                $this->line("   ✓ {$tableCount} tabelas MySQL otimizadas");
                 
                 // Atualizar estatísticas das tabelas MySQL
                 DB::statement('ANALYZE TABLE alunos, presencas, escalas, funcionarios, responsaveis');
                 $this->line('   ✓ Estatísticas das tabelas atualizadas');
             }
             
             // Limpar históricos antigos (mais de 6 meses)
             $deletedHistoricos = \App\Models\Historico::where('created_at', '<', \Carbon\Carbon::now()->subMonths(6))->delete();
             $this->line("   ✓ {$deletedHistoricos} registros de histórico antigos removidos");
            
            $this->info('✅ Banco de dados otimizado com sucesso');
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao otimizar banco de dados: ' . $e->getMessage());
        }
    }
    
    /**
     * Limpar logs antigos
     */
    private function cleanLogs()
    {
        $this->info('🧹 Limpando logs antigos...');
        
        try {
            $logPath = storage_path('logs');
            $files = glob($logPath . '/*.log');
            $deletedFiles = 0;
            $cutoffDate = Carbon::now()->subDays(30);
            
            foreach ($files as $file) {
                $fileTime = Carbon::createFromTimestamp(filemtime($file));
                
                if ($fileTime->lt($cutoffDate)) {
                    unlink($file);
                    $deletedFiles++;
                }
            }
            
            $this->line("   ✓ {$deletedFiles} arquivos de log antigos removidos");
            
            // Limpar cache de logs
            if (Storage::disk('local')->exists('logs')) {
                Storage::disk('local')->deleteDirectory('logs/old');
            }
            
            $this->info('✅ Logs limpos com sucesso');
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao limpar logs: ' . $e->getMessage());
        }
    }
    
    /**
     * Otimizar aplicação
     */
    private function optimizeApplication()
    {
        $this->info('⚡ Otimizando aplicação...');
        
        // Otimizar autoloader
        exec('composer dump-autoload --optimize --no-dev', $output, $returnCode);
        if ($returnCode === 0) {
            $this->line('   ✓ Autoloader otimizado');
        }
        
        // Limpar cache de eventos
        Artisan::call('event:cache');
        $this->line('   ✓ Cache de eventos recriado');
        
        // Otimizar para produção se não estiver em desenvolvimento
        if (!app()->environment('local')) {
            Artisan::call('optimize');
            $this->line('   ✓ Otimizações de produção aplicadas');
        }
        
        $this->info('✅ Aplicação otimizada com sucesso');
    }
}