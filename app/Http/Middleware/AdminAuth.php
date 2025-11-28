<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
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
            // Se for uma requisição AJAX, retornar JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você precisa estar logado para acessar o painel administrativo.',
                    'redirect' => route('corporativo.login')
                ], 401);
            }
            
            return redirect()->route('corporativo.login')->with('error', 'Você precisa estar logado para acessar o painel administrativo.');
        }

        $user = Auth::user();


        // Verificar se o usuário é Super Administrador ou tem cargo de Suporte Técnico
        if (!$user->isSuperAdmin() && !$user->temCargo('Suporte Técnico')) {
            abort(403, 'Acesso negado. Apenas Super Administradores e Suporte Técnico podem acessar esta área.');
        }

        return $next($request);
    }
}