<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EscolaContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Verificar se o usuário está autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Verificar se é super administrador ou suporte
            $isSuperAdmin = $user->temCargo('Super Administrador');
            $isSupport = $user->temCargo('Suporte');

            // Super admins e suporte não têm filtros aplicados automaticamente
            if ($isSuperAdmin || $isSupport) {
                // Apenas definir escola atual se estiver na sessão (para troca manual)
                if (session('escola_atual')) {
                    // Não aplicar filtros globais para super admin/suporte
                    // Eles veem todos os dados por padrão
                }
                return $next($request);
            }

            $isSchoolAdmin = $this->isSchoolAdmin($user);
            if ($isSchoolAdmin) {
                $this->handleSchoolAdminAccess($user);
                return $next($request);
            }

            // Para usuários normais, aplicar filtros por escola
            if ($user->escola_id) {
                // Definir o contexto da escola na sessão
                session(['escola_atual' => $user->escola_id]);


                // Aplicar filtro global para todas as consultas
                $this->applyGlobalScope($user->escola_id);
            }
        }

        return $next($request);
    }

    /**
     * Aplicar escopo global para filtrar por escola
     */
    private function applyGlobalScope($escolaId)
    {

        // Lista de modelos que devem ser filtrados por escola
        $modelsWithEscola = [
            \App\Models\User::class,
            \App\Models\Funcionario::class,
            \App\Models\Sala::class,
            \App\Models\Cargo::class,
            \App\Models\Aluno::class,
            \App\Models\Responsavel::class,
            \App\Models\ModalidadeEnsino::class,
            \App\Models\Grupo::class,
            \App\Models\Turno::class,
            \App\Models\Planejamento::class,
            \App\Models\QueryFavorite::class,
            \App\Models\QueryHistory::class,
        ];


        // Modelos que permitem registros globais (escola_id = null) além dos da própria escola
        $hybridModels = [
            \App\Models\Cargo::class,
            \App\Models\ModalidadeEnsino::class,
        ];

        foreach ($modelsWithEscola as $model) {
            if (class_exists($model)) {
                $isHybrid = in_array($model, $hybridModels);

                $model::addGlobalScope('escola', function ($builder) use ($escolaId, $model, $isHybrid) {
                    $tableName = (new $model)->getTable();
                    if ($isHybrid) {
                        $builder->where(function ($query) use ($tableName, $escolaId) {
                            $query->where($tableName . '.escola_id', $escolaId)
                                ->orWhereNull($tableName . '.escola_id');
                        });
                    } else {
                        $builder->where($tableName . '.escola_id', $escolaId);
                    }
                });
            }
        }
    }

    /**
     * Obter a escola atual do contexto
     */
    public static function getEscolaAtual()
    {
        return session('escola_atual');
    }

    /**
     * Definir a escola atual no contexto
     */
    public static function setEscolaAtual($escolaId)
    {
        session(['escola_atual' => $escolaId]);
    }

    /**
     * Limpar o contexto da escola
     */
    public static function clearEscolaContext()
    {
        session()->forget('escola_atual');
    }

    /**
     * Verificar se o usuário pode acessar uma escola específica
     */
    public static function podeAcessarEscola($escolaId)
    {
        $user = Auth::user();

        // Super admins e suporte podem acessar qualquer escola
        if ($user && ($user->temCargo('Super Administrador') || $user->temCargo('Suporte'))) {
            return true;
        }

        // Usuários normais só podem acessar sua própria escola
        return $user && $user->escola_id == $escolaId;
    }

    /**
     * Verifica se o usuário é administrador da escola
     */
    private function isSchoolAdmin($user)
    {
        return $user->temCargo('Administrador de Escola') ||
            $user->temCargo('Administrador') ||
            $user->temCargo('Diretor');
    }

    /**
     * Gerencia o acesso de administradores de escola
     */
    private function handleSchoolAdminAccess($user)
    {
        if (!$user->escola_id) {
            return;
        }

        // Definir escola atual
        session(['escola_atual' => $user->escola_id]);

        // Aplicar filtros globais por escola (administradores ficam restritos à sua escola)
        $this->applyGlobalScope($user->escola_id);

        // Verificar licenças para módulos específicos
        $this->checkModuleLicenses($user);
    }

    /**
     * Verifica licenças de módulos para administradores de escola
     */
    private function checkModuleLicenses($user)
    {
        if (!$user->escola_id) {
            return;
        }

        $escola = \App\Models\Escola::find($user->escola_id);
        if (!$escola) {
            return;
        }

        $licenseService = app(\App\Services\LicenseService::class);

        // Definir módulos que requerem licença (inclui relatórios e financeiro)
        $licensedModules = [
            'comunicacao_module',
            'alunos_module',
            'funcionarios_module',
            'academico_module',
            'administracao_module',
            'relatorios_module',
            'financeiro_module',
        ];

        $availableModules = [];
        foreach ($licensedModules as $module) {
            if ($licenseService->hasModuleLicense($module, $escola)) {
                $availableModules[] = $module;
            }
        }

        // Armazenar módulos disponíveis no contexto da aplicação
        app()->instance('available_modules', $availableModules);

        // Definir no cache para uso em views e controllers
        cache()->put("school_modules_{$user->escola_id}", $availableModules, now()->addHours(1));
    }

    /**
     * Aplicar filtro de escola em uma query específica
     */
    public static function aplicarFiltroEscola($query, $escolaId = null)
    {
        $escolaId = $escolaId ?: self::getEscolaAtual();

        if ($escolaId) {
            return $query->where('escola_id', $escolaId);
        }

        return $query;
    }

    /**
     * Obter dados com cache por escola
     */
    public static function getCachedData($key, $callback, $ttl = 3600)
    {
        $escolaId = self::getEscolaAtual();
        $cacheKey = "escola_{$escolaId}_{$key}";

        return cache()->remember($cacheKey, $ttl, $callback);
    }

    /**
     * Limpar cache de uma escola específica
     */
    public static function clearEscolaCache($escolaId = null)
    {
        $escolaId = $escolaId ?: self::getEscolaAtual();

        if ($escolaId) {
            $pattern = "escola_{$escolaId}_*";

            // Limpar todas as chaves de cache que começam com o padrão
            $keys = cache()->getRedis()->keys($pattern);
            if (!empty($keys)) {
                cache()->getRedis()->del($keys);
            }
        }
    }
}