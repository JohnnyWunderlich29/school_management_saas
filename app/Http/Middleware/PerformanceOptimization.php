<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PerformanceOptimization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Iniciar medição de performance
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Configurar otimizações de query
        $this->optimizeQueries();
        
        // Processar request
        $response = $next($request);
        
        // Calcular métricas de performance
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        $executionTime = ($endTime - $startTime) * 1000; // em millisegundos
        $memoryUsage = ($endMemory - $startMemory) / 1024 / 1024; // em MB
        
        // Log de performance para requests lentos
        if ($executionTime > 1000) { // > 1 segundo
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime . 'ms',
                'memory_usage' => $memoryUsage . 'MB',
                'user_id' => auth()->id(),
            ]);
        }
        
        // Adicionar headers de performance em desenvolvimento
        if (config('app.debug')) {
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
            $response->headers->set('X-Memory-Usage', $memoryUsage . 'MB');
            $response->headers->set('X-Query-Count', DB::getQueryLog() ? count(DB::getQueryLog()) : 0);
        }
        
        return $response;
    }
    
    /**
     * Otimizar configurações de query
     */
    private function optimizeQueries()
    {
        // Habilitar query log apenas em desenvolvimento
        if (config('app.debug')) {
            DB::enableQueryLog();
        }
        
        // Configurar timeout para queries
        DB::statement('SET SESSION wait_timeout = ' . config('performance.query.long_query_timeout', 30));
        
        // Otimizar configurações do MySQL se disponível
        if (config('database.default') === 'mysql') {
            try {
                // Configurar buffer pool para melhor performance
                DB::statement('SET SESSION sql_buffer_result = 1');
                
                // Otimizar joins
                DB::statement('SET SESSION join_buffer_size = 262144');
                
            } catch (\Exception $e) {
                // Ignorar erros de configuração em ambientes restritivos
                Log::debug('Could not optimize MySQL settings: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Limpar cache se necessário
     */
    private function clearCacheIfNeeded()
    {
        $memoryLimit = ini_get('memory_limit');
        $currentUsage = memory_get_usage(true);
        
        // Converter limite de memória para bytes
        $limitBytes = $this->convertToBytes($memoryLimit);
        
        // Se uso de memória > 80% do limite, limpar cache
        if ($currentUsage > ($limitBytes * 0.8)) {
            Cache::flush();
            Log::info('Cache cleared due to high memory usage', [
                'memory_usage' => $currentUsage,
                'memory_limit' => $limitBytes
            ]);
        }
    }
    
    /**
     * Converter string de memória para bytes
     */
    private function convertToBytes($value)
    {
        $unit = strtolower(substr($value, -1));
        $value = (int) $value;
        
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
}