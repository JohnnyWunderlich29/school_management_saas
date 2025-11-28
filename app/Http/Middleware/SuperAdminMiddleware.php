<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if (!auth()->check()) {
            abort(401, 'Não autenticado');
        }

        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Acesso negado. Apenas Super Administradores podem acessar esta funcionalidade.');
        }

        return $next($request);
    }
}