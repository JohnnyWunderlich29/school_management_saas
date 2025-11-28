<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

class PerformanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Registrar configurações de performance
        $this->mergeConfigFrom(
            __DIR__.'/../../config/performance.php', 'performance'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Otimizações gerais
        $this->optimizeEloquent();
        $this->optimizeDatabase();
        $this->optimizeViews();
        $this->optimizePagination();
        
        // Apenas em produção
        if ($this->app->environment('production')) {
            $this->optimizeForProduction();
        }
        
        // Apenas em desenvolvimento
        if ($this->app->environment('local')) {
            $this->optimizeForDevelopment();
        }
    }
    
    /**
     * Otimizar Eloquent
     */
    private function optimizeEloquent()
    {
        // Prevenir lazy loading em produção
        if ($this->app->environment('production')) {
            Model::preventLazyLoading();
        }
        
        // Prevenir acesso a atributos não preenchidos
        Model::preventAccessingMissingAttributes();
    }
    
    /**
     * Otimizar configurações do banco de dados
     */
    private function optimizeDatabase()
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}.driver");
        
        // Configurar modo strict apenas para MySQL em desenvolvimento
        if ($this->app->environment('local', 'development') && $connection === 'mysql') {
            try {
                DB::statement("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            } catch (\Exception $e) {
                Log::warning('Could not set MySQL strict mode: ' . $e->getMessage());
            }
        }
        
        // Otimizações específicas para SQLite
        if ($connection === 'sqlite') {
            try {
                DB::statement('PRAGMA journal_mode=WAL');
                DB::statement('PRAGMA synchronous=NORMAL');
                DB::statement('PRAGMA cache_size=10000');
                DB::statement('PRAGMA temp_store=MEMORY');
            } catch (\Exception $e) {
                Log::warning('Could not optimize SQLite: ' . $e->getMessage());
            }
        }
        
        // Log de queries lentas em desenvolvimento
        if ($this->app->environment('local', 'development')) {
            DB::listen(function ($query) {
                if ($query->time > config('performance.database.slow_query_threshold', 1000)) {
                    Log::warning('Slow Query Detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time
                    ]);
                }
            });
        }
    }
    
    /**
     * Otimizar Views
     */
    private function optimizeViews()
    {
        // Compartilhar dados comuns com todas as views
        View::composer('*', function ($view) {
            // Cache de dados frequentemente usados
            $view->with('cached_data', $this->getCachedCommonData());
        });
        
        // Otimizar views específicas
        View::composer(['dashboard', 'alunos.index', 'presencas.index'], function ($view) {
            // Pré-carregar dados necessários
            $view->with('performance_mode', true);
        });

        // Disponibilizar mapas de labels para cronogramas de planejamentos
        View::composer([
            'planejamentos.cronograma-dia',
            'planejamentos.partials.cronograma-detalhe-card',
        ], function ($view) {
            $maps = Cache::remember('planejamentos_maps', 600, function () {
                $campos = \App\Models\CampoExperiencia::ativos()->pluck('nome', 'id')->toArray();
                $saberes = \App\Models\SaberConhecimento::ativos()->pluck('titulo', 'id')->toArray();
                // Montar label "codigo - descricao" para objetivos
                $objetivos = \App\Models\ObjetivoAprendizagem::ativos()
                    ->get(['id', 'codigo', 'descricao'])
                    ->mapWithKeys(function ($obj) {
                        $codigo = trim((string)($obj->codigo ?? ''));
                        $descricao = trim((string)($obj->descricao ?? ''));
                        $label = $descricao;
                        if ($codigo !== '') {
                            $label = $codigo . ' - ' . $descricao;
                        }
                        if ($label === '') {
                            $label = (string)$obj->id;
                        }
                        return [$obj->id => $label];
                    })
                    ->toArray();
                return compact('campos', 'saberes', 'objetivos');
            });
            $view->with('maps', $maps);
        });
    }
    
    /**
     * Otimizar Paginação
     */
    private function optimizePagination()
    {
        // Usar paginação simples por padrão (sem contagem total)
        Paginator::defaultSimpleView('pagination::simple-bootstrap-4');
        
        // Configurar tamanho padrão de página
        Paginator::defaultView('pagination::bootstrap-4');
    }
    
    /**
     * Otimizações para produção
     */
    private function optimizeForProduction()
    {
        // Desabilitar debug queries
        DB::disableQueryLog();
        
        // Respeitar configurações do .env para cache e sessão
        // Não forçar Redis; usar valores do ambiente ou manter os já configurados
        $envCacheStore = env('CACHE_STORE');
        $currentCacheDefault = config('cache.default');
        
        // Se o .env define CACHE_STORE, aplicar; caso contrário, manter configuração atual
        if (!empty($envCacheStore)) {
            // Se tentar usar Redis sem extensão disponível, fazer fallback para 'file'
            $cacheStoreToUse = ($envCacheStore === 'redis' && !extension_loaded('redis'))
                ? 'file'
                : $envCacheStore;
            config(['cache.default' => $cacheStoreToUse]);
        } else {
            // Garantir que não ficamos presos em 'redis' sem suporte
            if ($currentCacheDefault === 'redis' && !extension_loaded('redis')) {
                config(['cache.default' => 'file']);
            }
        }
        
        $envSessionDriver = env('SESSION_DRIVER');
        $currentSessionDriver = config('session.driver');
        
        if (!empty($envSessionDriver)) {
            $sessionDriverToUse = ($envSessionDriver === 'redis' && !extension_loaded('redis'))
                ? 'file'
                : $envSessionDriver;
            config(['session.driver' => $sessionDriverToUse]);
        } else {
            if ($currentSessionDriver === 'redis' && !extension_loaded('redis')) {
                config(['session.driver' => 'file']);
            }
        }
    }
    
    /**
     * Otimizações para desenvolvimento
     */
    private function optimizeForDevelopment()
    {
        // Habilitar query log
        DB::enableQueryLog();
        
        // Configurar cache local
        config(['cache.default' => 'file']);
        
        // Detectar N+1 queries
        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            Log::warning('N+1 Query detected', [
                'model' => get_class($model),
                'relation' => $relation,
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
            ]);
        });
    }
    
    /**
     * Obter dados comuns em cache
     */
    private function getCachedCommonData()
    {
        return Cache::remember('common_data', config('performance.cache.select_lists_ttl', 30), function () {
            return [
                'current_time' => now(),
                'app_version' => config('app.version', '1.0.0'),
                'environment' => $this->app->environment(),
            ];
        });
    }
}