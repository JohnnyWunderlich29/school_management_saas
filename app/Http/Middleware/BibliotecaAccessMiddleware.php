<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Services\LicenseService;

class BibliotecaAccessMiddleware
{
    protected LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        // Requer usuário autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para acessar a biblioteca.');
        }

        $user = Auth::user();

        // Super admin tem acesso irrestrito
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verificar licença do módulo biblioteca
        if (!$this->licenseService->hasModuleLicense('biblioteca_module')) {
            return $this->deny($request, 'Licença da Biblioteca indisponível para esta escola.');
        }

        // Verificar permissão específica (se fornecida)
        if ($permission && !$user->temPermissao($permission)) {
            abort(403, 'Você não tem permissão para acessar esta funcionalidade da biblioteca.');
        }

        return $next($request);
    }

    private function deny(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Acesso negado',
                'message' => $message,
                'code' => 'BIBLIOTECA_ACCESS_DENIED'
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', $message)
            ->with('alert_type', 'warning');
    }
}