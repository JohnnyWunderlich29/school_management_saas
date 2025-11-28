<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar se o usuário está autenticado
        if (!Auth::check()) {
            return redirect()->route('corporativo.login')
                ->with('error', 'Você precisa estar logado para acessar esta área.');
        }
        
        $user = Auth::user();

        // Verificar se o usuário é super administrador
        if (!$user->isSuperAdmin()) {
            abort(403, 'Acesso negado. Esta área é exclusiva para super administradores.');
        }

        return $next($request);
    }
}