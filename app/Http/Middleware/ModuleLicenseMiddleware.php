<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\LicenseService;
use Symfony\Component\HttpFoundation\Response;

class ModuleLicenseMiddleware
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $module): Response
    {
        // Super admins têm acesso a todos os módulos
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return $next($request);
        }

        // Verifica se o módulo está habilitado globalmente
        if (!config("features.modules.{$module}")) {
            return $this->unauthorizedResponse($request, 'Módulo não disponível');
        }

        // Verifica se a escola tem licença para o módulo
        if (!$this->licenseService->hasModuleLicense($module)) {
            return $this->unauthorizedResponse($request, 'Licença do módulo expirada ou não encontrada');
        }

        return $next($request);
    }

    /**
     * Retorna resposta de não autorizado
     */
    private function unauthorizedResponse(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Acesso negado',
                'message' => $message,
                'code' => 'MODULE_ACCESS_DENIED'
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', $message)
            ->with('alert_type', 'warning');
    }
}