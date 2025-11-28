<?php

if (!function_exists('moduleEnabled')) {
    /**
     * Verifica se um módulo está habilitado e licenciado para a escola atual
     *
     * @param string $module Nome do módulo (ex: 'comunicacao_module')
     * @param \App\Models\Escola|null $escola Escola específica (opcional)
     * @return bool
     */
    function moduleEnabled(string $module, $escola = null): bool
    {
        // Verifica se o módulo está habilitado globalmente
        if (!config("features.modules.{$module}")) {
            return false;
        }

        // Se a verificação de licença estiver desabilitada, retorna true
        if (!config('features.license_check_enabled')) {
            return true;
        }

        // Para super admin com escola explícita, respeitar a licença da escola alvo
        if (auth()->check() && auth()->user()->isSuperAdmin() && $escola) {
            $licenseService = app(\App\Services\LicenseService::class);
            $available = $licenseService->getAvailableModules($escola);
            return in_array($module, $available);
        }

        // Verifica a licença da escola
        $licenseService = app(\App\Services\LicenseService::class);
        return $licenseService->hasModuleLicense($module, $escola);
    }
}

if (!function_exists('getAvailableModules')) {
    /**
     * Retorna todos os módulos disponíveis para a escola atual
     *
     * @param \App\Models\Escola|null $escola Escola específica (opcional)
     * @return array
     */
    function getAvailableModules($escola = null): array
    {
        $licenseService = app(\App\Services\LicenseService::class);
        return $licenseService->getAvailableModules($escola);
    }
}

if (!function_exists('getModuleLicenseInfo')) {
    /**
     * Retorna informações detalhadas da licença de um módulo
     *
     * @param string $module Nome do módulo
     * @param int|null $escolaId ID da escola (opcional)
     * @return array|null
     */
    function getModuleLicenseInfo(string $module, $escolaId = null): ?array
    {
        if (!$escolaId && auth()->check()) {
            $escolaId = auth()->user()->escola_id;
        }

        if (!$escolaId) {
            return null;
        }

        $licenseService = app(\App\Services\LicenseService::class);
        return $licenseService->getLicenseInfo($escolaId, $module);
    }
}

if (!function_exists('isModuleExpiringSoon')) {
    /**
     * Verifica se a licença de um módulo está próxima do vencimento
     *
     * @param string $module Nome do módulo
     * @param int $days Número de dias para considerar "em breve" (padrão: 30)
     * @param int|null $escolaId ID da escola (opcional)
     * @return bool
     */
    function isModuleExpiringSoon(string $module, int $days = 30, $escolaId = null): bool
    {
        if (!$escolaId && auth()->check()) {
            $escolaId = auth()->user()->escola_id;
        }

        if (!$escolaId) {
            return false;
        }

        $licenseService = app(\App\Services\LicenseService::class);
        return $licenseService->isLicenseExpiringSoon($escolaId, $module, $days);
    }
}

if (!function_exists('canAccessModule')) {
    /**
     * Verifica se o usuário atual pode acessar um módulo específico
     * Considera permissões de super admin, administradores de escola e licenças
     *
     * @param string $module Nome do módulo
     * @return bool
     */
    function canAccessModule(string $module): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Super administradores e suporte: respeitar licença quando há escola em contexto
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            // Determinar escola em contexto
            $escola = null;
            if (app()->bound('current_school')) {
                $escola = app('current_school');
            }
            if (!$escola && session()->has('escola_atual')) {
                $id = session('escola_atual');
                if ($id) {
                    $escola = \App\Models\Escola::find($id);
                }
            }
            if (!$escola && $user->escola_id) {
                $escola = \App\Models\Escola::find($user->escola_id);
            }

            if ($escola) {
                $licenseService = app(\App\Services\LicenseService::class);
                return $licenseService->hasModuleLicense($module, $escola);
            }

            // Sem escola em contexto, manter acesso total
            return true;
        }

        // Administradores de escola precisam ter licença para o módulo
        if ($user->temCargo('Administrador de Escola') || 
            $user->temCargo('Administrador') || 
            $user->temCargo('Diretor')) {
            
            // Verificar se o módulo está na lista de módulos disponíveis (cache)
            if ($user->escola_id) {
                $availableModules = cache()->get("school_modules_{$user->escola_id}", []);
                if (!empty($availableModules)) {
                    return in_array($module, $availableModules);
                }
                // Sem cache, checar diretamente
                $licenseService = app(\App\Services\LicenseService::class);
                $escola = \App\Models\Escola::find($user->escola_id);
                return $licenseService->hasModuleLicense($module, $escola);
            }
        }

        // Para outros usuários, usar a verificação padrão de licença
        return moduleEnabled($module);
    }
}