<?php

namespace App\Services;

use App\Models\SchoolLicense;
use App\Models\Escola;
use App\Models\EscolaModule;
use Carbon\Carbon;

class LicenseService
{
    /**
     * Verifica se uma escola tem licença para um módulo específico
     */
    public function hasModuleLicense(string $module, ?Escola $escola = null): bool
    {
        if (!config('features.license_check_enabled')) {
            return true;
        }

        // Determinar escola em contexto
        $escola = $escola ?? $this->getCurrentSchool();

        // Super admins: se não houver escola em contexto, acesso total;
        // se houver escola, respeitar licença da escola alvo
        if (auth()->check() && auth()->user()->isSuperAdmin() && !$escola) {
            return true;
        }
        
        if (!$escola) {
            return false;
        }

        // Verificar no novo sistema de módulos primeiro
        $escolaModule = EscolaModule::where('escola_id', $escola->id)
            ->whereHas('module', function($query) use ($module) {
                $query->where('name', $module);
            })
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->first();
            
        if ($escolaModule) {
            return true;
        }

        // Se existir registro no novo sistema para este módulo (ainda que inativo), considerar novo sistema como fonte de verdade
        $existsInNewSystem = EscolaModule::where('escola_id', $escola->id)
            ->whereHas('module', function($query) use ($module) {
                $query->where('name', $module);
            })
            ->exists();

        if ($existsInNewSystem) {
            return false;
        }

        // Fallback para o sistema antigo (SchoolLicense)
        return SchoolLicense::where('escola_id', $escola->id)
            ->where('module_name', $module)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Retorna todos os módulos licenciados para uma escola
     */
    public function getAvailableModules(?Escola $escola = null): array
    {
        if (!config('features.license_check_enabled')) {
            return array_keys(config('features'));
        }

        $escola = $escola ?? $this->getCurrentSchool();
        
        if (!$escola) {
            return [];
        }

        // Buscar módulos do novo sistema
        $newSystemModules = EscolaModule::where('escola_id', $escola->id)
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->with('module')
            ->get()
            ->pluck('module.name')
            ->toArray();

        // Buscar módulos do sistema antigo
        $oldSystemModules = SchoolLicense::where('escola_id', $escola->id)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->pluck('module_name')
            ->toArray();

        // Combinar e remover duplicatas
        return array_unique(array_merge($newSystemModules, $oldSystemModules));
    }

    /**
     * Cria uma nova licença para uma escola
     */
    public function createLicense(int $escolaId, string $module, int $durationDays = null): SchoolLicense
    {
        $durationDays = $durationDays ?? config('features.default_license_duration');
        
        return SchoolLicense::create([
            'escola_id' => $escolaId,
            'module_name' => $module,
            'is_active' => true,
            'expires_at' => Carbon::now()->addDays($durationDays),
            'max_users' => null, // Pode ser implementado futuramente
        ]);
    }

    /**
     * Revoga uma licença
     */
    public function revokeLicense(int $escolaId, string $module): bool
    {
        return SchoolLicense::where('escola_id', $escolaId)
            ->where('module_name', $module)
            ->update(['is_active' => false]);
    }

    /**
     * Verifica se uma licença está próxima do vencimento
     */
    public function isLicenseExpiringSoon(int $escolaId, string $module, int $days = 30): bool
    {
        $license = SchoolLicense::where('escola_id', $escolaId)
            ->where('module_name', $module)
            ->where('is_active', true)
            ->first();

        if (!$license) {
            return false;
        }

        return $license->expires_at->diffInDays(now()) <= $days;
    }

    /**
     * Obtém a escola atual do contexto
     */
    private function getCurrentSchool(): ?Escola
    {
        if (app()->bound('current_school')) {
            return app('current_school');
        }

        // Para super admins e navegação corporativa, usar escola da sessão, se disponível
        if (session()->has('escola_atual')) {
            $id = session('escola_atual');
            if ($id) {
                return Escola::find($id);
            }
        }

        if (auth()->check() && auth()->user()->escola_id) {
            return Escola::find(auth()->user()->escola_id);
        }

        return null;
    }

    /**
     * Obtém informações detalhadas de uma licença
     */
    public function getLicenseInfo(int $escolaId, string $module): ?array
    {
        $license = SchoolLicense::where('escola_id', $escolaId)
            ->where('module_name', $module)
            ->where('is_active', true)
            ->first();

        if (!$license) {
            return null;
        }

        return [
            'module' => $license->module_name,
            'expires_at' => $license->expires_at,
            'days_remaining' => $license->expires_at->diffInDays(now()),
            'is_expiring_soon' => $this->isLicenseExpiringSoon($escolaId, $module),
            'max_users' => $license->max_users,
        ];
    }
}